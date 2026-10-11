KASUWALINK PHASE 5 — REPORTS & LOW-STOCK ALERTS

NEW FEATURES
- Low-stock alert page listing products at/below their own configured threshold.
- Suggested reorder quantity and estimated reorder cost (planning estimates only).
- Low-stock CSV export.
- Dashboard links to low-stock items.
- Expanded reporting: collected amount, outstanding balances, gross profit estimate, expense total and estimated net result.
- CSV exports for sales history, expenses, current inventory valuation and monthly summary.
- CSV files include a UTF-8 BOM for improved Excel compatibility.

INSTALL
1. Back up the existing C:\xampp\htdocs\kasuwalink folder and the kasuwalink database.
2. Extract this ZIP and copy the contents of its kasuwalink folder into C:\xampp\htdocs\kasuwalink.
3. No database migration is required for Phase 5. Keep the existing database and earlier phase migrations/tables.
4. Start Apache and MySQL in XAMPP and sign in.
5. Visit http://localhost/kasuwalink/low_stock.php and http://localhost/kasuwalink/reports.php.

IMPORTANT
- Low-stock page requires the products permission for staff users; owners have access.
- Estimated reorder quantity is a suggested planning formula, not a purchase order.
- Estimated net result is informational, not a formal profit-and-loss statement.
- PHP syntax checks passed during packaging; a live MySQL/XAMPP end-to-end test has not been performed. Test with a backup and sample data first.
- Before public deployment, use HTTPS, a least-privilege MySQL account, regular backups, login rate limiting, and security review.
