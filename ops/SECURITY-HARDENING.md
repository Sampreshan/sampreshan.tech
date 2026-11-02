# Security source overlays

The 2026-11-03 remediation adds `wp-content/mu-plugins/sb-security-hardening.php`
to restrict the WordPress users REST endpoints to staff or the current user, disable
the WordPress file editor, and add compatible browser headers. The CSP limits base
URLs, object embeds and frame ancestors; it does not claim full script/XSS protection.

`wp-content/plugins/vp-firebase-auth/src/Rest.php` is an overlay for the existing
complete Firebase authentication plugin (0.2.5), not a standalone plugin installation.
It derives rate-limit buckets from the web-server remote address rather than a
client-supplied Cloudflare header. Trusted proxy normalization belongs to the server.

The same bytes are deployed on all four partner sites. The isolated regression test
covers anonymous access, self/other-profile authorization, staff access and spoofed
proxy headers. The source CI also rejects tracked private configuration and database
archives. It does not scan or certify all Git history or remove GitHub cached objects.

Production backup for this site: `/home/sampreshan.tech/backups/20261103-security-remediation/`.
Database restoration was tested in a separate non-networked MariaDB instance under
the non-root `codexdev` user; production data was not imported/restored. The promotion
used `/run/lock/hostinger-1677302-production-maintenance.lock`. Source hashes and
HTTP/REST health evidence are retained privately in the dated remediation report.
