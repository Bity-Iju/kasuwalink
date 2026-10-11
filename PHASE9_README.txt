KASUWALINK PHASE 9 — SUBSCRIPTIONS AND PAYSTACK CHECKOUT
=======================================================

Included:
- Owner-only Plans & Billing page at /subscription.php.
- Illustrative Pro and Business monthly plans (NGN 2,000 and NGN 5,000); change these prices in payments_config.php before launch.
- Server-side Paystack transaction initialization and verification.
- Payment reference storage and idempotent subscription application.
- Paystack signed webhook endpoint at /paystack_webhook.php, with API verification as an additional check.
- Admin payments page at /admin/payments.php, showing verified subscription income separately from shop sales.

INSTALL
1. Back up the project folder and database.
2. Copy this folder's contents into C:\xampp\htdocs\kasuwalink.
3. Import phase9_migration.sql into the existing kasuwalink database ONCE.
4. Enable PHP cURL extension in php.ini (remove leading semicolon from extension=curl if present), then restart Apache.
5. Configure PAYSTACK_SECRET_KEY on the web server environment. For production, use the live secret key only in the hosting provider's secret/environment settings; never put it in public source control. Use Paystack test keys for sandbox testing.
6. Set the Paystack webhook URL to https://YOUR-DOMAIN/paystack_webhook.php in the Paystack dashboard.
7. Visit /subscription.php while signed in as a business owner. Admin payment history is at /admin/payments.php.

SECURITY / TESTING NOTES
- Do not use live keys or collect real payments until HTTPS is enabled and end-to-end testing is complete.
- Callback is not treated as proof of payment: the server verifies the reference against Paystack and checks status, NGN currency, exact amount, and user metadata where provided.
- The webhook validates X-Paystack-Signature and re-verifies the transaction via Paystack API. Configure the webhook URL only on HTTPS in production.
- Prices are illustrative, not a market-validated recommendation. Confirm pricing, tax, refund policy, support process, and terms before launch.
- Phase 9 does not yet enforce feature-level limits for Free/Pro/Business plans. It records and updates plan/expiry only.
- Existing PHP syntax and archive integrity should be tested locally; a syntax pass is not a substitute for Paystack sandbox, webhook, database, and authorization testing.
- Ensure your Phase 8 migration has been applied before this migration, since users.plan and users.subscription_expires_at are used by this project.
