KASUWALINK PHASE 3 — INVENTORY AUDIT TRAIL

NEW IN THIS VERSION
- Stock History page with date, product, and movement-type filters.
- Opening stock recorded when a product is created with a positive quantity.
- Manual quantity changes recorded with before/after quantities and the change reason.
- Every successful sale records the stock deduction in the same database transaction.
- Product names remain in the ledger even if a product record is later removed.
- Existing per-business scoping is applied to inventory history queries.

INSTALL / UPDATE ON WINDOWS XAMPP
1. Back up your current kasuwalink folder and database first.
2. Extract this ZIP. Copy the contents of its kasuwalink folder into C:\xampp\htdocs\kasuwalink, overwriting the old files.
3. Open http://localhost/phpmyadmin, select the kasuwalink database, choose Import, and import phase3_migration.sql. This creates the new inventory_movements table without deleting existing records. Alternatively, database.sql includes this table for a fresh installation.
4. Confirm includes/config.php uses the correct local database settings.
5. Open http://localhost/kasuwalink/inventory.php.

IMPORTANT LIMITATIONS
- The ledger begins recording from the time Phase 3 is installed; it cannot reconstruct historical stock movements.
- Existing product quantities remain as they are. Existing stock has no historical opening movement unless manually adjusted after migration.
- This remains a development starter, not a production-audited accounting platform. Back up data, use least-privilege DB credentials and HTTPS before deployment.
- PHP syntax checks were performed; live MySQL/XAMPP integration was not available for this build.
