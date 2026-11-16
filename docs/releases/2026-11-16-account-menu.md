# Member photo menu

The active BuddyBoss desktop/mobile header now receives a child-theme account
menu at the member photo, with Dashboard, Account, Activity, Notifications and
Logout. The displayed name uses readable ink. Activity opens the current member's
profile activity section; Notifications uses the existing nonce-protected AJAX
store. Parent-theme files and account passwords remain unchanged.

The old global `a[href*="wp-login.php"]` hiding rule also hid WordPress logout
links. The rule now preserves `action=logout`, with WordPress's session-bound nonce
and the homepage return URL. Changed scripts/styles use content hashes.

Preview: 1,500 assertions and 132 viewport observations across home, dashboard,
profile, settings, feed and quiz, in Chromium/WebKit at all 11 required widths.
Checks cover layout, touch target sizes, keyboard/Enter/Escape, outside dismissal,
notifications, and logout nonce presence. Tests use one short-lived QA session;
notification mark-read requests are mocked to preserve the owner's unread state.
Live navigation and actual logout are separate release checks.

Run `tools/tests/account-menu-browser.cjs` with PLAYWRIGHT_PATH (if needed),
SP_ACCOUNT_SESSION_FILE, SP_ACCOUNT_EVIDENCE, and optional CHROME_PATH/WEBKIT_PATH.
SP_LIVE=1 tests served code; preview uses candidate markup in the private session
file. No persistent synthetic member, petition, signature or quiz attempt is created.
