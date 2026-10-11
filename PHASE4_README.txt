KASUWALINK PHASE 4 — STAFF ACCOUNTS & PERMISSIONS

WHAT IS INCLUDED
- Owner-only Staff page to create employee logins.
- Module permissions for sales, products, customers, expenses, reports, inventory history, and debts.
- Enable/disable staff accounts without deleting their history.
- Staff login uses the business owner's data scope; staff cannot select another business.
- Existing owner login remains supported.

INSTALL
1. Back up C:\xampp\htdocs\kasuwalink and your kasuwalink database.
2. Extract this ZIP and copy the contents of its kasuwalink folder into C:\xampp\htdocs\kasuwalink.
3. Open http://localhost/phpmyadmin, select the existing kasuwalink database, choose Import, and import phase4_migration.sql.
4. Start Apache and MySQL, then log in as the business owner.
5. Open http://localhost/kasuwalink/staff.php to create staff accounts.
6. Staff members log in through the same login.php page using their staff email and password.

IMPORTANT
- Do not import database.sql over an existing database as a replacement; import only the migration.
- Passwords are stored using PHP password_hash. The application checks permissions on protected module pages, not only by hiding navigation links.
- Dashboard is available to staff users; financial and operational modules require explicit permissions.
- This ZIP was syntax-checked with PHP. A live MySQL/XAMPP end-to-end test was not available. Test with a backup and dummy accounts first.
- For production use, configure a non-root MySQL user, HTTPS, routine backups, email/password recovery, login rate limiting, and a review of every action endpoint.
