#!/usr/bin/env bash
set -euo pipefail

fail() {
	echo "ERROR: $*" >&2
	exit 1
}

: "${RUNNER_TEMP:?RUNNER_TEMP must be set}"
: "${WP_VERSION:?WP_VERSION must be set}"
: "${DB_NAME:?DB_NAME must be set}"
: "${DB_USER:?DB_USER must be set}"
: "${DB_PASSWORD:?DB_PASSWORD must be set}"
: "${ADMIN_USER:?ADMIN_USER must be set}"
: "${ADMIN_PASSWORD:?ADMIN_PASSWORD must be set}"
: "${ADMIN_EMAIL:?ADMIN_EMAIL must be set}"

PROJECT_ROOT="$(realpath "${GITHUB_WORKSPACE:-$(pwd)}")"
DB_HOST="${DB_HOST:-127.0.0.1:3306}"
WP_DIR="${RUNNER_TEMP}/wordpress-${WP_VERSION}"
TEST_URL='http://127.0.0.1:8080'
export HTTP_HOST='127.0.0.1:8080'
SERVER_PID=''
COOKIE_JAR="${RUNNER_TEMP}/brand-master-cookies.txt"
WP=( wp --path="$WP_DIR" )

cleanup() {
	if [[ -n "$SERVER_PID" ]]; then
		kill "$SERVER_PID" 2>/dev/null || true
		wait "$SERVER_PID" 2>/dev/null || true
	fi
}
trap cleanup EXIT

assert_contains() {
	local file="$1"
	local expected="$2"
	local label="$3"
	if ! grep -Fq "$expected" "$file"; then
		fail "$label did not contain '$expected'."
	fi
}

assert_status() {
	local url="$1"
	local expected="$2"
	local output="$3"
	local status
	status="$(curl --silent --show-error --location --output "$output" --write-out '%{http_code}' "$url")"
	if [[ "$status" != "$expected" ]]; then
		head -c 2000 "$output" >&2 || true
		fail "$url returned HTTP $status, expected $expected."
	fi
}

rm -rf "$WP_DIR"
mkdir -p "$WP_DIR"

echo "Installing WordPress $WP_VERSION for the Brand Master runtime smoke test."
wp core download --path="$WP_DIR" --version="$WP_VERSION" --locale=en_US --force
mkdir -p "$WP_DIR/wp-content/mu-plugins"
cp "${PROJECT_ROOT}/.github/scripts/runtime-mu-plugin.php" "$WP_DIR/wp-content/mu-plugins/00-brand-master-runtime.php"
wp config create \
	--path="$WP_DIR" \
	--dbname="$DB_NAME" \
	--dbuser="$DB_USER" \
	--dbpass="$DB_PASSWORD" \
	--dbhost="$DB_HOST" \
	--skip-check
wp config set --path="$WP_DIR" WP_DEBUG true --raw
wp config set --path="$WP_DIR" WP_DEBUG_DISPLAY false --raw
wp config set --path="$WP_DIR" WP_DEBUG_LOG "$WP_DIR/debug.log"
wp config set --path="$WP_DIR" AUTOMATIC_UPDATER_DISABLED true --raw
wp db create --path="$WP_DIR"
wp core install \
	--path="$WP_DIR" \
	--url="$TEST_URL" \
	--title='Brand Master Runtime Test' \
	--admin_user="$ADMIN_USER" \
	--admin_password="$ADMIN_PASSWORD" \
	--admin_email="$ADMIN_EMAIL" \
	--skip-email
wp option update --path="$WP_DIR" home "$TEST_URL"
wp option update --path="$WP_DIR" siteurl "$TEST_URL"
wp option update --path="$WP_DIR" permalink_structure '/%postname%/'
wp rewrite flush --hard --path="$WP_DIR"

version="$(node -p "require('${PROJECT_ROOT}/package.json').version")"
wp plugin install \
	--path="$WP_DIR" \
	"${PROJECT_ROOT}/deploy/brand-master-${version}.zip" \
	--activate
wp config set --path="$WP_DIR" WP_HTTP_BLOCK_EXTERNAL true --raw

review_id="$(wp post create \
	--path="$WP_DIR" \
	--post_type=page \
	--post_status=publish \
	--post_title='Brand Master Smoke Review' \
	--post_content='[brand_master_dashboard /][brand_master_login /]' \
	--porcelain)"
[[ "$review_id" =~ ^[0-9]+$ ]] || fail 'Could not create the smoke-test review page.'

BM_SMOKE_PHASE='initial' \
BM_EXPECTED_VERSION="$version" \
	"${WP[@]}" eval-file "${PROJECT_ROOT}/.github/scripts/wordpress-runtime-assertions.php"

BM_WORDPRESS_DIR="$WP_DIR" php -S 127.0.0.1:8080 \
	-t "$WP_DIR" \
	"${PROJECT_ROOT}/.github/scripts/wp-router.php" \
	> "${RUNNER_TEMP}/brand-master-server.log" 2>&1 &
SERVER_PID=$!

for _ in $( seq 1 30 ); do
	if curl --silent --fail --output /dev/null "$TEST_URL/"; then
		break
	fi
	if ! kill -0 "$SERVER_PID" 2>/dev/null; then
		cat "${RUNNER_TEMP}/brand-master-server.log" >&2
		fail 'The WordPress test server exited during startup.'
	fi
	sleep 1
done
curl --silent --fail --output /dev/null "$TEST_URL/"

assert_status "$TEST_URL/?page_id=${review_id}" '200' "${RUNNER_TEMP}/review.html"
assert_contains "${RUNNER_TEMP}/review.html" 'bm-dashboard' 'Frontend dashboard'
assert_contains "${RUNNER_TEMP}/review.html" 'login-action-login' 'Frontend login form'
assert_status "$TEST_URL/wp-json/brand-master/v1/settings" '401' "${RUNNER_TEMP}/rest-anonymous.json"
assert_status "$TEST_URL/wp-json/wp/v2/settings" '401' "${RUNNER_TEMP}/core-settings-anonymous.json"

rm -f "$COOKIE_JAR"
login_status="$(curl --silent --show-error \
	--cookie-jar "$COOKIE_JAR" \
	--output /dev/null \
	--write-out '%{http_code}' \
	--data-urlencode "log=${ADMIN_USER}" \
	--data-urlencode "pwd=${ADMIN_PASSWORD}" \
	--data-urlencode 'wp-submit=Log In' \
	"$TEST_URL/wp-login.php")"
[[ "$login_status" == '302' ]] || fail "Administrator login returned HTTP $login_status."

admin_status="$(curl --silent --show-error \
	--cookie "$COOKIE_JAR" \
	--output "${RUNNER_TEMP}/admin.html" \
	--write-out '%{http_code}' \
	"$TEST_URL/wp-admin/admin.php?page=brand-master")"
[[ "$admin_status" == '200' ]] || fail "Brand Master admin page returned HTTP $admin_status."
assert_contains "${RUNNER_TEMP}/admin.html" 'id="brand-master"' 'Brand Master admin root'
assert_contains "${RUNNER_TEMP}/admin.html" 'brandMasterLocalize' 'Admin localization'
assert_contains "${RUNNER_TEMP}/admin.html" 'build/admin/admin.js' 'Admin JavaScript bundle'
assert_contains "${RUNNER_TEMP}/admin.html" 'build/admin/styles.css' 'Admin stylesheet'

nonce="$(python3 - "${RUNNER_TEMP}/admin.html" <<'PY'
import json
import re
import sys

with open(sys.argv[1], encoding="utf-8") as handle:
    html = handle.read()
match = re.search(r"var brandMasterLocalize\s*=\s*(\{.*?\});", html, re.DOTALL)
if not match:
    raise SystemExit("brandMasterLocalize was not found on the admin page")
nonce = json.loads(match.group(1)).get("nonce", "")
if not re.fullmatch(r"[0-9a-f]{10,}", nonce):
    raise SystemExit("brandMasterLocalize did not contain a valid REST nonce")
print(nonce)
PY
)"
rest_status="$(curl --silent --show-error \
	--cookie "$COOKIE_JAR" \
	--header "X-WP-Nonce: $nonce" \
	--output "${RUNNER_TEMP}/rest-authenticated.json" \
	--write-out '%{http_code}' \
	"$TEST_URL/wp-json/brand-master/v1/settings")"
[[ "$rest_status" == '200' ]] || fail "Authenticated settings API returned HTTP $rest_status."
python3 - "${RUNNER_TEMP}/rest-authenticated.json" <<'PY'
import json
import sys
with open(sys.argv[1], encoding="utf-8") as handle:
    settings = json.load(handle)
if not isinstance(settings.get("login"), dict) or not isinstance(settings.get("dashboard"), dict):
    raise SystemExit("Settings API response is missing login/dashboard settings")
PY

update_status="$(curl --silent --show-error \
	--cookie "$COOKIE_JAR" \
	--header "X-WP-Nonce: $nonce" \
	--header 'Content-Type: application/json' \
	--request PUT \
	--output "${RUNNER_TEMP}/rest-update.json" \
	--write-out '%{http_code}' \
	--data '{"login":{"title":"Brand Master Runtime Smoke"}}' \
	"$TEST_URL/wp-json/brand-master/v1/settings")"
[[ "$update_status" == '200' ]] || fail "Settings API update returned HTTP $update_status."
python3 - "${RUNNER_TEMP}/rest-update.json" <<'PY'
import json
import sys
with open(sys.argv[1], encoding="utf-8") as handle:
    settings = json.load(handle)
if settings.get("login", {}).get("title") != "Brand Master Runtime Smoke":
    raise SystemExit("Settings API did not persist the login title")
if not isinstance(settings.get("dashboard"), dict):
    raise SystemExit("Partial update discarded dashboard settings")
PY

core_status="$(curl --silent --show-error \
	--cookie "$COOKIE_JAR" \
	--header "X-WP-Nonce: $nonce" \
	--output "${RUNNER_TEMP}/core-settings-authenticated.json" \
	--write-out '%{http_code}' \
	"$TEST_URL/wp-json/wp/v2/settings")"
[[ "$core_status" == '200' ]] || fail "Core settings API returned HTTP $core_status."
if grep -Fq 'brand_master_options' "${RUNNER_TEMP}/core-settings-authenticated.json"; then
	fail 'Brand Master settings leaked through the core settings endpoint.'
fi

login_update_status="$(curl --silent --show-error \
	--cookie "$COOKIE_JAR" \
	--header "X-WP-Nonce: $nonce" \
	--header 'Content-Type: application/json' \
	--request PUT \
	--output "${RUNNER_TEMP}/rest-login-update.json" \
	--write-out '%{http_code}' \
	--data '{"login":{"url":{"on":true,"slug":"brand-master-smoke-login","redirect_slug":"brand-master-smoke-redirect"}}}' \
	"$TEST_URL/wp-json/brand-master/v1/settings")"
[[ "$login_update_status" == '200' ]] || {
	head -c 2000 "${RUNNER_TEMP}/rest-login-update.json" >&2 || true
	fail "Login settings API update returned HTTP $login_update_status."
}
python3 - "${RUNNER_TEMP}/rest-login-update.json" <<'PY'
import json
import sys
with open(sys.argv[1], encoding="utf-8") as handle:
    settings = json.load(handle)
if settings.get("login", {}).get("title") != "Brand Master Runtime Smoke":
    raise SystemExit("Login URL update discarded the saved title")
url = settings.get("login", {}).get("url", {})
if url.get("on") is not True or url.get("slug") != "brand-master-smoke-login" or url.get("redirect_slug") != "brand-master-smoke-redirect":
    raise SystemExit("Login URL settings were not persisted")
PY

"${WP[@]}" rewrite flush --hard

assert_status "$TEST_URL/brand-master-smoke-login" '200' "${RUNNER_TEMP}/custom-login.html"
assert_contains "${RUNNER_TEMP}/custom-login.html" 'login-action-login' 'Custom login slug'
assert_status "$TEST_URL/brand-master-smoke-login-tail" '404' "${RUNNER_TEMP}/custom-login-tail.html"

redirect_status="$(curl --silent --show-error \
	--output /dev/null \
	--dump-header "${RUNNER_TEMP}/wp-login-headers.txt" \
	--write-out '%{http_code}' \
	"$TEST_URL/wp-login.php")"
[[ "$redirect_status" == '302' ]] || fail "Default login URL returned HTTP $redirect_status."
if ! grep -Eiq '^location: .*brand-master-smoke-redirect' "${RUNNER_TEMP}/wp-login-headers.txt"; then
	fail 'Default login URL did not redirect to the configured redirect slug.'
fi

"${WP[@]}" plugin deactivate brand-master
"${WP[@]}" plugin activate brand-master
BM_SMOKE_PHASE='persistence' \
BM_EXPECTED_VERSION="$version" \
	"${WP[@]}" eval-file "${PROJECT_ROOT}/.github/scripts/wordpress-runtime-assertions.php"

if [[ -f "$WP_DIR/debug.log" ]] && grep -Eq 'PHP (Warning|Notice|Deprecated|Fatal error)|Uncaught' "$WP_DIR/debug.log"; then
	cat "$WP_DIR/debug.log" >&2
	fail 'WordPress logged PHP warnings, notices, deprecations, or fatal errors.'
fi

echo "Brand Master runtime smoke test passed on WordPress $WP_VERSION / PHP 8.3."
