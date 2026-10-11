Kasuwalink Phase 7 — Security hardening and launch preparation

WHAT'S NEW
- Database-backed login throttling: after 5 failed attempts for the same email + IP pair, login is blocked for 15 minutes.
- Successful login clears the matching failed-attempt record.
- Basic browser security headers: nosniff, SAMEORIGIN frame policy, strict-origin referrer policy, and disabled camera/microphone/geolocation permissions.
- Successful owner/staff login events are sent to the existing activity log when available.

INSTALLATION
1. Back up the whole project folder and the MySQL database first.
2. Extract the ZIP and copy the contents of kasuwalink_phase7 into C:\xampp\htdocs\kasuwalink.
3. Open phpMyAdmin, choose the existing kasuwalink database, and import phase7_migration.sql.
4. Start Apache and MySQL, then test owner login and staff login.

IMPORTANT
- This migration only adds the login_attempts table; it does not delete existing records.
- Keep includes/config.php database credentials appropriate for your environment. XAMPP's root account with no password is for local development only, not production.
- Before hosting online, use HTTPS, create a dedicated MySQL account with least-privilege permissions, set secure session cookie behavior, disable public error display, configure backups, and review all authorization checks.
- Rate limiting uses email + IP address. Deployments behind a reverse proxy must configure trusted client-IP handling at the server; do not trust arbitrary forwarded headers.
- PHP syntax checks are not a substitute for live MySQL, browser, security, and authorization tests. Test before using with real business data.
