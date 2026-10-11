KASUWALINK PHASE 8 — PLATFORM ADMIN PAGES
========================================

Included:
- Separate platform administrator login and session.
- One-time first-admin setup page.
- Admin overview with business, staff, product and last-30-day recorded sales totals.
- Business account search and status filters.
- Enable/disable business accounts (disabled owners can no longer log in).
- Assign Free, Pro or Business plan labels and an optional subscription expiry date.
- Admin-only routes are separate from ordinary business owner and staff pages.

INSTALL ON EXISTING XAMPP PROJECT
1. Back up C:\xampp\htdocs\kasuwalink and the kasuwalink database.
2. Copy the contents of this folder into C:\xampp\htdocs\kasuwalink.
3. In phpMyAdmin, select the existing kasuwalink database and import phase8_migration.sql ONCE.
4. Visit http://localhost/kasuwalink/admin/setup.php and create the first administrator.
5. Immediately delete or rename admin/setup.php after the first admin is created.
6. Sign in at http://localhost/kasuwalink/admin/login.php.
7. The admin overview is at http://localhost/kasuwalink/admin/ and business management is at /admin/businesses.php.

NOTES / LIMITATIONS
- This phase adds plan labels and expiry dates only. It does NOT charge subscriptions, enforce paid-feature limits, or connect Paystack/Flutterwave.
- The admin dashboard's sales totals are recorded business sales, not Kasuwalink platform revenue.
- Before internet deployment: enforce HTTPS, change DB credentials to a dedicated least-privilege DB user, remove setup.php, use strong unique admin passwords, add admin login rate limiting, and test account isolation.
- Import migration only once; it adds columns to users and creates admin_users.
- PHP syntax checks do not substitute for end-to-end testing against a live MySQL/XAMPP setup.
