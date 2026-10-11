<?php
require_once __DIR__.'/includes/config.php'; require_once __DIR__.'/payments_config.php';
require_owner();
$uid=(int)$_SESSION['user_id']; $errors=[]; $notice='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf(); $key=(string)($_POST['plan_key']??''); $plans=kasuwalink_plans($pdo);
 if(!isset($plans[$key])) $errors[]='Please choose a valid subscription plan.';
 elseif(PAYSTACK_SECRET_KEY==='') $errors[]='Payments are not configured yet. The platform administrator must configure Paystack keys.';
 else {
  try {
   $q=$pdo->prepare('SELECT email,business_name FROM users WHERE id=? LIMIT 1'); $q->execute([$uid]); $user=$q->fetch();
   if(!$user) throw new RuntimeException('Business account was not found.');
   $plan=$plans[$key]; $reference='KSW-'.strtoupper(bin2hex(random_bytes(12)));
   $q=$pdo->prepare("INSERT INTO subscription_payments (user_id,plan,plan_key,amount_kobo,currency,duration_days,reference,status) VALUES (?,?,?,?,?,?,?,'pending')");
   $q->execute([$uid,$plan['plan'],$key,$plan['amount'],'NGN',$plan['duration_days'],$reference]);
   $baseUrl=(isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off'?'https':'http').'://'.($_SERVER['HTTP_HOST']??'localhost').rtrim(str_replace('\\','/',dirname($_SERVER['SCRIPT_NAME']??'/')), '/');
   $result=paystack_request('POST','/transaction/initialize',['email'=>$user['email'],'amount'=>$plan['amount'],'currency'=>'NGN','reference'=>$reference,'callback_url'=>$baseUrl.'/subscription_callback.php','metadata'=>['user_id'=>$uid,'business_name'=>$user['business_name'],'plan_key'=>$key],'channels'=>['card','bank','bank_transfer','ussd']]);
   if(empty($result['data']['authorization_url'])) throw new RuntimeException('Payment link was not returned by Paystack.');
   header('Location: '.$result['data']['authorization_url']); exit;
  } catch(Throwable $e){$errors[]=$e->getMessage();}
 }
}
$q=$pdo->prepare('SELECT plan,subscription_expires_at FROM users WHERE id=?');$q->execute([$uid]);$current=$q->fetch();
$q=$pdo->prepare('SELECT plan,amount_kobo,currency,status,reference,created_at,paid_at FROM subscription_payments WHERE user_id=? ORDER BY id DESC LIMIT 10');$q->execute([$uid]);$payments=$q->fetchAll();
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Plans & billing · Kasuwalink</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="assets/style.css" rel="stylesheet"><style>body{background:#f5f7fb}.wrap{max-width:1100px}.plan{border:1px solid #e3e9f2;border-radius:18px;background:#fff;height:100%}.price{font-size:2rem;font-weight:800;letter-spacing:-.04em}.muted{color:#697789}</style></head><body><main class="container wrap py-4 py-md-5"><div class="d-flex justify-content-between align-items-center mb-4"><div><a href="dashboard.php" class="text-decoration-none">← Dashboard</a><h1 class="h2 fw-bold mt-3 mb-1">Plans & billing</h1><p class="muted">Manage your Kasuwalink subscription.</p></div><span class="badge text-bg-primary">Current plan: <?=e(ucfirst($current['plan']??'free'))?></span></div>
<?php foreach($errors as $error):?><div class="alert alert-danger"><?=e($error)?></div><?php endforeach;?>
<div class="alert alert-info">Prices are starter examples in Nigerian naira. Payments are activated only after Paystack confirms the transaction on the server. A paid subscription extends from the later of today or your current expiry date.</div>
<div class="row g-3 mb-5"><?php foreach(kasuwalink_plans($pdo) as $key=>$plan):?><div class="col-md-6"><div class="plan p-4"><div class="text-uppercase small fw-semibold text-primary mb-2"><?=e($plan['label'])?></div><div class="price"><?=e($plan['price'])?></div><ul class="mt-3 mb-4"><?php foreach($plan['features'] as $feature):?><li class="mb-2"><?=e($feature)?></li><?php endforeach;?></ul><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="plan_key" value="<?=e($key)?>"><button class="btn btn-primary w-100">Continue to secure payment</button></form></div></div><?php endforeach;?></div>
<div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mb-4"><h2 class="h5 fw-bold">Subscription status</h2><p class="mb-1">Plan: <strong><?=e(ucfirst($current['plan']??'free'))?></strong></p><p class="mb-0">Expiry: <strong><?=!empty($current['subscription_expires_at'])?e(date('d M Y',strtotime($current['subscription_expires_at']))):'No paid expiry recorded'?></strong></p></div>
<div class="card border-0 shadow-sm rounded-4 p-3 p-md-4"><h2 class="h5 fw-bold">Recent payment attempts</h2><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Date</th><th>Plan</th><th>Amount</th><th>Reference</th><th>Status</th></tr></thead><tbody><?php foreach($payments as $p):?><tr><td><?=e(date('d M Y H:i',strtotime($p['created_at'])))?></td><td><?=e(ucfirst($p['plan']))?></td><td><?=money(((int)$p['amount_kobo'])/100)?></td><td><code><?=e($p['reference'])?></code></td><td><span class="badge text-bg-<?=$p['status']==='success'?'success':($p['status']==='failed'?'danger':'secondary')?>"><?=e(ucfirst($p['status']))?></span></td></tr><?php endforeach;?><?php if(!$payments):?><tr><td colspan="5" class="muted text-center py-4">No payment attempts yet.</td></tr><?php endif;?></tbody></table></div></div></main></body></html>
