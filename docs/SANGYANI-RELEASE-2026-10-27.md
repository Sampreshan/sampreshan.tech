# Sangyani Sanatan: resumable learning release

The previous quiz lost the guest resume token after a failed answer request, trusted a browser timer and could count a duplicate request against a different card. This release preserves a question ID and deadline across reloads, serialises run changes with a database lock and transaction, and retains the last answer receipt until the learner explicitly continues. Resume recovers that receipt after a lost response. Failed requests retain the browser token.

## Learner experience

- Self-paced Learn is the default. Challenge has a server-enforced 30-second deadline; its timer continues away from the page. Existing runs retain their original timed behaviour.
- Ten-card revision creates a separate, untimed run and preserves the main journey. Incorrect cards form a revision notebook; correct answers remove them from that run's notebook. Ten attempts trigger a pause reminder.
- Every card has a concise learning or recall note. Topic names replace the unsupported promise of ascending scholarly difficulty. Questions and options shuffle without changing stable card indices used by existing progress.
- Compact mobile entry and gameplay, working hidden states, labelled controls, keyboard support, ten-second announcement, sound off by default, gentle motion and background animation pause.
- Guests can explicitly attach progress to their signed-in account. Save and exit keeps progress; deletion explicitly erases the selected journey and its certificate record.
- Completion certificates describe a course completed through study and revision. Names fit the certificate width; an HTML equivalent, PNG download, print view, sharing and public verification are available. Previously issued IDs remain valid unless revoked or erased.

## Editorial and administrative controls

WordPress **Tools > Sangyani Sanatan** provides a draft editor, reviewed publication with internal evidence and reviewer identity, report resolution, per-card attempt counts, name correction and certificate revocation. Active cards retain the answer/options and explanation issued before an editorial update. Dashboard statistics aggregate all journeys; the history labels its 50-row and 30-topic display limits explicitly.

`content/sangyani/review-register.json` records all 500 stable IDs, question hashes, topic and review status. The Saptarishi question now names the present Vaivasvata Manvantara; ambiguous largest-Upanishad and universal Ayurveda classification wording were corrected. Traditional medical terminology is presented without making efficacy claims. This is editorial learning material, not independently certified scholarship: the register deliberately does not invent human scholarly approval. Recall notes are not exhaustive commentaries. Future reasoning-based assessment would require a separately reviewed assessment bank and evaluation policy.

## Runtime content and deployment

The educational source bank remains in this public repository under `content/sangyani/`; historical copies were also public. It is not a secret examination bank. Deploy its ten TXT files to `/home/sampreshan.tech/private/sangyani/`, outside `public_html`, readable by the WordPress site user. The theme defaults to `dirname(ABSPATH) . '/private/sangyani'`; another installation can define `SP_QUIZ_BANK_DIR` in its configuration. **Theme-only deployment is insufficient on a new installation.** Do not put that directory back under a publicly served theme path.

The GitHub workflow now validates the protocol and syncs the ten bank files outside the webroot before deploying the theme. A dedicated deploy account needs traverse access on the private parent and a read/write ACL on this bank directory, with an inherited read ACL for the WordPress user. No general access to other private directories is required. New installations must provision that access before enabling automated deployment.

Deploy only these theme files, after the private bank is ready:

- `inc/quiz/quiz.php`, `runtime.php`, `admin.php`, `explanations.json`
- `assets/js/quiz.js`, `assets/css/quiz.css`
- `template-quiz.php`

Remove the ten old `inc/quiz/bank/NN.txt` files from the public theme after preserving a private backup. Do not copy tests or tooling into the webroot. Runtime schema 2.0.0 adds aggregate stats and reports tables without removing existing runs/logs. Quiz CSS and JS use content hashes to avoid stale same-theme-version assets. Purge page caches, verify both normal and cache-busting requests, old bank URLs, REST no-store headers, and downloaded asset hashes. Quiz HTML and REST responses are explicitly excluded from caching.

Back up the theme, database and private content before later deployments. The current release's pre-change backup is outside the webroot at `/home/sampreshan.tech/backups/sangyani-20261027/`. Roll back matching PHP/JS/CSS/template files as a set, restore the old bank path if rolling back the reader, and purge caches. Retain the new tables; avoid replacing the database after real users have continued learning.

## Privacy

Profiles stay private by default. Guest run tokens are bearer credentials stored only in the browser and database, never analytics. REST responses use `private, no-store`. Public certificate links disclose recipient name, date and score; the interface explains this before sharing. Inactive guest progress without a certificate expires after 90 days. WordPress personal-data export and erasure include member quiz records. Erasure removes corresponding certificate verification. Aggregate question counters and reports contain no user ID or raw IP. Rate-limit keys use salted hashes; transient expiry discards them.

## Verification

- `php tools/tests/sangyani-regression.php`: isolated protocol and all-card tests.
- `node tools/tests/sangyani-browser.cjs`: Playwright fixture tests at 320, 390, 768 and 1440px, failed-network recovery and long certificate names. Set `PLAYWRIGHT_PATH` and `CHROME_PATH` where needed.
- `wp --skip-themes eval-file /private/candidate/tools/tests/sangyani-wordpress.php`: actual WordPress callbacks against temporary InnoDB tables. It creates no public certificates and drops its temporary tables in `finally`. It needs a complete candidate checkout and existing quiz tables. Never expose this script through HTTP.
- Syntax, English-display and no-emoji checks accompany the regression suites.

Live rendered-browser results and deployed hash receipts are recorded separately in the release evidence report. Fixture tests do not by themselves certify production behaviour or independent human scholarly review.
