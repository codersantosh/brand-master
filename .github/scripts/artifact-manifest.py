#!/usr/bin/env python3
"""Create and verify immutable Brand Master artifact metadata."""

from __future__ import annotations

import hashlib
import json
import os
import re
import sys
from pathlib import Path


MANIFEST_NAME = "artifact-manifest.json"
HEX_40 = re.compile(r"^[0-9a-f]{40}$")
HEX_64 = re.compile(r"^[0-9a-f]{64}$")


def fail(message: str) -> None:
    print(f"ERROR: {message}", file=sys.stderr)
    raise SystemExit(1)


def require_env(name: str) -> str:
    value = os.environ.get(name, "").strip()
    if not value:
        fail(f"{name} is required")
    return value


def archive_paths(root: Path, version: str) -> tuple[Path, Path]:
    return (
        root / f"brand-master-{version}.zip",
        root / f"brand-master-{version}.zip.sha256",
    )


def verified_digest(root: Path, version: str) -> tuple[str, int]:
    archive, checksum = archive_paths(root, version)
    if not archive.is_file() or not checksum.is_file():
        fail("Release ZIP or checksum is missing")

    lines = checksum.read_text(encoding="utf-8").strip().splitlines()
    if len(lines) != 1:
        fail("Checksum file must contain exactly one line")
    parts = lines[0].split(maxsplit=1)
    if len(parts) != 2 or Path(parts[1].lstrip("*")).name != archive.name:
        fail("Malformed checksum file")
    expected = parts[0].lower()
    if not HEX_64.fullmatch(expected):
        fail("Checksum is not a SHA-256 digest")
    actual = hashlib.sha256(archive.read_bytes()).hexdigest()
    if actual != expected:
        fail("ZIP SHA-256 checksum does not match")

    runtime = root / "brand-master"
    if not runtime.is_dir():
        fail("Runtime directory is missing")
    file_count = sum(1 for path in runtime.rglob("*") if path.is_file())
    return actual, file_count


def write_output(key: str, value: str) -> None:
    output = os.environ.get("GITHUB_OUTPUT")
    if output:
        with open(output, "a", encoding="utf-8") as handle:
            handle.write(f"{key}={value}\n")


def create(root: Path, version: str) -> None:
    if not re.fullmatch(r"[0-9]+\.[0-9]+(?:\.[0-9]+)*", version):
        fail(f"Invalid version: {version}")

    digest, file_count = verified_digest(root, version)
    commit = require_env("RELEASE_COMMIT")
    artifact = require_env("ARTIFACT_NAME")
    run_id = require_env("GITHUB_RUN_ID")
    run_attempt = require_env("GITHUB_RUN_ATTEMPT")
    repository = require_env("GITHUB_REPOSITORY")
    server_url = require_env("GITHUB_SERVER_URL").rstrip("/")
    run_url = require_env("GITHUB_RUN_URL")

    if not HEX_40.fullmatch(commit):
        fail("RELEASE_COMMIT must be a full Git commit SHA")
    if not re.fullmatch(r"[0-9]+", run_id) or not re.fullmatch(r"[0-9]+", run_attempt):
        fail("GitHub run ID and attempt must be numeric")
    if not re.fullmatch(r"[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+", repository):
        fail("GITHUB_REPOSITORY is invalid")
    if run_url != f"{server_url}/{repository}/actions/runs/{run_id}":
        fail("GITHUB_RUN_URL does not match the current repository and run")
    if artifact != f"brand-master-{version}-{commit}-{digest[:12]}":
        fail("ARTIFACT_NAME does not match the version, commit, and package digest")

    release_tag = os.environ.get("RELEASE_TAG", "").strip()
    release_url = os.environ.get("RELEASE_URL", "").strip()
    if release_tag and not re.fullmatch(r"v[0-9]+\.[0-9]+(?:\.[0-9]+)*", release_tag):
        fail("RELEASE_TAG is invalid")
    if release_tag and release_tag.removeprefix("v") != version:
        fail("RELEASE_TAG does not match the package version")
    if release_url and not release_url.startswith(f"{server_url}/{repository}/releases/"):
        fail("RELEASE_URL does not belong to the current GitHub repository")

    manifest = {
        "schema_version": 1,
        "plugin": "brand-master",
        "version": version,
        "commit": commit,
        "artifact": artifact,
        "zip_sha256": digest,
        "runtime_files": file_count,
        "repository": repository,
        "workflow_run_id": run_id,
        "workflow_run_attempt": run_attempt,
        "workflow_run_url": run_url,
        "release_tag": release_tag,
        "release_url": release_url,
    }
    (root / MANIFEST_NAME).write_text(json.dumps(manifest, indent=2, sort_keys=True) + "\n", encoding="utf-8")
    print(f"Artifact manifest created: {digest} ({file_count} runtime files).")


def verify(root: Path, version: str, expected_digest: str) -> None:
    path = root / MANIFEST_NAME
    if not path.is_file():
        fail(f"Missing artifact manifest: {path}")
    try:
        manifest = json.loads(path.read_text(encoding="utf-8"))
    except (OSError, json.JSONDecodeError) as exc:
        fail(f"Could not read artifact manifest: {exc}")
    if not isinstance(manifest, dict):
        fail("Artifact manifest must be a JSON object")

    digest, file_count = verified_digest(root, version)
    expected = {
        "schema_version": 1,
        "plugin": "brand-master",
        "version": version,
        "commit": require_env("RELEASE_COMMIT"),
        "artifact": require_env("ARTIFACT_NAME"),
        "zip_sha256": expected_digest,
        "runtime_files": file_count,
        "repository": require_env("GITHUB_REPOSITORY"),
        "workflow_run_id": require_env("GITHUB_RUN_ID"),
        "workflow_run_attempt": require_env("GITHUB_RUN_ATTEMPT"),
        "workflow_run_url": require_env("GITHUB_RUN_URL"),
        "release_tag": os.environ.get("RELEASE_TAG", "").strip(),
        "release_url": os.environ.get("RELEASE_URL", "").strip(),
    }
    if not HEX_64.fullmatch(expected_digest) or digest != expected_digest:
        fail("Artifact digest does not match the reviewed package")
    for key, value in expected.items():
        if manifest.get(key) != value:
            fail(f"Artifact manifest mismatch for {key}: {manifest.get(key)!r} != {value!r}")
    if manifest.get("zip_sha256") != digest:
        fail("Artifact manifest ZIP digest does not match the downloaded ZIP")

    write_output("package_sha256", digest)
    print(f"Artifact manifest verified for Brand Master {version}: {digest}.")


def main() -> None:
    if len(sys.argv) not in (4, 5) or sys.argv[1] not in ("create", "verify"):
        fail("Usage: artifact-manifest.py create|verify VERSION ARTIFACT_ROOT [EXPECTED_SHA256]")
    command, version, root_arg = sys.argv[1:4]
    root = Path(root_arg).resolve()
    if command == "create":
        create(root, version)
    else:
        if len(sys.argv) != 5:
            fail("verify requires EXPECTED_SHA256")
        verify(root, version, sys.argv[4].lower())


if __name__ == "__main__":
    main()
