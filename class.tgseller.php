<?php

/**
 * Termgame Seller V2 API SDK
 * 
 * PHP SDK for interacting with Termgame Seller V2 API
 * 
 * @version 2.0.0
 * @author Termgame Seller
 * @link https://api-v2.termgameseller.com
 */
class TermgameSellerV2
{
    /**
     * API Base URL
     */
    private const BASE_URL = 'https://api-v2.termgameseller.com/v1/api';

    /**
     * API Key for authentication
     */
    private string $apiKey;

    /**
     * HTTP timeout in seconds
     */
    private int $timeout;

    /**
     * Last HTTP response
     */
    private ?array $lastResponse = null;

    /**
     * Constructor
     * 
     * @param string $apiKey API Key from Termgame Seller
     * @param int $timeout HTTP request timeout in seconds
     * @throws Exception if API key is empty
     */
    public function __construct(string $apiKey, int $timeout = 30)
    {
        if (empty($apiKey)) {
            throw new Exception('API Key is required');
        }

        $this->apiKey = $apiKey;
        $this->timeout = $timeout;
    }

    /**
     * Get account balance
     * 
     * @return array Balance information
     * @throws Exception on API error
     */
    public function getBalance(): array
    {
        $response = $this->request('GET', '/balance');
        return $response;
    }

    /**
     * Get account balance as float
     * 
     * @return float Balance amount
     * @throws Exception on API error
     */
    public function getBalanceAmount(): float
    {
        $balance = $this->getBalance();
        return isset($balance['balance']) ? (float)$balance['balance'] : 0.0;
    }

    /**
     * Check if account has enough balance
     * 
     * @param float $amount Amount to check
     * @return bool True if balance is sufficient
     * @throws Exception on API error
     */
    public function hasEnoughBalance(float $amount): bool
    {
        return $this->getBalanceAmount() >= $amount;
    }

    /**
     * Get all products with packages and servers
     * 
     * @return array List of products
     * @throws Exception on API error
     */
    public function getProducts(): array
    {
        $response = $this->request('GET', '/products');
        return $response;
    }

    /**
     * Get only active products
     * 
     * @return array List of active products
     * @throws Exception on API error
     */
    public function getActiveProducts(): array
    {
        $products = $this->getProducts();
        return array_filter($products, function($product) {
            return isset($product['isActive']) && $product['isActive'] === true;
        });
    }

    /**
     * Find product by name
     * 
     * @param string $name Product name to search for
     * @return array|null Product data or null if not found
     * @throws Exception on API error
     */
    public function findProductByName(string $name): ?array
    {
        $products = $this->getProducts();
        
        foreach ($products as $product) {
            if (stripos($product['name'], $name) !== false) {
                return $product;
            }
        }
        
        return null;
    }

    /**
     * Find product by ID
     * 
     * @param string $productId Product ID
     * @return array|null Product data or null if not found
     * @throws Exception on API error
     */
    public function findProductById(string $productId): ?array
    {
        $products = $this->getProducts();
        
        foreach ($products as $product) {
            if ($product['id'] === $productId) {
                return $product;
            }
        }
        
        return null;
    }

    /**
     * Get active packages from a product
     * 
     * @param array $product Product data
     * @return array List of active packages
     */
    public function getActivePackages(array $product): array
    {
        if (!isset($product['packages']) || !is_array($product['packages'])) {
            return [];
        }

        return array_filter($product['packages'], function($package) {
            return isset($package['isActive']) && $package['isActive'] === true;
        });
    }

    /**
     * Find package by name in a product
     * 
     * @param array $product Product data
     * @param string $packageName Package name to search for
     * @return array|null Package data or null if not found
     */
    public function findPackageByName(array $product, string $packageName): ?array
    {
        if (!isset($product['packages']) || !is_array($product['packages'])) {
            return null;
        }

        foreach ($product['packages'] as $package) {
            if (stripos($package['name'], $packageName) !== false) {
                return $package;
            }
        }

        return null;
    }

    /**
     * Find package by ID in a product
     * 
     * @param array $product Product data
     * @param string $packageId Package ID
     * @return array|null Package data or null if not found
     */
    public function findPackageById(array $product, string $packageId): ?array
    {
        if (!isset($product['packages']) || !is_array($product['packages'])) {
            return null;
        }

        foreach ($product['packages'] as $package) {
            if ($package['id'] === $packageId) {
                return $package;
            }
        }

        return null;
    }

    /**
     * Get all orders
     * 
     * @return array List of orders
     * @throws Exception on API error
     */
    public function getOrders(): array
    {
        $response = $this->request('GET', '/orders');
        return $response;
    }

    /**
     * Get order status by order ID
     * 
     * @param string $orderId Order ID
     * @return array Order details
     * @throws Exception on API error
     */
    public function getOrderStatus(string $orderId): array
    {
        $response = $this->request('GET', '/order-status/' . $orderId);
        return $response;
    }

    /**
     * Buy a package
     * 
     * @param string $packageId Package ID to purchase
     * @param array $data Purchase data (playerId, url, zoneId, etc.)
     * @return array Purchase result
     * @throws Exception on API error
     */
    public function buyPackage(string $packageId, array $data = []): array
    {
        $response = $this->request('POST', '/buy/' . $packageId, $data);
        return $response;
    }

    /**
     * Buy package with player ID
     * 
     * @param string $packageId Package ID
     * @param string $playerId Player ID
     * @param string|null $callbackUrl Optional callback URL
     * @return array Purchase result
     * @throws Exception on API error
     */
    public function buyWithPlayerId(string $packageId, string $playerId, ?string $callbackUrl = null): array
    {
        $data = ['playerId' => $playerId];
        
        if ($callbackUrl) {
            $data['callbackUrl'] = $callbackUrl;
        }

        return $this->buyPackage($packageId, $data);
    }

    /**
     * Buy package with URL
     * 
     * @param string $packageId Package ID
     * @param string $url Payment URL
     * @param string|null $callbackUrl Optional callback URL
     * @return array Purchase result
     * @throws Exception on API error
     */
    public function buyWithUrl(string $packageId, string $url, ?string $callbackUrl = null): array
    {
        $data = ['url' => $url];
        
        if ($callbackUrl) {
            $data['callbackUrl'] = $callbackUrl;
        }

        return $this->buyPackage($packageId, $data);
    }

    /**
     * Buy package with username and password
     * 
     * @param string $packageId Package ID
     * @param string $username Username
     * @param string $password Password
     * @param string|null $callbackUrl Optional callback URL
     * @return array Purchase result
     * @throws Exception on API error
     */
    public function buyWithCredentials(string $packageId, string $username, string $password, ?string $callbackUrl = null): array
    {
        $data = [
            'username' => $username,
            'password' => $password
        ];
        
        if ($callbackUrl) {
            $data['callbackUrl'] = $callbackUrl;
        }

        return $this->buyPackage($packageId, $data);
    }

    /**
     * Get last HTTP response
     * 
     * @return array|null Last response data
     */
    public function getLastResponse(): ?array
    {
        return $this->lastResponse;
    }

    /**
     * Format price with currency
     * 
     * @param string|float $price Price amount
     * @param string $currency Currency symbol
     * @return string Formatted price
     */
    public function formatPrice($price, string $currency = '฿'): string
    {
        return number_format((float)$price, 2) . ' ' . $currency;
    }

    /**
     * Make HTTP request to API
     * 
     * @param string $method HTTP method (GET, POST, PUT, DELETE)
     * @param string $endpoint API endpoint
     * @param array $data Request data
     * @return array Response data
     * @throws Exception on error
     */
    private function request(string $method, string $endpoint, array $data = []): array
    {
        $url = self::BASE_URL . $endpoint;

        $ch = curl_init();

        // Set base options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        // Set headers
        $headers = [
            'x-api-key: ' . $this->apiKey,
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Set request body for POST requests
        if ($method === 'POST' && !empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        // Execute request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        // Handle cURL error
        if ($response === false) {
            throw new Exception('cURL Error: ' . $error);
        }

        // Parse response
        $result = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('JSON Parse Error: ' . json_last_error_msg());
        }

        // Store last response
        $this->lastResponse = [
            'status_code' => $httpCode,
            'data' => $result,
            'raw' => $response
        ];

        // Handle HTTP errors
        if ($httpCode >= 400) {
            $message = isset($result['message']) ? $result['message'] : 'HTTP Error ' . $httpCode;
            throw new Exception($message, $httpCode);
        }

        return $result;
    }
}
