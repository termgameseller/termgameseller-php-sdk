<?php

/**
 * ตัวอย่างการใช้งาน Webhook Handler
 * 
 * ไฟล์นี้แสดงตัวอย่างการ customize webhook handler
 * สำหรับ use case ต่างๆ
 */

// ========================================
// ตัวอย่างที่ 1: Webhook พื้นฐาน
// ========================================

/**
 * ตัวอย่างการอัพเดทฐานข้อมูล
 */
function updateOrderInDatabase(string $transactionId, array $orderStatus): void
{
    // ตัวอย่างการใช้ PDO
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=your_database', 'username', 'password');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("
            UPDATE orders 
            SET 
                status = :status,
                paid_amount = :paid,
                before_balance = :before_balance,
                after_balance = :after_balance,
                fail_reason = :fail_reason,
                updated_at = NOW()
            WHERE transaction_id = :transaction_id
        ");

        $stmt->execute([
            'status' => $orderStatus['status'],
            'paid' => $orderStatus['paid'] ?? 0,
            'before_balance' => $orderStatus['beforeBalance'] ?? 0,
            'after_balance' => $orderStatus['afterBalance'] ?? 0,
            'fail_reason' => $orderStatus['failReason'] ?? null,
            'transaction_id' => $transactionId
        ]);

        // Log success
        error_log("Order updated: $transactionId - Status: {$orderStatus['status']}");

    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        throw $e;
    }
}

// ========================================
// ตัวอย่างที่ 2: ส่งอีเมลแจ้งเตือน
// ========================================

/**
 * ส่งอีเมลแจ้งเตือนเมื่อการเติมเงินสำเร็จ
 */
function sendEmailNotification(array $orderStatus): void
{
    // ดึงข้อมูลผู้ใช้จากฐานข้อมูล
    $userEmail = getUserEmail($orderStatus['memberId']);
    
    if (!$userEmail) {
        return;
    }

    $subject = 'การเติมเกมสำเร็จ - ' . $orderStatus['package']['product']['name'];
    
    $message = "
        <html>
        <head>
            <title>การเติมเกมสำเร็จ</title>
        </head>
        <body>
            <h2>การเติมเกมของคุณสำเร็จแล้ว</h2>
            <p><strong>เกม:</strong> {$orderStatus['package']['product']['name']}</p>
            <p><strong>แพ็คเกจ:</strong> {$orderStatus['package']['name']}</p>
            <p><strong>จำนวนเงิน:</strong> {$orderStatus['paid']} บาท</p>
            <p><strong>รหัสออเดอร์:</strong> {$orderStatus['orderId']}</p>
            <p><strong>สถานะ:</strong> {$orderStatus['status']}</p>
            
            <p>ขอบคุณที่ใช้บริการ</p>
        </body>
        </html>
    ";

    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=utf-8',
        'From: noreply@yoursite.com',
    ];

    mail($userEmail, $subject, $message, implode("\r\n", $headers));
}

function getUserEmail(string $memberId): ?string
{
    // TODO: ดึง email จากฐานข้อมูล
    return 'user@example.com';
}

// ========================================
// ตัวอย่างที่ 3: ส่ง LINE Notify
// ========================================

/**
 * ส่งการแจ้งเตือนผ่าน LINE Notify
 */
function sendLineNotification(array $orderStatus): void
{
    $lineToken = 'YOUR_LINE_NOTIFY_TOKEN';
    
    $message = "\n🎮 การเติมเกมสำเร็จ\n";
    $message .= "━━━━━━━━━━━━━━━\n";
    $message .= "เกม: {$orderStatus['package']['product']['name']}\n";
    $message .= "แพ็คเกจ: {$orderStatus['package']['name']}\n";
    $message .= "จำนวนเงิน: {$orderStatus['paid']} บาท\n";
    $message .= "รหัสออเดอร์: {$orderStatus['orderId']}\n";
    $message .= "สถานะ: {$orderStatus['status']}";

    $ch = curl_init('https://notify-api.line.me/api/notify');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['message' => $message]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $lineToken
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    curl_exec($ch);
    curl_close($ch);
}

// ========================================
// ตัวอย่างที่ 4: บันทึก Log แบบละเอียด
// ========================================

/**
 * บันทึก log แบบมี structure
 */
function logDetailedWebhook(string $transactionId, array $orderStatus, string $action): void
{
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $date = date('Y-m-d');
    $logFile = "$logDir/webhook-$date.log";

    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'action' => $action,
        'transaction_id' => $transactionId,
        'order_id' => $orderStatus['orderId'] ?? null,
        'status' => $orderStatus['status'] ?? 'unknown',
        'game' => $orderStatus['package']['product']['name'] ?? 'unknown',
        'package' => $orderStatus['package']['name'] ?? 'unknown',
        'amount' => $orderStatus['paid'] ?? 0,
        'player_id' => $orderStatus['playerId'] ?? null,
        'fail_reason' => $orderStatus['failReason'] ?? null,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];

    file_put_contents(
        $logFile,
        json_encode($logEntry, JSON_UNESCAPED_UNICODE) . "\n",
        FILE_APPEND
    );
}

// ========================================
// ตัวอย่างที่ 5: Webhook แบบ Queue System
// ========================================

/**
 * เพิ่ม webhook ลงใน queue เพื่อประมวลผลทีหลัง
 */
function addToQueue(string $transactionId, array $data): void
{
    // ตัวอย่างการใช้ Redis
    try {
        $redis = new Redis();
        $redis->connect('127.0.0.1', 6379);
        
        $queueData = [
            'transaction_id' => $transactionId,
            'data' => $data,
            'received_at' => time()
        ];
        
        $redis->rPush('webhook_queue', json_encode($queueData));
        $redis->close();
        
    } catch (Exception $e) {
        // Fallback: บันทึกลง file
        $queueFile = __DIR__ . '/webhook_queue.json';
        $queue = file_exists($queueFile) ? json_decode(file_get_contents($queueFile), true) : [];
        $queue[] = [
            'transaction_id' => $transactionId,
            'data' => $data,
            'received_at' => time()
        ];
        file_put_contents($queueFile, json_encode($queue, JSON_PRETTY_PRINT));
    }
}

// ========================================
// ตัวอย่างที่ 6: Retry Mechanism
// ========================================

/**
 * ประมวลผล transaction พร้อม retry logic
 */
function processTransactionWithRetry(string $transactionId, int $maxRetries = 3): array
{
    $retryCount = 0;
    $lastError = null;

    while ($retryCount < $maxRetries) {
        try {
            require_once 'class.tgseller.php';
            
            $api = new TermgameSellerV2(API_KEY);
            $orderStatus = $api->getOrderStatus($transactionId);
            
            // สำเร็จ - return ทันที
            return $orderStatus;
            
        } catch (Exception $e) {
            $lastError = $e->getMessage();
            $retryCount++;
            
            // รอก่อน retry (exponential backoff)
            if ($retryCount < $maxRetries) {
                sleep(pow(2, $retryCount)); // 2s, 4s, 8s
            }
        }
    }

    // Retry หมดแล้วยังไม่สำเร็จ
    throw new Exception("Failed after $maxRetries retries. Last error: $lastError");
}

// ========================================
// ตัวอย่างที่ 7: Webhook Security
// ========================================

/**
 * ตรวจสอบ IP Address ของ webhook sender
 */
function verifyWebhookIP(): bool
{
    $allowedIPs = [
        '127.0.0.1',
        '::1',
        // เพิ่ม IP ของ Termgame Seller server
        // 'xxx.xxx.xxx.xxx'
    ];

    $clientIP = $_SERVER['REMOTE_ADDR'] ?? '';
    
    return in_array($clientIP, $allowedIPs);
}

/**
 * Rate limiting สำหรับ webhook
 */
function checkRateLimit(string $identifier, int $maxRequests = 10, int $timeWindow = 60): bool
{
    $cacheFile = __DIR__ . '/webhook_rate_limit.json';
    $now = time();
    
    // โหลด rate limit data
    $data = file_exists($cacheFile) ? json_decode(file_get_contents($cacheFile), true) : [];
    
    // ลบข้อมูลที่หมดอายุ
    $data = array_filter($data, function($entry) use ($now, $timeWindow) {
        return ($now - $entry['time']) < $timeWindow;
    });
    
    // นับจำนวน request ของ identifier นี้
    $requests = array_filter($data, function($entry) use ($identifier) {
        return $entry['id'] === $identifier;
    });
    
    if (count($requests) >= $maxRequests) {
        return false; // เกิน rate limit
    }
    
    // บันทึก request ใหม่
    $data[] = [
        'id' => $identifier,
        'time' => $now
    ];
    
    file_put_contents($cacheFile, json_encode(array_values($data)));
    
    return true;
}

// ========================================
// ตัวอย่างที่ 8: Webhook Testing
// ========================================

/**
 * ฟังก์ชันสำหรับทดสอบ webhook (ใช้ในโหมด development)
 */
function testWebhook(): void
{
    if (php_sapi_name() !== 'cli') {
        die('This function can only be run from command line');
    }

    echo "Testing Webhook Handler...\n\n";

    $testData = [
        'id' => 'test-transaction-id-' . time()
    ];

    // สร้าง test request
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['HTTP_USER_AGENT'] = 'TermgameSeller';
    $_SERVER['HTTP_X_HASH'] = md5(API_KEY);
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

    // จำลอง POST body
    $GLOBALS['test_webhook_body'] = json_encode($testData);

    echo "Sending test webhook...\n";
    echo "Transaction ID: {$testData['id']}\n";
    echo "Hash: " . $_SERVER['HTTP_X_HASH'] . "\n\n";

    // Include webhook handler
    require_once 'webhook.php';
}

// เรียกใช้ test ถ้า run จาก command line
if (php_sapi_name() === 'cli' && isset($argv[1]) && $argv[1] === 'test') {
    testWebhook();
}
