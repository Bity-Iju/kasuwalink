<?php
require_once __DIR__.'/includes/config.php'; require_owner();
if($_SERVER['REQUEST_METHOD']!=='POST'){ $pageTitle='Business backup'; require __DIR__.'/includes/header.php'; ?>
<div class="page-heading"><div><div class="text-secondary small">OWNER TOOLS</div><h1 class="h3 fw-bold mb-1">Business data backup</h1><p class="muted mb-0">Download a portable JSON snapshot of this business's records.</p></div></div>
<div class="row g-4"><div class="col-lg-8"><div class="card"><div class="card-body p-4"><div class="d-flex gap-3 align-items-start"><div class="rounded-3 p-3 bg-primary-subtle text-primary"><span class="fs-3">⇩</span></div><div><h2 class="h5 fw-bold">Export your business data</h2><p class="text-secondary">The file contains your products, customers, sales and sale items, expenses, debt payments, inventory movements, staff account settings, and activity logs.</p><p class="small text-secondary">Passwords are not included. Keep the downloaded file private and store it somewhere safe. This is an export, not an automated cloud backup or one-click restore.</p><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><button class="btn btn-primary">Download JSON backup</button></form></div></div></div></div><div class="col-lg-4"><div class="alert alert-warning"><strong>Important</strong><p class="mb-0 small">For full disaster recovery, also back up your MySQL database and test restoration on a separate database. Do not upload backup files to a public folder.</p></div></div></div>
<?php require __DIR__.'/includes/footer.php'; exit; }
verify_csrf();
$uid=(int)$_SESSION['user_id'];
$queries=[
 'business'=>['SELECT id,business_name,full_name,email,created_at FROM users WHERE id=?',[$uid]],
 'products'=>['SELECT id,name,sku,cost_price,selling_price,quantity,low_stock_threshold,created_at FROM products WHERE user_id=? ORDER BY id',[$uid]],
 'customers'=>['SELECT id,name,phone,created_at FROM customers WHERE user_id=? ORDER BY id',[$uid]],
 'sales'=>['SELECT id,customer_id,total,amount_paid,payment_method,notes,created_at FROM sales WHERE user_id=? ORDER BY id',[$uid]],
 'expenses'=>['SELECT id,title,amount,expense_date,created_at FROM expenses WHERE user_id=? ORDER BY id',[$uid]],
 'debt_payments'=>['SELECT id,sale_id,amount,note,paid_at FROM debt_payments WHERE user_id=? ORDER BY id',[$uid]],
 'inventory_movements'=>['SELECT id,product_id,product_name,movement_type,quantity_change,quantity_before,quantity_after,reference_type,reference_id,note,created_at FROM inventory_movements WHERE user_id=? ORDER BY id',[$uid]],
 'activity_logs'=>['SELECT id,actor_id,actor_type,actor_name,action,entity_type,entity_id,details,created_at FROM activity_logs WHERE user_id=? ORDER BY id',[$uid]],
 'staff'=>['SELECT id,full_name,email,can_sales,can_products,can_customers,can_expenses,can_reports,can_inventory,can_debts,is_active,created_at FROM staff_users WHERE owner_user_id=? ORDER BY id',[$uid]],
];
$backup=['app'=>'Kasuwalink','format_version'=>1,'exported_at'=>date(DATE_ATOM),'business_id'=>$uid,'data'=>[]];
foreach($queries as $key=>[$sql,$params]){ $q=$pdo->prepare($sql);$q->execute($params);$backup['data'][$key]=$q->fetchAll(); }
// Sale items are included only for sales belonging to this business.
$q=$pdo->prepare('SELECT si.id,si.sale_id,si.product_id,si.product_name,si.quantity,si.unit_price,si.unit_cost FROM sale_items si JOIN sales s ON s.id=si.sale_id WHERE s.user_id=? ORDER BY si.id');$q->execute([$uid]);$backup['data']['sale_items']=$q->fetchAll();
$json=json_encode($backup,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
if($json===false){http_response_code(500);exit('Could not create backup.');}
audit_log($pdo,'business_backup_exported','backup',null,'JSON business data export downloaded');
header('Content-Type: application/json; charset=utf-8');header('Content-Disposition: attachment; filename="kasuwalink-backup-'.$uid.'-'.date('Ymd-His').'.json"');header('X-Content-Type-Options: nosniff');header('Cache-Control: no-store, private');echo $json;exit;
