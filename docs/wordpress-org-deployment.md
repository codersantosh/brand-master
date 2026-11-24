# WordPress.org deployment

## Overview

- Plugin slug: `brand-master`
- Publication target: `https://plugins.svn.wordpress.org/brand-master/`
- Workflow: `.github/workflows/deploy.yml`
- Publication trigger: a published, non-prerelease GitHub Release
- Publishing environment: `wordpress-org`
- Manual dispatch: credential-free SVN dry run only

Git remains the development source. WordPress.org SVN is used only as a release
repository. The workflow never writes to a developer's local SVN checkout.

## Verified release requirements

| Declaration | Value |
| --- | --- |
| Plugin version | `1.0.6` |
| Minimum PHP | `8.3` |
| Minimum WordPress | `7.0` |
| Tested WordPress | `7.1` |

PHP 8.3 is the minimum because PHP 8.1 is end-of-life, PHP 8.2 security support
ends on December 31, 2026, and PHP 8.3 receives security fixes through December
31, 2027. WordPress 7.0 and 7.1 support PHP 8.3, and WordPress developer guidance
recommends PHP 8.3.

## Release flow

1. The `package` job checks out one immutable Git commit.
2. It runs PHP syntax checks, PHPUnit, PHPCS, and the JavaScript release build.
3. It validates plugin metadata, runtime references, package exclusions, the ZIP
   checksum, and byte-for-byte ZIP/runtime equivalence.
4. It uploads the runtime directory, ZIP, and checksum as one artifact.
5. A manual dispatch runs a credential-free dry run against a disposable SVN
   working copy.
6. Publishing a stable GitHub Release runs the `deploy` job in the protected
   `wordpress-org` environment.
7. The deploy job checks out the exact commit recorded by the package job and
   downloads that job's artifact.
8. The SVN script updates `trunk/`, stages additions and deletions, creates
   `tags/<version>/` with `svn copy`, and commits once.

The leading `v` in a Git tag such as `v1.0.6` is removed for WordPress.org. The
plugin version remains `1.0.6`.

## First manual dry run

GitHub only exposes `workflow_dispatch` for a workflow when that workflow file
exists on the repository's default branch. The selected ref must also contain
the workflow file.

This implementation does not merge or push anything. Therefore, the first
manual dry run cannot be enabled from the local `1.0.6` branch alone. After
separate approval to update the default branch:

1. Merge or otherwise place the reviewed workflow on `master`.
2. Ensure the ref selected for dispatch also contains the workflow.
3. Open **Actions → Deploy Brand Master to WordPress.org → Run workflow**.
4. Select the reviewed ref and run it.

Until then, use the local validation and disposable SVN simulation commands in
this document. Do not merge or push solely to enable a dry run without approval.

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

Create an environment named `wordpress-org`:

1. Open **Settings → Environments → New environment**.
2. Add the authorized maintainer(s) under **Required reviewers**.
3. Restrict deployment to tags matching `v*`.
4. Add environment secrets:

| Secret | Value |
| --- | --- |
| `SVN_USERNAME` | Exact WordPress.org committer username |
| `SVN_PASSWORD` | Generated WordPress.org SVN password |

The workflow requests only `contents: read`. The dry-run job does not reference
SVN secrets, does not use the protected environment, and the SVN script refuses
to run if credentials are present.

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

## Package contents

Included:

- PHP runtime code
- Compiled `build/` assets
- Runtime `assets/`
- Translation template
- `readme.txt`, `LICENSE`, and `uninstall.php`

Excluded:

- Git and GitHub files
- Node and Composer dependencies
- JavaScript/SCSS source
- Tests and CI scripts
- Development configuration
- Source maps, Markdown files, archives, hidden files, and symlinks

The validator checks literal `BRAND_MASTER_PATH` and `BRAND_MASTER_URL`
references and relative CSS `url()` references against the packaged files.

## SVN layout

```text
SVN root/
├── assets/          # WordPress.org listing media; untouched by this release
├── trunk/           # Verified runtime package, directly inside trunk
│   ├── brand-master.php
│   ├── readme.txt
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
npm ci --no-audit --no-fund
npm run deploy
php .github/scripts/validate-release.php 1.0.6 deploy/brand-master
sha256sum deploy/brand-master-1.0.6.zip \
  > deploy/brand-master-1.0.6.zip.sha256
python3 .github/scripts/verify-artifact.py 1.0.6 deploy
composer lint
composer test
vendor/bin/phpcs --report=summary --warning-severity=0
```

The SVN script accepts `SVN_URL=file:///path/to/disposable/repository` and
`SVN_WORK_DIR=/path/to/disposable/working-copy` for local simulation. Dry runs
require `DRY_RUN=true` and no SVN credentials.

## Release procedure

Only after the implementation is reviewed and release publication is separately
approved:

1. Confirm all version fields are `1.0.6`.
2. Confirm PHP 8.3 runtime checks and WordPress 7.0/7.1 smoke tests have passed.
3. Select the exact reviewed commit for `v1.0.6`.
4. Create and push the annotated tag.
5. Create a draft GitHub Release and wait for CI.
6. Run the manual dry run and review its SVN preview.
7. Publish the stable GitHub Release.
8. Approve the `wordpress-org` environment deployment.
9. Verify the SVN tag and, after WordPress.org processing, the download ZIP and
   Plugin Directory API.

WordPress.org can take several hours to rebuild download ZIPs.

## Rollback

Never modify or reuse an existing SVN release tag. Prefer a fix-forward release
with a higher version. In an emergency, an authorized committer can temporarily
point `Stable tag` in `trunk/readme.txt` back to the previous known-good tag, but
a new release should follow.
