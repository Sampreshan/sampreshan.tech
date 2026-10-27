# Production verification: Sangyani Sanatan, 28 October 2026

Live page: https://sampreshan.tech/sanatan-quiz/

| Evidence | Result | Scope |
| --- | --- | --- |
| Protocol regression | PASS, 2,026 checks | Isolated memory storage; timeout, duplicate receipt, stale card, guest claim, ownership and all 500 cards |
| WordPress/InnoDB regression | PASS, 1,018 checks | Temporary tables on the actual VPS; full course, ten logs, verification/revocation, revision and member resume without a browser token |
| Browser fixtures | PASS, 56 checks | 320, 390, 768 and 1440px; connection failure, blocked browser storage, expired-card recovery, next-card visibility on a 320×640px screen, hidden Next, long certificate name and print visibility |
| Live rendered browser | PASS, 29 checks | Same four widths; network interruption, reload recovery, concurrent duplicate answers, stale rejection, save/exit, no overflow and no JavaScript page errors |
| Live timed challenge | PASS, 8 checks | Resume kept the original question and reduced remaining time; after 32 seconds the server rejected the answer, deducted five points once and returned the same receipt on replay |
| Concurrent initial answer | PASS | Two simultaneous submissions for a fresh card returned identical receipts and counted exactly one answer |
| Admin and privacy registration | PASS, 10 checks | Restricted editor rendered through actual WordPress; review controls, reporting, analytics, certificate tools, cleanup cron and privacy hooks registered |
| Local source / deployed source | PASS | Seven theme files and ten private bank files have matching SHA-256 hashes |
| Public asset delivery | PASS | Normal and cache-busting HTML reference the expected content hashes; downloaded CSS/JS bytes match source |
| Legacy public bank URLs | PASS | All ten URLs checked normally and with cache-busting: twenty HTTP 404 results |
| Scoped cleanup | PASS | Synthetic guest runs, logs and their aggregate answer contributions removed; temporary database tables removed; no public certificate issued |
| Syntax and policy | PASS | 94 theme PHP files, JavaScript syntax, workflow YAML/shell parsing, English display and no-emoji checks |

On the measured 390px mobile viewport, the primary start action moved from the audit's approximately 824px to approximately 538px. Learning preferences are expandable. A saved guest journey can resume without retyping a name.

Production copies were replaced after a theme and database backup. Only this release's theme files and private runtime bank were changed by the scoped deployment. Existing WordPress runs, certificate IDs and unrelated plugin/configuration changes were retained. The dedicated deployment account has traverse access to the private parent and write access specifically to this bank, with inherited WordPress read access tested using a temporary probe.

Full-course certificates in automated tests were created only inside isolated storage, not the public production tables. Authenticated claiming and member resume were verified using WordPress callbacks in isolated tables; a real interactive member login was not performed. Browser print visibility is verified, but no independent human acceptance of a physical print is asserted. The 500 editorial recall/learning notes and question register do not constitute independent scholarly certification; that review remains explicitly pending. No separate invigilated examination or hidden assessment pool is claimed.

Detailed screenshots and JSON receipts are retained privately in the operator workspace under `backups/sangyani-fix-20261027/evidence/`. They are deliberately not copied into the served theme. CI status must be read from the workflow run for the final commit; manual live proof above is independent of that status.
