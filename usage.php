<?php
require_once 'TermgameSellerV2.php';

// สร้าง instance
$api = new TermgameSellerV2('YOUR_API_KEY');

// ตรวจสอบยอดเงิน
$balance = $api->getBalanceAmount();
echo "ยอดเงิน: " . $api->formatPrice($balance);

// ดึงรายการสินค้า
$products = $api->getActiveProducts();

// ค้นหาและซื้อ
$product = $api->findProductByName('Freefire');
$package = $api->findPackageByName($product, '68 เพชร');

if ($api->hasEnoughBalance($package['price'])) {
    $result = $api->buyWithPlayerId($package['id'], '123456789');
}