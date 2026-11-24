#!/usr/bin/env bash
set -euo pipefail

fail() {
	echo "ERROR: $*" >&2
	exit 1
}

: "${PLUGIN_SLUG:?PLUGIN_SLUG is required}"
: "${VERSION:?VERSION is required}"
: "${BUNDLE:?BUNDLE is required}"
: "${DRY_RUN:?DRY_RUN must be true or false}"

PROJECT_ROOT="${GITHUB_WORKSPACE:-$(pwd)}"
SVN_URL="${SVN_URL:-https://plugins.svn.wordpress.org/${PLUGIN_SLUG}/}"
SVN_URL="${SVN_URL%/}/"
PREVIEW_DIR="${SVN_PREVIEW_DIR:-${PROJECT_ROOT}/deploy/svn-preview}"

[[ "$PLUGIN_SLUG" =~ ^[a-z0-9-]+$ ]] || fail "Invalid plugin slug: $PLUGIN_SLUG"
[[ "$VERSION" =~ ^[0-9]+\.[0-9]+(\.[0-9]+)*$ ]] || fail "Invalid release version: $VERSION"
[[ "$DRY_RUN" == 'true' || "$DRY_RUN" == 'false' ]] || fail "DRY_RUN must be true or false"
[[ -f "$BUNDLE/brand-master.php" ]] || fail "Plugin bundle not found: $BUNDLE"

if [[ "$DRY_RUN" == 'true' && ( -n "${SVN_USERNAME:-}" || -n "${SVN_PASSWORD:-}" ) ]]; then
	fail "Dry runs must not receive SVN credentials."
fi

auth_args=()
if [[ -n "${SVN_USERNAME:-}" && -n "${SVN_PASSWORD:-}" ]]; then
	auth_args=( --username "$SVN_USERNAME" --password "$SVN_PASSWORD" )
elif [[ -n "${SVN_USERNAME:-}" || -n "${SVN_PASSWORD:-}" ]]; then
	fail "SVN_USERNAME and SVN_PASSWORD must be supplied together."
elif [[ "$DRY_RUN" == 'false' && "$SVN_URL" == https://* ]]; then
	fail "SVN credentials are required to publish to WordPress.org."
fi

if [[ -n "${SVN_WORK_DIR:-}" ]]; then
	WORK_DIR="$SVN_WORK_DIR"
else
	WORK_DIR="$(mktemp -d "${RUNNER_TEMP:-${TMPDIR:-/tmp}}/brand-master-svn-${VERSION}.XXXXXX")"
fi

mkdir -p "$WORK_DIR"
if [[ -n "$(find "$WORK_DIR" -mindepth 1 -print -quit)" ]]; then
	fail "SVN working directory is not empty: $WORK_DIR"
fi

rm -rf "$PREVIEW_DIR"
mkdir -p "$PREVIEW_DIR"

echo "Preparing Brand Master $VERSION from $SVN_URL"

svn checkout \
	--depth immediates \
	--non-interactive \
	--no-auth-cache \
	"${auth_args[@]}" \
	"$SVN_URL" \
	"$WORK_DIR"

cd "$WORK_DIR"

# A failed listing is not evidence that a tag is absent. Fail closed.
tag_list="$(mktemp)"
if ! svn list \
	--xml \
	--non-interactive \
	--no-auth-cache \
	"${auth_args[@]}" \
	"${SVN_URL}tags" > "$tag_list"; then
	fail "Could not list SVN tags; refusing to continue."
fi

set +e
python3 - "$tag_list" "$VERSION" <<'PY'
import sys
import xml.etree.ElementTree as ET

try:
    root = ET.parse(sys.argv[1]).getroot()
except (ET.ParseError, OSError) as exc:
    print(f"Could not parse SVN tag listing: {exc}", file=sys.stderr)
    raise SystemExit(2)

names = {entry.findtext("name", "") for entry in root.findall(".//entry")}
raise SystemExit(10 if sys.argv[2] in names else 0)
PY
tag_status=$?
set -e

case "$tag_status" in
	0) ;;
	10) fail "SVN tag $VERSION already exists; refusing to overwrite it." ;;
	*) fail "Could not interpret the SVN tag listing; refusing to continue." ;;
esac

cp "$tag_list" "$PREVIEW_DIR/svn-tags.xml"
rm -f "$tag_list"

svn update \
	--non-interactive \
	--no-auth-cache \
	"${auth_args[@]}" \
	--set-depth infinity trunk

svn update \
	--non-interactive \
	--no-auth-cache \
	"${auth_args[@]}" \
	--set-depth immediates tags

# Sync only the runtime package. Top-level SVN assets are never touched.
rsync -rc --delete --exclude='.svn/' "$BUNDLE/" trunk/

# Stage additions, then explicitly stage paths deleted by rsync.
svn add --force trunk >/dev/null

status_xml="$(mktemp)"
svn status --xml trunk > "$status_xml"

python3 - "$status_xml" <<'PY' > "${status_xml}.missing"
import sys
import xml.etree.ElementTree as ET

root = ET.parse(sys.argv[1]).getroot()
paths = sorted(
    {
        entry.get("path")
        for entry in root.findall(".//entry")
        if entry.get("path")
        and entry.find("wc-status") is not None
        and entry.find("wc-status").get("item") == "missing"
    },
    key=lambda value: (value.count("/"), value),
)
selected = []
for path in paths:
    if not any(path == parent or path.startswith(parent + "/") for parent in selected):
        selected.append(path)
for path in selected:
    sys.stdout.buffer.write(path.encode("utf-8") + b"\0")
PY

while IFS= read -r -d '' missing_path; do
	svn delete --force "$missing_path"
done < "${status_xml}.missing"

rm -f "$status_xml" "${status_xml}.missing"

# The release tag must be an SVN copy of the fully staged trunk.
svn copy trunk "tags/$VERSION"

php "${PROJECT_ROOT}/.github/scripts/validate-release.php" \
	"$VERSION" \
	"$WORK_DIR/trunk" \
	"$PROJECT_ROOT"

diff -qr --exclude='.svn' trunk "tags/$VERSION"

all_status="$(mktemp)"
svn status --xml > "$all_status"

python3 - "$all_status" "$VERSION" <<'PY'
import sys
import xml.etree.ElementTree as ET

root = ET.parse(sys.argv[1]).getroot()
version = sys.argv[2]
allowed = ("trunk", f"tags/{version}")
blocked = {"missing", "unversioned", "conflicted", "obstructed", "incomplete", "ignored", "external"}
counts = {}

for entry in root.findall(".//entry"):
    path = entry.get("path", "")
    status = entry.find("wc-status")
    item = status.get("item", "normal") if status is not None else "normal"
    counts[item] = counts.get(item, 0) + 1

    if not any(path == prefix or path.startswith(prefix + "/") for prefix in allowed):
        raise SystemExit(f"Unexpected SVN change outside trunk/tags/{version}: {path}")
    if item in blocked:
        raise SystemExit(f"Unsafe SVN status '{item}' for {path}")

if not any(
    entry.get("path") == f"tags/{version}"
    and entry.find("wc-status") is not None
    and entry.find("wc-status").get("item") == "added"
    for entry in root.findall(".//entry")
):
    raise SystemExit(f"Staged SVN copy tags/{version} was not found.")

print("Staged SVN status: " + ", ".join(f"{key}={value}" for key, value in sorted(counts.items())))
PY

rm -f "$all_status"

svn status > "$PREVIEW_DIR/svn-status.txt"
svn diff > "$PREVIEW_DIR/svn-diff.txt" || true
(
	find trunk -path '*/.svn' -prune -o -type f -print0 \
		| sort -z \
		| xargs -0 sha256sum
) > "$PREVIEW_DIR/trunk-manifest.sha256"

echo "Staged SVN changes:"
svn status

if [[ "$DRY_RUN" == 'true' ]]; then
	echo "DRY RUN COMPLETE: no SVN commit was performed."
	exit 0
fi

commit_message="Brand Master: Release ${VERSION} from GitHub"
release_commit="${RELEASE_COMMIT:-${GITHUB_SHA:-}}"
if [[ -n "$release_commit" ]]; then
	commit_message="${commit_message} (${release_commit})"
fi

svn commit \
	--non-interactive \
	--no-auth-cache \
	"${auth_args[@]}" \
	-m "$commit_message"

revision="$(svn info --show-item revision "${SVN_URL}tags/${VERSION}")"
if [[ -n "${GITHUB_OUTPUT:-}" ]]; then
	echo "version=$VERSION" >> "$GITHUB_OUTPUT"
	echo "revision=$revision" >> "$GITHUB_OUTPUT"
fi
echo "Deployed Brand Master $VERSION at SVN revision $revision."
