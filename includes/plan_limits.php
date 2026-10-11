<?php
declare(strict_types=1);
/** Returns the effective plan; expired paid plans fall back to Free limits. */
function kasuwalink_effective_plan(PDO $pdo, int $userId): string {
    $q=$pdo->prepare('SELECT plan,subscription_expires_at FROM users WHERE id=? LIMIT 1'); $q->execute([$userId]); $u=$q->fetch();
    if (!$u) return 'free';
    $plan=(string)($u['plan']??'free');
    if (!in_array($plan,['pro','business'],true)) return 'free';
    if (empty($u['subscription_expires_at']) || strtotime((string)$u['subscription_expires_at']) < time()) return 'free';
    return $plan;
}
function kasuwalink_plan_limits(PDO $pdo, int $userId): array {
    $plan=kasuwalink_effective_plan($pdo,$userId);
    if ($plan==='free') return ['plan'=>'free','product_limit'=>50,'staff_limit'=>1];
    try { $q=$pdo->prepare('SELECT product_limit,staff_limit FROM subscription_plan_settings WHERE plan_code=? AND is_active=1 ORDER BY id DESC LIMIT 1'); $q->execute([$plan]); $r=$q->fetch(); if($r) return ['plan'=>$plan,'product_limit'=>(int)$r['product_limit'],'staff_limit'=>(int)$r['staff_limit']]; } catch(Throwable $e) {}
    return $plan==='business'?['plan'=>$plan,'product_limit'=>0,'staff_limit'=>0]:['plan'=>$plan,'product_limit'=>500,'staff_limit'=>5];
}
