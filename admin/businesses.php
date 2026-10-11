<?php
require_once __DIR__ . '/_guard.php'; require_platform_admin();
$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf(); $id=(int)($_POST['business_id']??0); $action=(string)($_POST['action']??'');
 if($id<1){$errors[]='Invalid business selected.';}
 elseif($action==='save'){
  $plan=(string)($_POST['plan']??'free'); $expiry=trim((string)($_POST['subscription_expires_at']??''));
  if(!in_array($plan,['free','pro','business'],true))$errors[]='Select a valid plan.';
  if($expiry!=='' && !preg_match('/^\d{4}-\d{2}-\d{2}$/',$expiry))$errors[]='Enter a valid subscription expiry date.';
  if(!$errors){$dt=$expiry!==''?$expiry.' 23:59:59':null;$q=$pdo->prepare('UPDATE users SET plan=?,subscription_expires_at=? WHERE id=?');$q->execute([$plan,$dt,$id]);$_SESSION['admin_flash']=['type'=>'success','message'=>'Business plan settings updated.'];header('Location: businesses.php');exit;}
 } elseif($action==='toggle'){
  $q=$pdo->prepare('UPDATE users SET is_active=IF(is_active=1,0,1) WHERE id=?');$q->execute([$id]);
  $_SESSION['admin_flash']=['type'=>'success','message'=>$q->rowCount()?'Business account status updated.':'No business account was changed.'];header('Location: businesses.php');exit;
 } else {$errors[]='Unknown action.';}
}
$search=trim((string)($_GET['q']??''));$status=(string)($_GET['status']??'all');
$sql='SELECT u.id,u.business_name,u.full_name,u.email,u.plan,u.is_active,u.subscription_expires_at,u.created_at,COALESCE(p.product_count,0) product_count,COALESCE(s.sales_total,0) sales_total FROM users u LEFT JOIN (SELECT user_id,COUNT(*) product_count FROM products GROUP BY user_id) p ON p.user_id=u.id LEFT JOIN (SELECT user_id,SUM(total) sales_total FROM sales GROUP BY user_id) s ON s.user_id=u.id WHERE 1=1';$args=[];
if($search!==''){$sql.=' AND (u.business_name LIKE ? OR u.full_name LIKE ? OR u.email LIKE ?)';$like='%'.$search.'%';array_push($args,$like,$like,$like);}
if($status==='active')$sql.=' AND u.is_active=1';elseif($status==='disabled')$sql.=' AND u.is_active=0';
$sql.=' ORDER BY u.created_at DESC LIMIT 300';$q=$pdo->prepare($sql);$q->execute($args);$businesses=$q->fetchAll();
admin_page_start('Businesses');
?>
<div class="mb-4"><div class="text-uppercase small muted fw-semibold">ACCOUNT MANAGEMENT</div><h1 class="h3 fw-bold mb-1">Businesses</h1><p class="muted mb-0">Search accounts, assign a plan and enable or disable access.</p></div>
<?php foreach($errors as $error):?><div class="alert alert-danger"><?=e($error)?></div><?php endforeach;?>
<div class="card p-3 mb-4"><form class="row g-2 align-items-end" method="get"><div class="col-md-7"><label class="form-label">Search</label><input class="form-control" name="q" placeholder="Business, owner or email" value="<?=e($search)?>"></div><div class="col-md-3"><label class="form-label">Status</label><select class="form-select" name="status"><option value="all" <?=$status==='all'?'selected':''?>>All accounts</option><option value="active" <?=$status==='active'?'selected':''?>>Active</option><option value="disabled" <?=$status==='disabled'?'selected':''?>>Disabled</option></select></div><div class="col-md-2 d-grid"><button class="btn btn-primary">Filter</button></div></form></div>
<div class="card p-3 p-md-4"><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Business / owner</th><th>Plan & expiry</th><th>Products</th><th>Total recorded sales</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach($businesses as $b):?><tr><td><strong><?=e($b['business_name'])?></strong><div class="small muted"><?=e($b['full_name'])?></div><div class="small muted"><?=e($b['email'])?></div><div class="small muted">Joined <?=e(date('d M Y',strtotime($b['created_at'])))?></div></td><td><form method="post" class="d-flex flex-column gap-2" style="min-width:145px"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="save"><input type="hidden" name="business_id" value="<?=(int)$b['id']?>"><select class="form-select form-select-sm" name="plan"><option value="free" <?=$b['plan']==='free'?'selected':''?>>Free</option><option value="pro" <?=$b['plan']==='pro'?'selected':''?>>Pro</option><option value="business" <?=$b['plan']==='business'?'selected':''?>>Business</option></select><input class="form-control form-control-sm" type="date" name="subscription_expires_at" value="<?=e($b['subscription_expires_at']?date('Y-m-d',strtotime($b['subscription_expires_at'])):'')?>"><button class="btn btn-sm btn-outline-primary">Save plan</button></form></td><td><?=(int)$b['product_count']?></td><td><?=money($b['sales_total'])?></td><td><span class="badge text-bg-<?=$b['is_active']?'success':'secondary'?>"><?=$b['is_active']?'Active':'Disabled'?></span></td><td><form method="post" onsubmit="return confirm('Change this business account status?');"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="business_id" value="<?=(int)$b['id']?>"><input type="hidden" name="action" value="toggle"><button class="btn btn-sm btn-outline-<?=$b['is_active']?'danger':'success'?>"><?=$b['is_active']?'Disable':'Enable'?></button></form></td></tr><?php endforeach;?>
<?php if(!$businesses):?><tr><td colspan="6" class="text-center muted py-5">No matching businesses found.</td></tr><?php endif;?></tbody></table></div><div class="small muted">Showing up to 300 matching businesses. Use search to narrow the list.</div></div>
<?php admin_page_end(); ?>
