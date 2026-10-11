KASUWALINK PHASE 10 — SUBSCRIPTION PLAN MANAGEMENT & LIMIT ENFORCEMENT

New:
- Platform admin page admin/plans.php to configure paid plan price, duration, features and product/staff limits.
- Subscription checkout uses active plan settings from the database.
- Server-side product limits when creating products and active-staff limits when creating staff accounts.
- Expired paid subscriptions use Free limits. Current illustrative Free limits: 50 products and 1 active staff account; Pro defaults: 500 products and 5 active staff; Business defaults: unlimited (limit 0).

Install:
1. Back up files and database.
2. Copy the contents of the kasuwalink folder into C:\xampp\htdocs\kasuwalink.
3. Import phase10_migration.sql into the existing kasuwalink database ONCE.
4. Ensure Phase 8 and Phase 9 migrations are already imported, plus previous migrations.
5. Log in as platform admin and visit /admin/plans.php.
6. Configure Paystack keys privately as described in PHASE9_README.txt; use test keys first.

Important:
- This adds limits for product creation and staff creation only; it does not yet gate every premium report/export feature.
- Existing paid accounts without a valid subscription_expires_at date fall back to Free limits. Check existing account data before deployment.
- The plan settings table is authoritative for checkout pricing after migration. Only admins should access admin pages.
- PHP syntax validation performed; live MySQL/Paystack integration not tested.
