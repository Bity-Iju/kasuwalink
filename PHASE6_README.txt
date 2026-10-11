KASUWALINK PHASE 6 — AUDIT LOG, BUSINESS DATA EXPORT & PWA SHELL

NEW FEATURES
- Owner-only Activity Log with date and action filters.
- Activity events for sales, expenses, product create/update/delete, staff account create/status change, debt repayments and backup exports.
- Owner-only JSON backup export containing this business's records; password hashes are excluded.
- PWA manifest, app icon, service worker and offline information page.
- The service worker deliberately does not cache PHP pages, authenticated screens, or business records.

INSTALL
1. Back up your existing C:\xampp\htdocs\kasuwalink folder and MySQL database.
2. Extract this ZIP and copy the contents of its kasuwalink folder into C:\xampp\htdocs\kasuwalink.
3. In phpMyAdmin, select the existing kasuwalink database and import phase6_migration.sql once.
4. Start Apache and MySQL, then log in.
5. Owner tools: http://localhost/kasuwalink/activity.php and http://localhost/kasuwalink/backup.php.

NOTES & LIMITATIONS
- The JSON download is a data export, not a one-click database restore. Test database backups and restoration separately.
- PWA service workers require localhost or HTTPS. Offline mode does not make live PHP pages or database data available offline.
- Activity logging is added to the listed workflows; it is not yet a complete immutable security audit for every possible action.
- A downloaded backup contains sensitive business/customer information. Store it securely and do not put it in a public web folder.
- PHP syntax checks and ZIP integrity checks were performed during packaging; live MySQL/XAMPP end-to-end tests have not been performed.
- Before public deployment: use HTTPS, a dedicated least-privilege MySQL user, server-side backups, rate limiting, security headers, and independent security testing.
