<?php

/**
 * Termgame Seller V2 Webhook Handler
 * 
 * ไฟล์สำหรับรับ webhook callback จาก Termgame Seller V2
 * 
 * Request Format:
 * - Method: POST
 * - Headers:
 *   - Content-Type: application/json
 *   - User-Agent: TermgameSeller
 *   - x-hash: MD5 hash ของ API Key
 * - Body: { "id": "transaction-id" }
 */

// กำหนด API Key ของคุณ (ควรเก็บใน config หรือ .env)
define('API_KEY', 'YOUR_API_KEY_HERE');

// กำหนด Log File Path
define('WEBHOOK_LOG_FILE', __DIR__ . '/webhook.log');

// กำหนดว่าจะเปิด logging หรือไม่
define('ENABLE_LOGGING', true);

/**
 * Log webhook activity
 * 
 * @param string $message ข้อความที่ต้องการบันทึก
 * @param array $data ข้อมูลเพิ่มเติม
 */
function logWebhook(string $message, array $data = []): void
{
    if (!ENABLE_LOGGING) {
        return;
    }

    $timestamp = date('Y-m-d H:i:s');
    $logEntry = sprintf(
        "[%s] %s\n%s\n%s\n\n",
        $timestamp,
        $message,
        !empty($data) ? json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '',
        str_repeat('-', 80)
    );

    file_put_contents(WEBHOOK_LOG_FILE, $logEntry, FILE_APPEND);
}

/**
 * สร้าง MD5 hash จาก API Key
 * 
 * @param string $apiKey API Key
 * @return string MD5 hash
 */
function generateHash(string $apiKey): string
{
    return md5($apiKey);
}

/**
 * ส่ง JSON response
 * 
 * @param array $data ข้อมูลที่ต้องการส่ง
 * @param int $statusCode HTTP status code
 */
function sendResponse(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * ส่ง error response
 * 
 * @param string $message ข้อความ error
 * @param int $statusCode HTTP status code
 */
function sendError(string $message, int $statusCode = 400): void
{
    logWebhook("Error: $message", ['status_code' => $statusCode]);
    sendResponse([
        'success' => false,
        'error' => $message
    ], $statusCode);
}

// ========================================
// Main Webhook Handler
// ========================================

try {
    // 1. ตรวจสอบ HTTP Method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendError('Method not allowed. Only POST is accepted.', 405);
    }

    // 2. ตรวจสอบ User-Agent
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if ($userAgent !== 'TermgameSeller') {
        logWebhook('Invalid User-Agent', ['user_agent' => $userAgent]);
        sendError('Invalid User-Agent', 403);
    }

    // 3. ตรวจสอบ x-hash header
    $receivedHash = $_SERVER['HTTP_X_HASH'] ?? '';
    $expectedHash = generateHash(API_KEY);

    if (empty($receivedHash)) {
        sendError('Missing x-hash header', 401);
    }

    if ($receivedHash !== $expectedHash) {
        logWebhook('Invalid x-hash', [
            'received' => $receivedHash,
            'expected' => $expectedHash
        ]);
        sendError('Invalid x-hash signature', 403);
    }

    // 4. อ่าน JSON Body
    $rawBody = file_get_contents('php://input');
    $data = json_decode($rawBody, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        sendError('Invalid JSON body: ' . json_last_error_msg(), 400);
    }

    // 5. ตรวจสอบว่ามี transaction ID
    if (empty($data['id'])) {
        sendError('Missing transaction ID', 400);
    }

    $transactionId = $data['id'];

    // Log webhook received
    logWebhook('Webhook received', [
        'transaction_id' => $transactionId,
        'raw_body' => $rawBody,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ]);

    // ========================================
    // ประมวลผล Transaction
    // ========================================

    // TODO: ใส่โค้ดประมวลผลของคุณที่นี่
    
    // ตัวอย่าง: ตรวจสอบสถานะจาก API
    require_once 'class.tgseller.php';
    
    $api = new TermgameSellerV2(API_KEY);
    $orderStatus = $api->getOrderStatus($transactionId);
    
    // อัพเดทฐานข้อมูลของคุณ
    // updateOrderInDatabase($transactionId, $orderStatus);
    
    // ส่งอีเมลแจ้งเตือน (ถ้ามี)
    // if ($orderStatus['status'] === 'SUCCESS') {
    //     sendEmailNotification($orderStatus);
    // }
    
    // บันทึก log
    logWebhook('Transaction processed', [
        'transaction_id' => $transactionId,
        'status' => $orderStatus['status'] ?? 'unknown',
        'order_details' => $orderStatus
    ]);

    // ========================================
    // ส่ง Response กลับ
    // ========================================

    sendResponse([
        'success' => true,
        'message' => 'Webhook received and processed',
        'transaction_id' => $transactionId,
        'processed_at' => date('Y-m-d H:i:s')
    ], 200);

} catch (Exception $e) {
    // จัดการ error ที่เกิดขึ้น
    logWebhook('Exception occurred', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);

    sendError('Internal server error: ' . $e->getMessage(), 500);
}
