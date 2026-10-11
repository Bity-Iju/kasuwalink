KASUWALINK PHASE 2 UPGRADE
==========================

Includes the original Kasuwalink MVP plus:
- Multi-product cart sales with transactional stock locking and rollback.
- Printable receipt page for each sale.
- Product editing page.
- Customer debt dashboard with partial repayment records.
- Navigation link to Debts.

INSTALL / UPDATE ON XAMPP
1. Back up your existing C:\xampp\htdocs\kasuwalink folder and database first.
2. Extract this archive. It contains a kasuwalink_phase2 folder; copy its contents into your existing project folder (or rename the folder to kasuwalink).
3. Keep your existing includes/config.php database credentials if they differ from the XAMPP defaults.
4. No schema changes are required for the supplied database.sql schema, because debt_payments and sale_items are already included.
5. Start Apache and MySQL in XAMPP, then visit http://localhost/kasuwalink/.

IMPORTANT
- Test on a backup/local database before replacing an existing live project.
- This is a development MVP, not a production-hardened accounting system.
- Stock edits are not yet written to a separate inventory audit ledger.
- Receipt printing uses the browser print dialog.
- This upgrade requires the original MVP tables from database.sql.
