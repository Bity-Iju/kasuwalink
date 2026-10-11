<?php
require_once __DIR__.'/includes/config.php'; require_once __DIR__.'/payments_config.php';
if(empty($_SESSION['user_id'])) { flash('Please sign in to review your subscription payment.','warning'); redirect('login.php'); }
$uid=(int)$_SESSION['user_id']; $reference=trim((string)($_GET['reference']??''));
if($reference==='' || !preg_match('/^[A-Za-z0-9_-]{8,100}$/',$reference)){flash('The payment reference is invalid.','danger');redirect('subscription.php');}
try {
 $q=$pdo->prepare('SELECT id FROM subscription_payments WHERE reference=? AND user_id=? LIMIT 1');$q->execute([$reference,$uid]);
 if(!$q->fetchColumn()){http_response_code(404);exit('Payment reference not found for this account.');}
 $response=paystack_request('GET','/transaction/verify/'.rawurlencode($reference));
 if(subscription_apply_verified($pdo,$reference,$response['data']??[])) flash('Payment verified. Your subscription has been updated.');
 else { $q=$pdo->prepare("UPDATE subscription_payments SET status='failed' WHERE reference=? AND user_id=? AND status='pending'");$q->execute([$reference,$uid]); flash('Payment has not been verified as successful. If you were charged, contact support with your reference: '.$reference,'warning'); }
} catch(Throwable $e){flash('We could not verify the payment right now. Your payment reference is '.$reference.'. Please revisit billing or contact support before paying again.','warning');}
redirect('subscription.php');
