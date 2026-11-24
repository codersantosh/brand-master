# WordPress.org deployment

## Overview

- Plugin slug: `brand-master`
- Publication target: `https://plugins.svn.wordpress.org/brand-master/`
- Workflow: `.github/workflows/deploy.yml`
- Publication triggers: a stable published GitHub Release, or an approved manual dispatch using an exact `vX.Y.Z` tag
- Publishing environment: `wordpress-org`
- Manual dispatch: `dry_run` defaults to checked

Git remains the development source. WordPress.org SVN is used only as a release
repository. The workflow never writes to a developer's local SVN checkout.

## Verified release requirements

| Declaration | Value |
| --- | --- |
| Plugin version | `1.0.6` |
| Minimum PHP | `8.3` |
| Minimum WordPress | `7.0` |
| Tested WordPress | `7.1` |

`Tested up to` is declared only in `readme.txt`. WordPress Plugin Check rejects
that field in the main plugin PHP header.

PHP 8.3 is the minimum because PHP 8.1 is end-of-life, PHP 8.2 security support
ends on December 31, 2026, and PHP 8.3 receives security fixes through December
31, 2027. WordPress 7.0 and 7.1 support PHP 8.3, and WordPress developer guidance
recommends PHP 8.3.

## Release flow

1. The `package` job resolves one immutable Git commit. Real publishing requires
   an exact versioned tag whose version matches `package.json` and whose commit
   is the checked-out commit.
2. It installs locked Composer dependencies, validates Composer metadata, runs
   PHP syntax checks, PHPUnit, and PHPCS, then builds the release package. The
   reusable WordPress runtime workflow then rebuilds and tests that exact commit
   on the 7.0/7.1 smoke-test matrix. Every matrix job must match the package ZIP
   digest, and deployment compares both the tested commit and digest before
   publishing.
3. The validator checks metadata, current-only readme changelog, full changelog,
   runtime references, residual ATRC placeholders, package exclusions, and
   byte-for-byte ZIP/runtime equivalence.
4. The ZIP has fixed entry timestamps, normalized permissions, sorted paths, and
   a checksum. The translation-template timestamp is normalized from the Git
   commit timestamp, so identical source commits produce identical ZIPs.
5. The artifact name includes the version, commit, and ZIP digest prefix. A
   provenance manifest records the full ZIP SHA-256, runtime file count, commit,
   tag, workflow run, and release URL.
6. The `dry-run` job always runs before deployment. It downloads the package
   artifact, verifies its manifest and digest, stages `trunk/` and a new SVN tag
   in a disposable working copy, and uploads the SVN status, diff, file manifest,
   and release provenance for review. It receives no SVN credentials.
7. The protected `deploy` job starts only after the dry run and WordPress runtime
   matrix succeed. Required environment reviewers can inspect the preview before
   approving it. It downloads the same artifact and verifies its digest against
   the dry-run output, so the package committed to SVN is the package that was
   staged for review.
8. The SVN script updates `trunk/`, stages additions and deletions, creates
   `tags/<version>/` with `svn copy`, and commits once. The commit message records
   the Git commit, artifact SHA-256, Actions run URL/attempt, and release URL.
9. Immediately after commit, the script exports remote `trunk/` and the release
   tag, checks both revisions, compares every file with the reviewed bundle, and
   reruns release validation on both exports.

The leading `v` in a Git tag such as `v1.0.6` is removed for WordPress.org. The
plugin version remains `1.0.6`.

## First manual dry run

GitHub only exposes `workflow_dispatch` when the workflow exists on the default
branch. The selected ref must also contain the workflow file.

1. Open **Actions → Deploy Brand Master to WordPress.org → Run workflow**.
2. Select the reviewed ref. A dry run may use a branch, commit, or tag.
3. Leave `dry_run` checked.
4. Review the uploaded package artifact and `svn-preview-*` artifact.

A dry run never requests SVN secrets and the SVN script refuses credentials in
dry-run mode.

## Manual real deployment

1. Create and push the exact annotated `vX.Y.Z` tag only after release approval.
2. Confirm the tag version and all plugin version fields agree.
3. Dispatch the workflow with that exact tag as `ref` and `dry_run` unchecked.
4. Review the automatically generated SVN preview.
5. Approve the protected `wordpress-org` environment deployment.

The workflow rejects branch names, arbitrary refs, missing tags, tags that do not
resolve to the checked-out commit, and version mismatches before building a real
deployment. Manual publishing does not require a GitHub Release, but its
tag/release URL is recorded in provenance.

A stable, published GitHub Release follows the same package, dry-run, approval,
and verification path. The release tag must already exist and match the package
version.

## One-time WordPress.org setup

1. Use an individual WordPress.org account listed as a **Committer** on the
   Brand Master plugin's Advanced View page:
   `https://wordpress.org/plugins/brand-master/advanced/`
2. Enable Two-Factor Authentication.
3. Open **Account & Security → SVN credentials**:
   `https://profiles.wordpress.org/me/profile/edit/group/3/?screen=svn-password`
4. Generate and securely store the SVN-specific password.
5. Record the exact WordPress.org username, including capitalization. It is not
   the account email address.

Do not use the normal WordPress.org account password. Generating a new SVN
password invalidates the previous one.

## GitHub setup

These repository settings require separate maintainer approval; the workflow
cannot create or modify them.

Create an environment named `wordpress-org`:

1. Open **Settings → Environments → New environment**.
2. Add the authorized maintainer(s) under **Required reviewers**.
3. Restrict deployment to tags matching `v*`.
4. Add environment secrets:

| Secret | Value |
| --- | --- |
| `SVN_USERNAME` | Exact WordPress.org committer username |
| `SVN_PASSWORD` | Generated WordPress.org SVN password |

Protect release tags with a repository ruleset:

1. Target `v*` tags.
2. Restrict tag creation and deletion to authorized maintainers.
3. Require pull requests for changes where supported.
4. Prevent force updates and tag deletion.

The workflow requests only `contents: read`. The dry-run job does not reference
the protected environment or SVN secrets. The deploy job alone receives the
environment secrets and starts after the dry run and environment approval.

A read-only authentication check is:

```bash
read -rp "WordPress.org username: " SVN_USERNAME
read -rsp "WordPress.org SVN password: " SVN_PASSWORD
echo
svn info --non-interactive --no-auth-cache \
  --username "$SVN_USERNAME" --password "$SVN_PASSWORD" \
  https://plugins.svn.wordpress.org/brand-master/
unset SVN_PASSWORD
```

This verifies authentication but not commit permission. The Advanced View page
must also list the account as a Committer.

## Runtime smoke tests

CI runs `.github/scripts/wordpress-runtime-smoke.sh` against disposable
WordPress 7.0 and 7.1 installations on PHP 8.3. Each run installs the built ZIP
and verifies:

- plugin activation, version, defaults, and full changelog access;
- dashboard/login shortcode rendering;
- the Brand Master admin page and compiled admin assets;
- anonymous REST denial and authenticated settings read/update;
- partial-update preservation and absence from the core settings endpoint;
- exact custom-login slug matching, HTTP 200 login responses, and `wp-login.php`
  redirect to the configured redirect slug;
- settings persistence across deactivation/reactivation;
- absence of PHP warnings, notices, deprecations, and fatal errors.

The test server and database contents are disposable GitHub Actions resources.
WordPress.org update-check APIs are stubbed and other external HTTP requests are
blocked, so runtime failures reflect the plugin rather than network availability.

## Package contents

Included:

- PHP runtime code
- Compiled `build/` assets
- Runtime `assets/`
- Translation template
- `readme.txt`, full `changelog.txt`, `LICENSE`, and `uninstall.php`

Excluded:

- Git and GitHub files
- Node and Composer dependencies, including `composer.lock`
- JavaScript/SCSS source
- Tests and CI scripts
- Development configuration
- Source maps, Markdown files, archives, hidden files, and symlinks

`readme.txt` contains only the current release changelog for WordPress.org. The
admin “What's New” reader and `changelog.txt` retain the complete history.

The validator checks literal `BRAND_MASTER_PATH` and `BRAND_MASTER_URL`
references, relative CSS `url()` references, and unreplaced ATRC placeholders
against the packaged files.

## SVN layout

```text
SVN root/
├── assets/          # WordPress.org listing media; untouched by this release
├── trunk/           # Verified runtime package, directly inside trunk
│   ├── brand-master.php
│   ├── readme.txt
│   ├── changelog.txt
│   ├── assets/      # Runtime assets shipped with the plugin
│   └── ...
└── tags/
    ├── 1.0.5/       # Existing release; preserved
    └── 1.0.6/       # New svn copy of trunk
```

The top-level SVN `assets/` directory is intentionally not synchronized. The
plugin's own runtime `assets/` directory remains inside `trunk/` and release
tags. ZIP files are never uploaded to SVN.

## Local validation

```bash
composer install --no-interaction --prefer-dist
composer validate --strict --no-check-publish
npm ci --no-audit --no-fund
npm run deploy
php .github/scripts/validate-release.php 1.0.6 deploy/brand-master
python3 .github/scripts/verify-artifact.py 1.0.6 deploy
composer lint
composer test
vendor/bin/phpcs --report=summary --warning-severity=0
```

`npm run deploy` creates the ZIP checksum automatically. Set `SOURCE_DATE_EPOCH`
to a Unix timestamp to reproduce a package for another source date; otherwise
the current Git commit timestamp is used. Rebuilding the same commit without
source changes should produce the same ZIP SHA-256.

The SVN script accepts `SVN_URL=file:///path/to/disposable/repository` and
`SVN_WORK_DIR=/path/to/disposable/working-copy` for local simulation. Dry runs
require `DRY_RUN=true`, no SVN credentials, a full `RELEASE_COMMIT`, and a
`PACKAGE_SHA256`. The script supports disposable local real commits as well,
including remote export verification.

## Release procedure

Only after implementation review and separate publication approval:

1. Confirm all version fields are `1.0.6`.
2. Confirm PHP 8.3 checks and WordPress 7.0/7.1 runtime smoke tests passed.
3. Commit the reviewed `composer.lock` if dependencies changed.
4. Select the exact reviewed commit for `v1.0.6`.
5. Create and push the annotated tag.
6. Run the manual workflow with `dry_run` checked and review both artifacts.
7. Either dispatch the exact tag with `dry_run` unchecked, or publish a stable
   GitHub Release.
8. Approve the `wordpress-org` environment deployment after reviewing the
   preview generated by that same run.
9. Confirm the workflow's immediate remote SVN verification passed.
10. After WordPress.org processing, verify the Plugin Directory API and download
    ZIP manually. Processing can take several hours.

## Delayed WordPress.org verification

Immediate SVN verification does not imply that WordPress.org has rebuilt its
public download ZIP. After processing, check:

```bash
curl --fail --silent --show-error \
  https://api.wordpress.org/plugins/info/1.2/?action=plugin_information\&request%5Bslug%5D=brand-master
curl --fail --silent --show-error --location --output /tmp/brand-master.zip \
  https://downloads.wordpress.org/plugin/brand-master.1.0.6.zip
unzip -tqq /tmp/brand-master.zip
```

Confirm the API reports `1.0.6` and inspect the downloaded package before
announcing the release.

## Rollback

Never modify or reuse an existing SVN release tag. Prefer a fix-forward release
with a higher version. In an emergency, an authorized committer can temporarily
point `Stable tag` in `trunk/readme.txt` back to the previous known-good tag, but
a new release should follow.
