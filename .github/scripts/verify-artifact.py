#!/usr/bin/env python3
"""Verify a downloaded Brand Master release artifact and its ZIP contents."""

from __future__ import annotations

import hashlib
import stat
import sys
import zipfile
from pathlib import Path


def fail(message: str) -> None:
    print(f"ERROR: {message}", file=sys.stderr)
    raise SystemExit(1)


def main() -> None:
    if len(sys.argv) != 3:
        fail("Usage: verify-artifact.py VERSION ARTIFACT_ROOT")

    version = sys.argv[1]
    root = Path(sys.argv[2]).resolve()
    runtime = root / "brand-master"
    archive = root / f"brand-master-{version}.zip"
    checksum = root / f"brand-master-{version}.zip.sha256"

    if not version.replace(".", "").isdigit():
        fail(f"Invalid version: {version}")
    if not runtime.is_dir():
        fail(f"Missing runtime directory: {runtime}")
    if not archive.is_file():
        fail(f"Missing ZIP: {archive}")
    if not checksum.is_file():
        fail(f"Missing checksum file: {checksum}")

    checksum_lines = checksum.read_text(encoding="utf-8").strip().splitlines()
    if len(checksum_lines) != 1:
        fail("Checksum file must contain exactly one line")

    parts = checksum_lines[0].split(maxsplit=1)
    if len(parts) != 2:
        fail("Malformed checksum file")
    expected_hash = parts[0].lower()
    checksum_name = Path(parts[1].lstrip("*")).name
    if checksum_name != archive.name:
        fail(f"Checksum file names {checksum_name}, expected {archive.name}")

    actual_hash = hashlib.sha256(archive.read_bytes()).hexdigest()
    if actual_hash != expected_hash:
        fail("ZIP SHA-256 checksum does not match")

    runtime_files: dict[str, Path] = {}
    for path in runtime.rglob("*"):
        if path.is_symlink():
            fail(f"Symlink in runtime directory: {path.relative_to(runtime)}")
        if path.is_file():
            runtime_files[path.relative_to(runtime).as_posix()] = path

    if "brand-master.php" not in runtime_files:
        fail("Runtime directory is missing brand-master.php")

    try:
        with zipfile.ZipFile(archive) as package:
            bad_member = package.testzip()
            if bad_member is not None:
                fail(f"ZIP CRC check failed for {bad_member}")

            zip_files: dict[str, bytes] = {}
            for info in package.infolist():
                name = info.filename
                if "\\" in name or name.startswith("/"):
                    fail(f"Unsafe ZIP path: {name}")
                path_parts = Path(name).parts
                if ".." in path_parts:
                    fail(f"Unsafe ZIP path: {name}")
                if not name.startswith("brand-master/"):
                    fail(f"ZIP entry is outside brand-master/: {name}")
                if info.is_dir():
                    continue

                mode = info.external_attr >> 16
                if stat.S_ISLNK(mode):
                    fail(f"Symlink in ZIP: {name}")
                if name in zip_files:
                    fail(f"Duplicate ZIP entry: {name}")

                zip_files[name.removeprefix("brand-master/")] = package.read(info)
    except zipfile.BadZipFile as exc:
        fail(f"Invalid ZIP: {exc}")

    runtime_names = set(runtime_files)
    zip_names = set(zip_files)
    if runtime_names != zip_names:
        missing = sorted(runtime_names - zip_names)
        extra = sorted(zip_names - runtime_names)
        fail(f"ZIP/runtime file mismatch; missing={missing}, extra={extra}")

    for relative, path in runtime_files.items():
        runtime_hash = hashlib.sha256(path.read_bytes()).hexdigest()
        zip_hash = hashlib.sha256(zip_files[relative]).hexdigest()
        if runtime_hash != zip_hash:
            fail(f"ZIP content differs from runtime directory: {relative}")

    print(
        f"Artifact verification passed for Brand Master {version}: "
        f"{len(runtime_files)} files, SHA-256 {actual_hash}."
    )


if __name__ == "__main__":
    main()
