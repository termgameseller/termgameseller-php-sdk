<?php

require_once 'class.tgseller.php';

// ตั้งค่า API Key
$apiKey = 'YOUR_API_KEY_HERE';

try {
    // สร้าง instance
    $api = new TermgameSellerV2($apiKey);
    
    echo "=== Termgame Seller V2 API Examples ===\n\n";

    // ========================================
    // 1. ตรวจสอบยอดเงิน (Get Balance)
    // ========================================
    echo "1. ตรวจสอบยอดเงิน\n";
    echo "-------------------\n";
    
    $balance = $api->getBalance();
    echo "Balance: " . json_encode($balance, JSON_PRETTY_PRINT) . "\n";
    
    // ดึงยอดเงินเป็น float
    $balanceAmount = $api->getBalanceAmount();
    echo "Balance Amount: " . $api->formatPrice($balanceAmount) . "\n";
    
    // ตรวจสอบว่ายอดเงินพอหรือไม่
    $hasEnough = $api->hasEnoughBalance(100);
    echo "Has 100 THB? " . ($hasEnough ? 'Yes' : 'No') . "\n\n";

    // ========================================
    // 2. ดึงรายการสินค้าทั้งหมด (Get Products)
    // ========================================
    echo "2. ดึงรายการสินค้าทั้งหมด\n";
    echo "-------------------------\n";
    
    $products = $api->getProducts();
    echo "Total Products: " . count($products) . "\n";
    
    foreach ($products as $product) {
        echo "\nProduct: {$product['name']}\n";
        echo "  ID: {$product['id']}\n";
        echo "  Active: " . ($product['isActive'] ? 'Yes' : 'No') . "\n";
        echo "  Player Field: {$product['playerFieldName']}\n";
        
        if (!empty($product['packages'])) {
            echo "  Packages: " . count($product['packages']) . "\n";
            foreach ($product['packages'] as $package) {
                echo "    - {$package['name']} ({$api->formatPrice($package['price'])})\n";
            }
        }
        
        if (!empty($product['servers'])) {
            echo "  Servers: " . count($product['servers']) . "\n";
        }
    }
    echo "\n";

    // ========================================
    // 3. ค้นหาสินค้า (Find Product)
    // ========================================
    echo "3. ค้นหาสินค้า\n";
    echo "--------------\n";
    
    // ค้นหาด้วยชื่อ
    $product = $api->findProductByName('Freefire');
    if ($product) {
        echo "Found Product: {$product['name']}\n";
        echo "ID: {$product['id']}\n";
        echo "Player Field: {$product['playerFieldName']}\n";
    } else {
        echo "Product not found\n";
    }
    echo "\n";

    // ========================================
    // 4. ดึงเฉพาะสินค้าที่เปิดใช้งาน (Active Products)
    // ========================================
    echo "4. ดึงเฉพาะสินค้าที่เปิดใช้งาน\n";
    echo "------------------------------\n";
    
    $activeProducts = $api->getActiveProducts();
    echo "Active Products: " . count($activeProducts) . "\n";
    foreach ($activeProducts as $product) {
        echo "  - {$product['name']}\n";
    }
    echo "\n";

    // ========================================
    // 5. จัดการ Packages
    // ========================================
    echo "5. จัดการ Packages\n";
    echo "------------------\n";
    
    $product = $api->findProductByName('Freefire');
    if ($product) {
        // ดึง active packages
        $activePackages = $api->getActivePackages($product);
        echo "Active Packages: " . count($activePackages) . "\n";
        
        // ค้นหา package
        $package = $api->findPackageByName($product, '68');
        if ($package) {
            echo "Found Package: {$package['name']}\n";
            echo "Price: {$api->formatPrice($package['price'])}\n";
            echo "ID: {$package['id']}\n";
        }
    }
    echo "\n";

    // ========================================
    // 6. ดูประวัติการสั่งซื้อ (Get Orders)
    // ========================================
    echo "6. ดูประวัติการสั่งซื้อ\n";
    echo "----------------------\n";
    
    $orders = $api->getOrders();
    echo "Total Orders: " . count($orders) . "\n";
    
    foreach ($orders as $order) {
        echo "\nOrder ID: {$order['orderId']}\n";
        echo "  Package: {$order['package']['name']}\n";
        echo "  Product: {$order['package']['product']['name']}\n";
        echo "  Amount: {$api->formatPrice($order['paid'])}\n";
        echo "  Status: {$order['status']}\n";
        echo "  Created: {$order['createdAt']}\n";
        
        if (isset($order['playerId'])) {
            echo "  Player ID: {$order['playerId']}\n";
        }
    }
    echo "\n";

    // ========================================
    // 7. ตรวจสอบสถานะออเดอร์ (Get Order Status)
    // ========================================
    echo "7. ตรวจสอบสถานะออเดอร์\n";
    echo "----------------------\n";
    
    // ใช้ order ID จากตัวอย่าง
    if (!empty($orders) && isset($orders[0]['id'])) {
        $orderId = $orders[0]['id'];
        $orderStatus = $api->getOrderStatus($orderId);
        
        echo "Order ID: {$orderStatus['orderId']}\n";
        echo "Status: {$orderStatus['status']}\n";
        echo "Before Balance: {$api->formatPrice($orderStatus['beforeBalance'])}\n";
        echo "After Balance: {$api->formatPrice($orderStatus['afterBalance'])}\n";
        echo "Paid: {$api->formatPrice($orderStatus['paid'])}\n";
        
        if ($orderStatus['status'] === 'FAILED' && isset($orderStatus['failReason'])) {
            echo "Fail Reason: {$orderStatus['failReason']}\n";
        }
    }
    echo "\n";

    // ========================================
    // 8. ซื้อแพ็คเกจ (Buy Package)
    // ========================================
    echo "8. ซื้อแพ็คเกจ (ตัวอย่าง - ไม่ได้ execute จริง)\n";
    echo "-----------------------------------------------\n";
    
    // *** คำเตือน: Comment โค้ดด้านล่างออกเพื่อป้องกันการซื้อจริง ***
    
    // ตัวอย่างการซื้อด้วย Player ID
    echo "// Example 1: Buy with Player ID\n";
    echo "// \$result = \$api->buyWithPlayerId('package-id', '123456789');\n";
    
    // ตัวอย่างการซื้อด้วย URL
    echo "// Example 2: Buy with URL\n";
    echo "// \$result = \$api->buyWithUrl('package-id', 'https://example.com');\n";
    
    // ตัวอย่างการซื้อด้วย Username/Password
    echo "// Example 3: Buy with Credentials\n";
    echo "// \$result = \$api->buyWithCredentials('package-id', 'username', 'password');\n";
    
    // ตัวอย่างการซื้อแบบกำหนดเอง
    echo "// Example 4: Buy with custom data\n";
    echo "// \$data = [\n";
    echo "//     'playerId' => '123456789',\n";
    echo "//     'zoneId' => '1001',\n";
    echo "//     'callbackUrl' => 'https://yoursite.com/callback'\n";
    echo "// ];\n";
    echo "// \$result = \$api->buyPackage('package-id', \$data);\n";
    
    echo "\n";
    
    // ========================================
    // ตัวอย่างการซื้อจริง (ถ้าต้องการทดสอบ)
    // ========================================
    /*
    echo "9. ทดสอบการซื้อจริง\n";
    echo "-------------------\n";
    
    // ค้นหาสินค้าและแพ็คเกจ
    $product = $api->findProductByName('Freefire');
    $package = $api->findPackageByName($product, '33 Diamonds');
    
    if ($package) {
        // ตรวจสอบยอดเงินก่อนซื้อ
        if ($api->hasEnoughBalance((float)$package['price'])) {
            try {
                $result = $api->buyWithPlayerId(
                    $package['id'],
                    '277272', // Player ID
                    'https://yoursite.com/callback' // Optional callback
                );
                
                echo "Purchase Result:\n";
                echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
            } catch (Exception $e) {
                echo "Purchase Failed: " . $e->getMessage() . "\n";
            }
        } else {
            echo "ยอดเงินไม่เพียงพอ\n";
        }
    }
    */

    // ========================================
    // 9. ข้อมูล HTTP Response ล่าสุด
    // ========================================
    echo "9. ข้อมูล HTTP Response ล่าสุด\n";
    echo "------------------------------\n";
    
    $lastResponse = $api->getLastResponse();
    if ($lastResponse) {
        echo "HTTP Status Code: {$lastResponse['status_code']}\n";
        echo "Response Data: " . json_encode($lastResponse['data'], JSON_PRETTY_PRINT) . "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
}

// ========================================
// ตัวอย่างการใช้งานจริง
// ========================================
echo "\n\n=== Use Case Examples ===\n\n";

echo "Use Case 1: ระบบเติมเกมอัตโนมัติ\n";
echo "-----------------------------------\n";
echo "<?php\n";
echo "// รับข้อมูลจากฟอร์ม\n";
echo "\$gameId = \$_POST['game_id'];\n";
echo "\$packageId = \$_POST['package_id'];\n";
echo "\$playerId = \$_POST['player_id'];\n";
echo "\n";
echo "// สร้าง API instance\n";
echo "\$api = new TermgameSellerV2(\$apiKey);\n";
echo "\n";
echo "// ตรวจสอบยอดเงิน\n";
echo "\$product = \$api->findProductById(\$gameId);\n";
echo "\$package = \$api->findPackageById(\$product, \$packageId);\n";
echo "\n";
echo "if (\$api->hasEnoughBalance(\$package['price'])) {\n";
echo "    \$result = \$api->buyWithPlayerId(\$packageId, \$playerId);\n";
echo "    echo 'Success: ' . json_encode(\$result);\n";
echo "} else {\n";
echo "    echo 'ยอดเงินไม่เพียงพอ';\n";
echo "}\n";
echo "?>\n\n";

echo "Use Case 2: แสดงรายการสินค้าในเว็บไซต์\n";
echo "--------------------------------------\n";
echo "<?php\n";
echo "\$api = new TermgameSellerV2(\$apiKey);\n";
echo "\$products = \$api->getActiveProducts();\n";
echo "\n";
echo "foreach (\$products as \$product) {\n";
echo "    echo '<div class=\"product\">';\n";
echo "    echo '<h3>' . \$product['name'] . '</h3>';\n";
echo "    \n";
echo "    \$packages = \$api->getActivePackages(\$product);\n";
echo "    foreach (\$packages as \$package) {\n";
echo "        echo '<div class=\"package\">';\n";
echo "        echo \$package['name'] . ' - ';\n";
echo "        echo \$api->formatPrice(\$package['price']);\n";
echo "        echo '</div>';\n";
echo "    }\n";
echo "    \n";
echo "    echo '</div>';\n";
echo "}\n";
echo "?>\n";
