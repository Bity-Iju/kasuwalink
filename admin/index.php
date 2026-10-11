<?php
require_once __DIR__ . '/_guard.php'; require_platform_admin();
$stats = [
 'businesses'=>(int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
 'active'=>(int)$pdo->query('SELECT COUNT(*) FROM users WHERE is_active=1')->fetchColumn(),
 'disabled'=>(int)$pdo->query('SELECT COUNT(*) FROM users WHERE is_active=0')->fetchColumn(),
 'staff'=>(int)$pdo->query('SELECT COUNT(*) FROM staff_users')->fetchColumn(),
 'products'=>(int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn(),
];
$sales30 = (float)$pdo->query('SELECT COALESCE(SUM(total),0) FROM sales WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)')->fetchColumn();
$recent = $pdo->query('SELECT u.id,u.business_name,u.full_name,u.email,u.plan,u.is_active,u.created_at,COALESCE(s.sale_count,0) sale_count,COALESCE(s.sales_total,0) sales_total FROM users u LEFT JOIN (SELECT user_id,COUNT(*) sale_count,SUM(total) sales_total FROM sales WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY user_id) s ON s.user_id=u.id ORDER BY u.created_at DESC LIMIT 8')->fetchAll();
admin_page_start('Overview');
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="text-uppercase small muted fw-semibold">PLATFORM CONTROL CENTER</div><h1 class="h3 fw-bold mb-1">Admin overview</h1><p class="muted mb-0">Monitor registered businesses and platform activity.</p></div><a class="btn btn-primary" href="businesses.php">Manage businesses</a></div>
<div class="row g-3 mb-4">
<?php foreach([['Registered businesses',$stats['businesses'],'building-2'],['Active accounts',$stats['active'],'check-circle'],['Disabled accounts',$stats['disabled'],'ban'],['Staff accounts',$stats['staff'],'users'],['Products listed',$stats['products'],'package'],['Sales value · last 30 days',money($sales30),'chart-no-axes-combined']] as $s):?><div class="col-6 col-lg-4"><div class="card p-3 p-md-4 h-100"><div class="muted small mb-2"><?=e($s[0])?></div><div class="stat"><?=e((string)$s[1])?></div></div></div><?php endforeach;?></div>
<div class="card p-3 p-md-4"><div class="d-flex justify-content-between align-items-center mb-3"><div><h2 class="h5 fw-bold mb-1">Recently registered businesses</h2><p class="muted small mb-0">Sales shown are gross recorded sales for the last 30 days, across the platform.</p></div><a href="businesses.php" class="small">View all</a></div><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Business</th><th>Plan</th><th>30-day sales</th><th>Status</th><th>Registered</th></tr></thead><tbody><?php foreach($recent as $b):?><tr><td><strong><?=e($b['business_name'])?></strong><div class="small muted"><?=e($b['full_name'])?> · <?=e($b['email'])?></div></td><td><span class="badge text-bg-light"><?=e(ucfirst($b['plan']))?></span></td><td><?=money($b['sales_total'])?><div class="small muted"><?=(int)$b['sale_count']?> sales</div></td><td><span class="badge text-bg-<?=$b['is_active']?'success':'secondary'?>"><?=$b['is_active']?'Active':'Disabled'?></span></td><td><?=e(date('d M Y',strtotime($b['created_at'])))?></td></tr><?php endforeach;?><?php if(!$recent):?><tr><td colspan="5" class="text-center muted py-4">No businesses have registered yet.</td></tr><?php endif;?></tbody></table></div></div>
<div class="alert alert-warning small mt-4">Platform sales are the sum of recorded sales, not platform revenue or subscription income. Payment collection and subscription billing are not integrated in this phase.</div>
<?php admin_page_end(); ?>
