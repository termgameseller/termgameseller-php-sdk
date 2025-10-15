# Termgame Seller V2 PHP SDK

PHP SDK สำหรับเชื่อมต่อกับ Termgame Seller V2 API อย่างง่ายและรวดเร็ว

## คุณสมบัติ

-   ✅ รองรับ API ทั้งหมดของ Termgame Seller V2
-   ✅ เขียนด้วย PHP แบบ OOP ใช้งานง่าย
-   ✅ มี Helper Functions ที่ช่วยในการจัดการข้อมูล
-   ✅ Error Handling ที่สมบูรณ์
-   ✅ มีเอกสารและตัวอย่างครบถ้วน
-   ✅ รองรับ PHP 7.4 ขึ้นไป

## การติดตั้ง

### วิธีที่ 1: ดาวน์โหลดไฟล์โดยตรง

1. ดาวน์โหลด `class.tgseller.php`
2. นำไฟล์ไปวางในโปรเจคของคุณ
3. เรียกใช้งานด้วย `require_once`

```php
<?php
require_once 'class.tgseller.php';

$api = new TermgameSellerV2('YOUR_API_KEY');
```

### วิธีที่ 2: ใช้ Composer (ถ้ามี)

```bash
composer require termgameseller/sdk-v2
```

## ความต้องการของระบบ

-   PHP >= 7.4
-   cURL Extension
-   JSON Extension

## การเริ่มต้นใช้งาน

### 1. สร้าง Instance

```php
<?php
require_once 'class.tgseller.php';

// กำหนด API Key
$apiKey = 'YOUR_API_KEY_HERE';

// สร้าง instance
$api = new TermgameSellerV2($apiKey);

// สร้าง instance พร้อมกำหนด timeout (default = 30 seconds)
$api = new TermgameSellerV2($apiKey, 60);
```

## API Documentation

### 1. ตรวจสอบยอดเงิน (Balance)

#### `getBalance(): array`

ดึงข้อมูลยอดเงินในบัญชี

```php
$balance = $api->getBalance();
// Returns: ["balance" => "4630.15"]
```

#### `getBalanceAmount(): float`

ดึงยอดเงินเป็นตัวเลข

```php
$amount = $api->getBalanceAmount();
// Returns: 4630.15
```

#### `hasEnoughBalance(float $amount): bool`

ตรวจสอบว่ายอดเงินเพียงพอหรือไม่

```php
if ($api->hasEnoughBalance(100)) {
    echo "ยอดเงินเพียงพอ";
}
```

---

### 2. จัดการสินค้า (Products)

#### `getProducts(): array`

ดึงรายการสินค้าทั้งหมดพร้อม packages และ servers

```php
$products = $api->getProducts();

foreach ($products as $product) {
    echo $product['name']; // Freefire, ROV, etc.
    echo $product['id'];
    echo $product['playerFieldName']; // UID, Open ID, etc.

    // แสดง packages
    foreach ($product['packages'] as $package) {
        echo $package['name'];
        echo $package['price'];
    }
}
```

#### `getActiveProducts(): array`

ดึงเฉพาะสินค้าที่เปิดใช้งาน

```php
$activeProducts = $api->getActiveProducts();
```

#### `findProductByName(string $name): ?array`

ค้นหาสินค้าด้วยชื่อ

```php
$product = $api->findProductByName('Freefire');

if ($product) {
    echo $product['id'];
    echo $product['name'];
}
```

#### `findProductById(string $productId): ?array`

ค้นหาสินค้าด้วย ID

```php
$product = $api->findProductById('46e3eaad-7331-429e-a592-5964b79126e3');
```

---

### 3. จัดการ Packages

#### `getActivePackages(array $product): array`

ดึง packages ที่เปิดใช้งานจากสินค้า

```php
$product = $api->findProductByName('Freefire');
$packages = $api->getActivePackages($product);

foreach ($packages as $package) {
    echo $package['name'];
    echo $package['price'];
}
```

#### `findPackageByName(array $product, string $packageName): ?array`

ค้นหา package ด้วยชื่อ

```php
$product = $api->findProductByName('Freefire');
$package = $api->findPackageByName($product, '68 เพชร');

if ($package) {
    echo $package['id'];
    echo $package['price'];
}
```

#### `findPackageById(array $product, string $packageId): ?array`

ค้นหา package ด้วย ID

```php
$product = $api->findProductByName('Freefire');
$package = $api->findPackageById($product, 'package-id');
```

---

### 4. ประวัติการสั่งซื้อ (Orders)

#### `getOrders(): array`

ดึงประวัติการสั่งซื้อทั้งหมด

```php
$orders = $api->getOrders();

foreach ($orders as $order) {
    echo $order['orderId'];
    echo $order['status']; // PROCESSING, SUCCESS, FAILED
    echo $order['paid'];
    echo $order['package']['name'];
    echo $order['package']['product']['name'];
}
```

#### `getOrderStatus(string $orderId): array`

ตรวจสอบสถานะของออเดอร์

```php
$order = $api->getOrderStatus('order-id-here');

echo $order['status'];
echo $order['beforeBalance'];
echo $order['afterBalance'];

if ($order['status'] === 'FAILED') {
    echo $order['failReason'];
}
```

---

### 5. ซื้อแพ็คเกจ (Purchase)

#### `buyPackage(string $packageId, array $data = []): array`

ซื้อแพ็คเกจพร้อมกำหนดข้อมูลเอง

```php
$data = [
    'playerId' => '123456789',
    'zoneId' => '1001',
    'callbackUrl' => 'https://yoursite.com/callback'
];

$result = $api->buyPackage('package-id', $data);
```

#### `buyWithPlayerId(string $packageId, string $playerId, ?string $callbackUrl = null): array`

ซื้อด้วย Player ID

```php
$result = $api->buyWithPlayerId('package-id', '123456789');

// พร้อม callback
$result = $api->buyWithPlayerId(
    'package-id',
    '123456789',
    'https://yoursite.com/callback'
);
```

#### `buyWithUrl(string $packageId, string $url, ?string $callbackUrl = null): array`

ซื้อด้วย URL

```php
$result = $api->buyWithUrl('package-id', 'https://example.com/pay');
```

#### `buyWithCredentials(string $packageId, string $username, string $password, ?string $callbackUrl = null): array`

ซื้อด้วย Username และ Password

```php
$result = $api->buyWithCredentials(
    'package-id',
    'username',
    'password'
);
```

---

### 6. Helper Functions

#### `formatPrice($price, string $currency = '฿'): string`

จัดรูปแบบราคา

```php
echo $api->formatPrice(100); // "100.00 ฿"
echo $api->formatPrice(100, 'THB'); // "100.00 THB"
```

#### `getLastResponse(): ?array`

ดึงข้อมูล HTTP response ล่าสุด

```php
$response = $api->getLastResponse();

echo $response['status_code']; // 200
echo json_encode($response['data']);
echo $response['raw'];
```

## ตัวอย่างการใช้งาน

### ตัวอย่างที่ 1: ระบบเติมเกมอัตโนมัติ

```php
<?php
require_once 'class.tgseller.php';

// รับข้อมูลจากฟอร์ม
$gameName = $_POST['game']; // "Freefire"
$packageName = $_POST['package']; // "68 เพชร"
$playerId = $_POST['player_id']; // "123456789"

try {
    $api = new TermgameSellerV2('YOUR_API_KEY');

    // ค้นหาเกมและแพ็คเกจ
    $product = $api->findProductByName($gameName);
    if (!$product) {
        throw new Exception('ไม่พบเกมที่เลือก');
    }

    $package = $api->findPackageByName($product, $packageName);
    if (!$package) {
        throw new Exception('ไม่พบแพ็คเกจที่เลือก');
    }

    // ตรวจสอบยอดเงิน
    $price = (float)$package['price'];
    if (!$api->hasEnoughBalance($price)) {
        throw new Exception('ยอดเงินไม่เพียงพอ');
    }

    // ทำการซื้อ
    $result = $api->buyWithPlayerId($package['id'], $playerId);

    // แสดงผลลัพธ์
    echo json_encode([
        'success' => true,
        'message' => 'เติมเกมสำเร็จ',
        'data' => $result
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
```

### ตัวอย่างที่ 2: แสดงรายการสินค้าในเว็บไซต์

```php
<?php
require_once 'class.tgseller.php';

$api = new TermgameSellerV2('YOUR_API_KEY');
$products = $api->getActiveProducts();
?>

<!DOCTYPE html>
<html>
<head>
    <title>รายการเกม</title>
</head>
<body>
    <h1>เลือกเกมที่ต้องการเติม</h1>

    <?php foreach ($products as $product): ?>
        <div class="product-card">
            <h2><?= htmlspecialchars($product['name']) ?></h2>
            <p>กรอก: <?= htmlspecialchars($product['playerFieldName']) ?></p>

            <h3>แพ็คเกจ</h3>
            <ul>
                <?php
                $packages = $api->getActivePackages($product);
                foreach ($packages as $package):
                ?>
                    <li>
                        <?= htmlspecialchars($package['name']) ?> -
                        <?= $api->formatPrice($package['price']) ?>
                        <button onclick="buy('<?= $package['id'] ?>')">
                            ซื้อเลย
                        </button>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endforeach; ?>
</body>
</html>
```

### ตัวอย่างที่ 3: ตรวจสอบสถานะออเดอร์

```php
<?php
require_once 'class.tgseller.php';

$orderId = $_GET['order_id'];

try {
    $api = new TermgameSellerV2('YOUR_API_KEY');
    $order = $api->getOrderStatus($orderId);

    echo "สถานะ: " . $order['status'] . "<br>";
    echo "ราคา: " . $api->formatPrice($order['paid']) . "<br>";
    echo "แพ็คเกจ: " . $order['package']['name'] . "<br>";
    echo "เกม: " . $order['package']['product']['name'] . "<br>";

    if ($order['status'] === 'SUCCESS') {
        echo "<p style='color: green;'>เติมสำเร็จแล้ว!</p>";
    } elseif ($order['status'] === 'FAILED') {
        echo "<p style='color: red;'>เติมไม่สำเร็จ: " .
             $order['failReason'] . "</p>";
    } else {
        echo "<p style='color: orange;'>กำลังดำเนินการ...</p>";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

### ตัวอย่างที่ 4: Webhook Callback

```php
<?php
require_once 'class.tgseller.php';

// รับข้อมูลจาก webhook
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// บันทึก log
file_put_contents('webhook.log', date('Y-m-d H:i:s') . "\n" . $json . "\n\n", FILE_APPEND);

// ตรวจสอบสถานะ
if ($data['status'] === 'SUCCESS') {
    // อัพเดทฐานข้อมูล
    // ส่งอีเมลแจ้งเตือน
    // ฯลฯ

    echo json_encode(['received' => true]);
} else {
    // จัดการกรณีที่ไม่สำเร็จ
    echo json_encode(['received' => true]);
}
```

## การจัดการ Errors

SDK จะ throw `Exception` เมื่อเกิดข้อผิดพลาด

```php
try {
    $api = new TermgameSellerV2('INVALID_KEY');
    $balance = $api->getBalance();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    echo "Code: " . $e->getCode();

    // ดูข้อมูล response เพิ่มเติม
    $lastResponse = $api->getLastResponse();
    if ($lastResponse) {
        echo "HTTP Code: " . $lastResponse['status_code'];
    }
}
```

### ประเภทของ Errors

-   **API Key ไม่ถูกต้อง**: HTTP 401 Unauthorized
-   **ยอดเงินไม่เพียงพอ**: ตรวจสอบด้วย `hasEnoughBalance()`
-   **Package ไม่พบ**: ตรวจสอบด้วย `findPackageById()` ก่อนซื้อ
-   **Connection Error**: ตรวจสอบ network และ timeout
-   **JSON Parse Error**: Response ไม่ใช่ JSON

## API Endpoints

SDK นี้รองรับ endpoints ต่อไปนี้:

| Method | Endpoint                         | Function           |
| ------ | -------------------------------- | ------------------ |
| GET    | `/v1/api/balance`                | `getBalance()`     |
| GET    | `/v1/api/products`               | `getProducts()`    |
| GET    | `/v1/api/orders`                 | `getOrders()`      |
| GET    | `/v1/api/order-status/{orderId}` | `getOrderStatus()` |
| POST   | `/v1/api/buy/{packageId}`        | `buyPackage()`     |

## ข้อมูล Request/Response

### Buy Package Request

```json
{
    "playerId": "123456789",
    "url": "https://example.com",
    "zoneId": "1001",
    "playerName": "PlayerName",
    "serverName": "Asia",
    "username": "username",
    "password": "password",
    "serverId": "server-id",
    "callbackUrl": "https://yoursite.com/callback"
}
```

**หมายเหตุ**: ส่งเฉพาะฟิลด์ที่จำเป็นตาม product configuration

### Buy Package Response (Success)

```json
{
    "status": "success",
    "message": "ซื้อแพ็คเกจสำเร็จ"
}
```

### Order Status Response

```json
{
    "id": "order-uuid",
    "orderId": 80,
    "status": "SUCCESS",
    "paid": "20.00",
    "beforeBalance": "100.00",
    "afterBalance": "80.00",
    "package": {
        "name": "68 เพชร",
        "product": {
            "name": "Freefire"
        }
    }
}
```

## Tips & Best Practices

### 1. Cache Product List

```php
// Cache products เพื่อลด API calls
$cacheFile = 'products_cache.json';
$cacheTime = 3600; // 1 hour

if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
    $products = json_decode(file_get_contents($cacheFile), true);
} else {
    $api = new TermgameSellerV2($apiKey);
    $products = $api->getProducts();
    file_put_contents($cacheFile, json_encode($products));
}
```

### 2. ตรวจสอบยอดเงินก่อนแสดงหน้าชำระเงิน

```php
$package = $api->findPackageById($product, $packageId);
$hasEnough = $api->hasEnoughBalance((float)$package['price']);

if (!$hasEnough) {
    // แจ้งเตือนให้เติมเงินเข้าระบบ
    redirect('topup.php');
}
```

### 3. ใช้ Try-Catch ทุกครั้งที่เรียก API

```php
try {
    $result = $api->buyWithPlayerId($packageId, $playerId);
    // Handle success
} catch (Exception $e) {
    // Log error
    error_log("Buy failed: " . $e->getMessage());
    // Show user-friendly message
    echo "เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง";
}
```

### 4. ใช้ Callback URL สำหรับการอัพเดทสถานะ

```php
$callbackUrl = 'https://yoursite.com/webhook/termgame';
$result = $api->buyWithPlayerId($packageId, $playerId, $callbackUrl);

// บันทึก order ID สำหรับจับคู่ใน webhook
saveOrderId($result['orderId']);
```

## Webhook Handler

### การตั้งค่า Webhook

Termgame Seller V2 จะส่ง callback แจ้งสถานะการทำรายการมาที่ URL ที่คุณกำหนด

**Request Format:**
```
Method: POST
Headers:
  - Content-Type: application/json
  - User-Agent: TermgameSeller
  - x-hash: MD5 hash ของ API Key
Body:
  {
    "id": "transaction-id"
  }
```

### ตัวอย่าง Webhook Handler

```php
<?php
// webhook.php
define('API_KEY', 'YOUR_API_KEY_HERE');

// ตรวจสอบ User-Agent
if ($_SERVER['HTTP_USER_AGENT'] !== 'TermgameSeller') {
    http_response_code(403);
    exit('Invalid User-Agent');
}

// ตรวจสอบ x-hash
$receivedHash = $_SERVER['HTTP_X_HASH'] ?? '';
$expectedHash = md5(API_KEY);

if ($receivedHash !== $expectedHash) {
    http_response_code(403);
    exit('Invalid signature');
}

// อ่าน transaction ID
$data = json_decode(file_get_contents('php://input'), true);
$transactionId = $data['id'];

// ตรวจสอบสถานะจาก API
require_once 'class.tgseller.php';
$api = new TermgameSellerV2(API_KEY);
$orderStatus = $api->getOrderStatus($transactionId);

// อัพเดทฐานข้อมูล
// updateOrderInDatabase($transactionId, $orderStatus);

// ส่ง response กลับ
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'transaction_id' => $transactionId
]);
```

### การใช้ Callback URL

```php
// กำหนด callback URL ตอนซื้อ
$result = $api->buyWithPlayerId(
    $packageId,
    $playerId,
    'https://yoursite.com/webhook.php'
);
```

### Security Best Practices

1. **ตรวจสอบ x-hash Header**
   ```php
   $expectedHash = md5(API_KEY);
   if ($_SERVER['HTTP_X_HASH'] !== $expectedHash) {
       die('Invalid signature');
   }
   ```

2. **ตรวจสอบ User-Agent**
   ```php
   if ($_SERVER['HTTP_USER_AGENT'] !== 'TermgameSeller') {
       die('Invalid User-Agent');
   }
   ```

3. **บันทึก Log**
   ```php
   file_put_contents('webhook.log', 
       date('Y-m-d H:i:s') . " - " . 
       json_encode($data) . "\n", 
       FILE_APPEND
   );
   ```

4. **ใช้ HTTPS**
   - ใช้ SSL/TLS สำหรับ webhook URL
   - ตรวจสอบ IP address ของผู้ส่ง (ถ้าเป็นไปได้)

### Webhook Use Cases

**ส่งอีเมลแจ้งเตือน:**
```php
if ($orderStatus['status'] === 'SUCCESS') {
    mail(
        $userEmail,
        'การเติมเกมสำเร็จ',
        "เติม {$orderStatus['package']['name']} สำเร็จแล้ว"
    );
}
```

**อัพเดทฐานข้อมูล:**
```php
$pdo->prepare("
    UPDATE orders 
    SET status = ?, updated_at = NOW() 
    WHERE transaction_id = ?
")->execute([$orderStatus['status'], $transactionId]);
```

**ส่ง LINE Notify:**
```php
$ch = curl_init('https://notify-api.line.me/api/notify');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'message' => "เติมเกมสำเร็จ: {$orderStatus['package']['name']}"
]);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer YOUR_LINE_TOKEN'
]);
curl_exec($ch);
```

## การพัฒนาและทดสอบ

### การทดสอบโดยไม่ซื้อจริง

```php
// Comment โค้ดซื้อออก
// $result = $api->buyPackage($packageId, $data);

// ใช้ Mock Data แทน
$result = [
    'status' => 'success',
    'message' => 'ซื้อแพ็คเกจสำเร็จ (TEST MODE)'
];
```

### Debug Mode

```php
// เปิด error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ดู response ทั้งหมด
$api->getBalance();
$response = $api->getLastResponse();
var_dump($response);
```

## License

MIT License - สามารถนำไปใช้งานได้อย่างอิสระ

## Support

-   **Email**: support@termgameseller.com
-   **Website**: https://termgameseller.com
-   **API Documentation**: https://api-v2.termgameseller.com/docs

## Changelog

### Version 2.0.0

-   รองรับ Termgame Seller V2 API
-   เพิ่ม Helper Functions
-   ปรับปรุง Error Handling
-   เพิ่มตัวอย่างการใช้งาน

---

สร้างโดย Termgame Seller Team | Last Updated: 2025
# termgameseller-php-sdk
