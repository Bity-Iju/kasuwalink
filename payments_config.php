<?php
declare(strict_types=1);
// Configure PAYSTACK_SECRET_KEY in your server environment. Never commit a live key.
// For local testing, set it in Apache's environment or your private server config.
if (!defined('PAYSTACK_SECRET_KEY')) {
    define('PAYSTACK_SECRET_KEY', (string)(getenv('PAYSTACK_SECRET_KEY') ?: ''));
}
if (!defined('PAYSTACK_PUBLIC_KEY')) {
    define('PAYSTACK_PUBLIC_KEY', (string)(getenv('PAYSTACK_PUBLIC_KEY') ?: ''));
}
// Illustrative starter prices. Adjust before launch after deciding your pricing.
function kasuwalink_plans(?PDO $pdo = null): array {
    $fallback = [
        'pro_monthly' => ['plan'=>'pro','label'=>'Pro Monthly','amount'=>200000,'duration_days'=>30,'price'=>'₦2,000 / 30 days','features'=>['Advanced business reporting','CSV exports and stock tools','Up to 500 products and 5 staff accounts'],'product_limit'=>500,'staff_limit'=>5],
        'business_monthly' => ['plan'=>'business','label'=>'Business Monthly','amount'=>500000,'duration_days'=>30,'price'=>'₦5,000 / 30 days','features'=>['Inventory and team workflows','Unlimited products and staff accounts','For larger shops and teams'],'product_limit'=>0,'staff_limit'=>0],
    ];
    if (!$pdo) return $fallback;
    try {
        $rows = $pdo->query("SELECT plan_key,plan_code,label,amount_kobo,duration_days,product_limit,staff_limit,features_text FROM subscription_plan_settings WHERE is_active=1 ORDER BY id")->fetchAll();
        if (!$rows) return $fallback;
        $plans=[];
        foreach ($rows as $row) {
            $amount=(int)$row['amount_kobo'];
            $plans[$row['plan_key']]=['plan'=>$row['plan_code'],'label'=>$row['label'],'amount'=>$amount,'duration_days'=>(int)$row['duration_days'],'price'=>'₦'.number_format($amount/100).' / '.(int)$row['duration_days'].' days','features'=>array_values(array_filter(array_map('trim',preg_split('/\r?\n/',(string)$row['features_text'])))),'product_limit'=>(int)$row['product_limit'],'staff_limit'=>(int)$row['staff_limit']];
        }
        return $plans ?: $fallback;
    } catch (Throwable $e) { return $fallback; }
}
function paystack_request(string $method, string $path, ?array $body=null): array {
    if (PAYSTACK_SECRET_KEY === '') throw new RuntimeException('Paystack is not configured. Set PAYSTACK_SECRET_KEY on the server first.');
    if (!function_exists('curl_init')) throw new RuntimeException('PHP cURL extension is required for Paystack requests. Enable it in php.ini and restart Apache.');
    $ch = curl_init('https://api.paystack.co'.$path);
    $headers = ['Authorization: Bearer '.PAYSTACK_SECRET_KEY, 'Content-Type: application/json', 'Accept: application/json'];
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_CUSTOMREQUEST=>strtoupper($method), CURLOPT_HTTPHEADER=>$headers, CURLOPT_CONNECTTIMEOUT=>10, CURLOPT_TIMEOUT=>25]);
    if ($body !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_SLASHES));
    $raw = curl_exec($ch); $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE); $err = curl_error($ch); curl_close($ch);
    if ($raw === false) throw new RuntimeException('Unable to contact Paystack: '.$err);
    $json = json_decode((string)$raw, true);
    if (!is_array($json) || $status < 200 || $status >= 300 || empty($json['status'])) throw new RuntimeException('Paystack request failed. Check your keys and transaction settings.');
    return $json;
}
function subscription_apply_verified(PDO $pdo, string $reference, array $verified): bool {
    if (($verified['status'] ?? '') !== 'success' || ($verified['currency'] ?? '') !== 'NGN') return false;
    $q=$pdo->prepare('SELECT * FROM subscription_payments WHERE reference=? LIMIT 1'); $q->execute([$reference]); $payment=$q->fetch();
    if (!$payment || (int)($verified['amount'] ?? -1) !== (int)$payment['amount_kobo']) return false;
    $meta=$verified['metadata'] ?? [];
    if (isset($meta['user_id']) && (int)$meta['user_id'] !== (int)$payment['user_id']) return false;
    $pdo->beginTransaction();
    try {
        $q=$pdo->prepare('SELECT status FROM subscription_payments WHERE id=? FOR UPDATE'); $q->execute([(int)$payment['id']]); $current=$q->fetchColumn();
        if ($current === 'success') { $pdo->commit(); return true; }
        if ($current !== 'pending') { $pdo->commit(); return false; }
        $q=$pdo->prepare("UPDATE subscription_payments SET status='success',gateway_transaction_id=?,paid_at=NOW(),gateway_response=? WHERE id=? AND status='pending'");
        $q->execute([isset($verified['id'])?(string)$verified['id']:null, json_encode(['status'=>'success','channel'=>$verified['channel']??null], JSON_UNESCAPED_SLASHES), (int)$payment['id']]);
        $q=$pdo->prepare('SELECT subscription_expires_at FROM users WHERE id=? FOR UPDATE'); $q->execute([(int)$payment['user_id']]); $expiry=$q->fetchColumn();
        if ($expiry === false) { throw new RuntimeException('Business account not found.'); }
        $base = ($expiry && strtotime((string)$expiry) > time()) ? (string)$expiry : date('Y-m-d H:i:s');
        $newExpiry = date('Y-m-d H:i:s', strtotime($base.' +'.(int)$payment['duration_days'].' days'));
        $q=$pdo->prepare('UPDATE users SET plan=?,subscription_expires_at=? WHERE id=?'); $q->execute([$payment['plan'], $newExpiry, (int)$payment['user_id']]);
        $pdo->commit(); return true;
    } catch (Throwable $e) { if ($pdo->inTransaction()) $pdo->rollBack(); throw $e; }
}
