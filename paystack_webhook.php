<?php
require_once __DIR__.'/includes/config.php'; require_once __DIR__.'/payments_config.php';
// Configure this endpoint in the Paystack dashboard. No browser session is used.
$raw=file_get_contents('php://input') ?: '';
$signature=(string)($_SERVER['HTTP_X_PAYSTACK_SIGNATURE']??'');
if(PAYSTACK_SECRET_KEY==='' || $signature==='' || !hash_equals(hash_hmac('sha512',$raw,PAYSTACK_SECRET_KEY),$signature)){http_response_code(401);exit('Invalid signature');}
$event=json_decode($raw,true);
if(!is_array($event) || ($event['event']??'')!=='charge.success'){http_response_code(200);exit('Ignored');}
$data=$event['data']??[]; $reference=(string)($data['reference']??'');
if($reference===''){http_response_code(400);exit('Missing reference');}
try {
 // Paystack webhook payload is signed, but verify against Paystack API as an additional server-side check.
 $verified=paystack_request('GET','/transaction/verify/'.rawurlencode($reference));
 if(!subscription_apply_verified($pdo,$reference,$verified['data']??[])){http_response_code(422);exit('Payment not applied');}
 http_response_code(200);echo 'OK';
} catch(Throwable $e){http_response_code(500);echo 'Verification temporarily unavailable';}
