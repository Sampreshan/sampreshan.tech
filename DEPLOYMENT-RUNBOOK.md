# Sampreshan deployment runbook

Verified 15 November 2026. Repository: `Sampreshan/sampreshan.tech`; release branch: `main`.
Active source is `buddyboss-theme-child/`. Production is Hostinger VPS 1677302,
`/home/sampreshan.tech/public_html`, site user `sampr4722`, active BuddyBoss child theme.
WordPress and MariaDB own users, sessions, petitions, signatures and quiz progress.
The private 10-file quiz bank is `/home/sampreshan.tech/private/sangyani`.
Uploads, configuration, databases, caches and credentials are excluded from source promotion.

## Release paths

`.github/workflows/wordpress-theme.yml` validates and deploys theme/bank changes on
main when WP_DEPLOY_* secrets are configured. It uses the production environment and
`wordpress-theme-production` concurrency. A successful security workflow alone does
not prove theme deployment. GitHub Pages is a separate static demo.

Scoped manual promotion uses the existing server maintenance lock
`/run/lock/hostinger-1677302-production-maintenance.lock`. Pause the independent theme
workflow, verify no running/queued deploys, then hold the lock throughout backup,
expected-before hash checks, file promotion and health checks/rollback. Restore the
workflow's prior state afterward. Do not merge or push main during that transaction.
The maintenance lock by itself does not exclude an independently running CI deployer.

Analytics is a separate MU plugin, `wp-content/mu-plugins/sampreshan-google-tag.php`.
As of the audit, its live content corresponds to PR 87 rather than main. Recheck the
PR and exact-head CI; never overwrite unrelated MU plugins with a theme deployment.

## Gates and rollback

Run PHP lint, `node --check buddyboss-theme-child/assets/js/quiz.js`,
`php tools/tests/sangyani-regression.php`, `php tools/tests/sangyani-audit.php`,
`php tools/tests/petition-privacy.php`, `php tools/tests/seo-publishing.php`,
the offline browser fixtures, emoji/language policy checks and `git diff --check`.
Inspect all changed public components at 320,360,375,390,430,768,820,1024,1180,1366,
1920 pixels in Chromium and WebKit before release. Record the page inventory and
screenshots; a mocked fixture does not prove the full authenticated member journey.

Back up only the release's target files plus a current private database export;
verify the file archive can be extracted with identical hashes. Record database
restore verification separately. Pin each before/after file hash and the source
commit, including dirty status. Abort on unexpected target or drift.

Restore the exact prior file set if syntax/health/hash checks fail. Do not replace
the production database or learner progress as a code rollback. Do not restore old
exposed passwords or salts. Clear LiteSpeed after promotion/rollback, then check
normal and cache-busting URLs and served asset hashes. Record receipt and remaining
CI, content, recovery and journey limitations in the private report directory.

## Credentials and historical data

Credentials go only in owner-readable files outside Git and webroot. A password
hash check and successful HTTP login are distinct from Google/OTP provider tests.
Historical SQL exports still require approved history rewriting and GitHub retained
object cleanup. Removing files from main does not remove public historical objects.
