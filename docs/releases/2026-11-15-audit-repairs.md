# Audit remediation: 15 November 2026

This release fixes the petition public-resource boundary and anonymous account-ID
redaction, preserves current quiz auto-next while allowing readers to stop it with
a meaning control, adds the shared glossary/quality modules and restricted admin
audit, improves the homepage primary CTA and login footer, aligns the site/parent
organization schema, supplies update metadata from existing text, enables profile
and update sitemap types, and holds newly imported non-English updates for editing.
Existing card IDs, option strings, learner histories and public slugs are preserved.

Local verification: 2,039 quiz assertions, 22 petition privacy checks, 24 SEO and
publishing checks, 254 meaning-control assertions per engine at all 11 required
widths in Chromium and WebKit, plus the existing 56-check browser suite. Homepage
and sign-in/register/recovery preview produced 96 observations with no page errors.
All 96 PHP files linted; source language/icon and sensitive-file checks passed.

The glossary currently defines the six existing labels only. The remaining 994
unique labels need definitions. Twenty-four near-duplicate candidates were reviewed
and retained: they ask about different deities, roles, texts, locations, directions,
chakras, stages or modes of practice. No question bank content was deleted/reindexed.
Imported article translations, substantive summaries and event provenance remain
editorial work; a generated meta description is not full article enrichment.

Historical SQL objects, GitHub cache cleanup, Analytics PR 87/billing, the complete
authenticated member/provider/email journey and latest nightly-cloud recovery
remain separately tracked. Current-main parity, exact-head CI, server promotion,
backup restoration and rendered live acceptance are recorded separately in the
private `reports/sampreshan-fixes-20261115` evidence directory. Credentials are outside
Git and webroot. See DEPLOYMENT-RUNBOOK.md for the deployment/rollback boundary.

The first promotion reverted its complete file set when the sitemap smoke gate failed.
Rank Math had cached its settings before the theme filter was registered; the final
patch updates only the two sitemap keys in its initialized runtime settings cache.
The retry retains the same data and restores no production database.

The sitemap corpus fits within 1,000 URLs. A 1,000-entry page size keeps imported
records with identical modification timestamps on one XML page and avoids page-boundary
duplication during independently cached sitemap generation. Verify exact URL-set
coverage against the published database inventory after cache invalidation.
