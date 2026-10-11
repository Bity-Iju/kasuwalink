<?php
require_once __DIR__.'/_guard.php'; require_platform_admin();
$errors=[];
try { $rows=$pdo->query('SELECT * FROM subscription_plan_settings ORDER BY id')->fetchAll(); } catch(Throwable $e) { $rows=[]; $errors[]='Import phase10_migration.sql before managing plans.'; }
if($_SERVER['REQUEST_METHOD']==='POST') {
 verify_csrf(); $key=(string)($_POST['plan_key']??'');
 if(!in_array($key,['pro_monthly','business_monthly'],true)) $errors[]='Invalid plan selected.';
 $label=trim((string)($_POST['label']??'')); $amount=filter_var($_POST['amount_naira']??null,FILTER_VALIDATE_INT); $days=filter_var($_POST['duration_days']??null,FILTER_VALIDATE_INT); $products=filter_var($_POST['product_limit']??null,FILTER_VALIDATE_INT); $staff=filter_var($_POST['staff_limit']??null,FILTER_VALIDATE_INT); $features=trim((string)($_POST['features_text']??'')); $active=isset($_POST['is_active'])?1:0;
 if($label===''||mb_strlen($label)>80)$errors[]='Plan label must be 1–80 characters.';
 if($amount===false||$amount<0||$amount>100000000)$errors[]='Enter a valid whole-naira price.';
 if($days===false||$days<1||$days>366)$errors[]='Duration must be between 1 and 366 days.';
 if($products===false||$products<0||$staff===false||$staff<0)$errors[]='Limits must be zero or a positive whole number. Use 0 for unlimited.';
 if(!$errors) { $q=$pdo->prepare('UPDATE subscription_plan_settings SET label=?,amount_kobo=?,duration_days=?,product_limit=?,staff_limit=?,features_text=?,is_active=? WHERE plan_key=?'); $q->execute([$label,$amount*100,$days,$products,$staff,$features,$active,$key]); $_SESSION['admin_flash']=['type'=>'success','message'=>'Subscription plan settings saved.']; header('Location: plans.php'); exit; }
}
try { $rows=$pdo->query('SELECT * FROM subscription_plan_settings ORDER BY id')->fetchAll(); } catch(Throwable $e) { $rows=[]; }
admin_page_start('Subscription plans');
?>
<div class="mb-4"><div class="text-uppercase small muted fw-semibold">MONETISATION</div><h1 class="h3 fw-bold mb-1">Subscription plans</h1><p class="muted mb-0">Configure prices, billing duration and server-enforced product/staff limits.</p></div>
<?php foreach($errors as $error):?><div class="alert alert-danger"><?=e($error)?></div><?php endforeach;?>
<div class="alert alert-info">Limits are enforced when a business adds a product or creates a staff account. A limit of 0 means unlimited. Expired Pro/Business subscriptions fall back to Free limits. This page does not automatically refund or renew payments.</div>
<?php foreach($rows as $r):?><div class="card p-3 p-md-4 mb-4"><form method="post" class="row g-3"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="plan_key" value="<?=e($r['plan_key'])?>"><div class="col-md-6"><label class="form-label">Plan name</label><input class="form-control" name="label" maxlength="80" required value="<?=e($r['label'])?>"></div><div class="col-md-3"><label class="form-label">Price (NGN)</label><input class="form-control" type="number" min="0" step="1" name="amount_naira" required value="<?=(int)round(((int)$r['amount_kobo'])/100)?>"></div><div class="col-md-3"><label class="form-label">Duration (days)</label><input class="form-control" type="number" min="1" max="366" name="duration_days" required value="<?=(int)$r['duration_days']?>"></div><div class="col-md-6"><label class="form-label">Product limit (0 = unlimited)</label><input class="form-control" type="number" min="0" name="product_limit" required value="<?=(int)$r['product_limit']?>"></div><div class="col-md-6"><label class="form-label">Active staff limit (0 = unlimited)</label><input class="form-control" type="number" min="0" name="staff_limit" required value="<?=(int)$r['staff_limit']?>"></div><div class="col-12"><label class="form-label">Features (one per line)</label><textarea class="form-control" rows="3" name="features_text"><?=e($r['features_text']??'')?></textarea></div><div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" id="active_<?=e($r['plan_key'])?>" <?=$r['is_active']?'checked':''?>><label class="form-check-label" for="active_<?=e($r['plan_key'])?>">Available for new checkout</label></div></div><div class="col-12"><button class="btn btn-primary">Save <?=e($r['plan_code'])?> plan</button></div></form></div><?php endforeach;?>
<?php admin_page_end(); ?>
