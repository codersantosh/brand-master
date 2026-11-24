#!/usr/bin/env bash
set -euo pipefail

: "${RUNNER_TEMP:?RUNNER_TEMP must be set}"

version='2.12.0'
filename="wp-cli-${version}.phar"
url="https://github.com/wp-cli/wp-cli/releases/download/v${version}/${filename}"
sha512='be928f6b8ca1e8dfb9d2f4b75a13aa4aee0896f8a9a0a1c45cd5d2c98605e6172e6d014dda2e27f88c98befc16c040cbb2bd1bfa121510ea5cdf5f6a30fe8832'
destination="${RUNNER_TEMP}/wp"

curl --fail --silent --show-error --location "$url" --output "$destination"
printf '%s  %s\n' "$sha512" "$destination" | sha512sum --check --strict
chmod 0755 "$destination"
if [[ -n "${GITHUB_PATH:-}" ]]; then
	echo "$RUNNER_TEMP" >> "$GITHUB_PATH"
fi
"$destination" --version
