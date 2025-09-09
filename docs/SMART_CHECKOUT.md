# Smart Checkout Support

This Laravel package now supports Viva Payments Smart Checkout v2 API, which is compatible with PHP 7.4+ and Laravel 5.7+.

## Overview

Smart Checkout is the modern payment API from Viva Payments that replaces the deprecated `/api/orders` endpoints with the new `/checkout/v2/orders` endpoints.

### Key Features

- ✅ PHP 7.4+ compatibility (backported from PHP 8.1+ main branch)
- ✅ Laravel 5.7+ compatibility
- ✅ Smart Checkout v2 API support
- ✅ Backward compatibility with legacy APIs
- ✅ Bearer Token authentication
- ✅ Structured Request/Response objects
- ✅ Enhanced payment options (installments, tips, etc.)

## Usage

### Basic Usage

```php
use Sebdesign\VivaPayments\Services\SmartCheckout;
use Sebdesign\VivaPayments\Requests\CreatePaymentOrder;

// Using the dedicated SmartCheckout service
$smartCheckout = app(SmartCheckout::class);

// Create a simple order
$orderCode = $smartCheckout->createSimpleOrder(
    1000, // €10.00 in cents
    'customer-ref-123'
);

// Get redirect URL
$redirectUrl = $smartCheckout->getRedirectUrl($orderCode);
```

### Advanced Usage with CreatePaymentOrder

```php
use Sebdesign\VivaPayments\Requests\CreatePaymentOrder;
use Sebdesign\VivaPayments\Requests\Customer;

// Create customer information
$customer = new Customer(
    'customer@example.com',
    '+30123456789',
    'John Doe',
    'en',
    'GR'
);

// Create payment order with full options
$order = new CreatePaymentOrder(
    1000,                    // amount in cents
    'customer-ref-123',      // customer transaction reference
    $customer,               // customer information
    3600,                    // payment timeout (1 hour)
    'EUR',                   // currency code
    false,                   // preauth
    false,                   // allow recurring
    12,                      // max installments
    true,                    // payment notification
    50,                      // tip amount (€0.50)
    false,                   // disable exact amount
    false,                   // disable cash
    false,                   // disable wallet
    null,                    // ISV amount
    'Default',               // source code
    'merchant-ref-123',      // merchant transaction reference
    ['tag1', 'tag2'],        // tags
    [],                      // card tokens
    null                     // reseller source code
);

$orderCode = $smartCheckout->createOrder($order);
```

### Backward Compatibility

The existing `Order` class has been extended with Smart Checkout methods:

```php
use Sebdesign\VivaPayments\Order;

$order = new Order($client);

// Create Smart Checkout order using legacy interface
$orderCode = $order->createSmartCheckoutOrder(1000, [
    'customerTrns' => 'customer-ref-123',
    'currencyCode' => 'EUR',
    'maxInstallments' => 12,
]);

// Get Smart Checkout URL
$redirectUrl = $order->getSmartCheckoutUrl($orderCode, '#ff6900', 1);
```

### Legacy API (still supported)

The old API endpoints continue to work:

```php
// Legacy order creation (deprecated but functional)
$orderCode = $order->create(1000, [
    'CustomerTrns' => 'customer-ref-123',
    'SourceCode' => 'Default',
]);

// Legacy checkout URL
$checkoutUrl = $order->getCheckoutUrl($orderCode);
```

## Authentication

Smart Checkout requires Bearer Token authentication. Make sure your configuration includes OAuth credentials:

```php
// config/services.php
return [
    'viva' => [
        'environment' => env('VIVA_ENVIRONMENT', 'demo'),
        'merchant_id' => env('VIVA_MERCHANT_ID'),
        'api_key' => env('VIVA_API_KEY'),
        'client_id' => env('VIVA_CLIENT_ID'),        // Required for Smart Checkout
        'client_secret' => env('VIVA_CLIENT_SECRET'), // Required for Smart Checkout
        'public_key' => env('VIVA_PUBLIC_KEY'),
    ],
];
```

## API Endpoints

### Smart Checkout (New)
- **Create Order:** `POST /checkout/v2/orders`
- **Authentication:** Bearer Token
- **Request Format:** Structured objects

### Legacy API (Deprecated but supported)
- **Create Order:** `POST /api/orders`  
- **Authentication:** Basic Auth
- **Request Format:** Simple arrays

## Migration Guide

### From Legacy to Smart Checkout

**Before (Legacy):**
```php
$orderCode = $order->create(1000, [
    'CustomerTrns' => 'ref-123',
    'SourceCode' => 'Default',
]);
```

**After (Smart Checkout):**
```php
$orderCode = $order->createSmartCheckoutOrder(1000, [
    'customerTrns' => 'ref-123',
    'sourceCode' => 'Default',
]);
```

### Key Differences

1. **Parameter naming:** CamelCase → camelCase
2. **Authentication:** Basic Auth → Bearer Token
3. **Request structure:** Arrays → Objects
4. **Response format:** Enhanced with more fields

## Testing

Run Smart Checkout tests:

```bash
# Run all tests
composer test

# Run only Smart Checkout related tests
vendor/bin/phpunit tests/Unit/Requests/
vendor/bin/phpunit tests/Unit/Services/
```

## Requirements

- PHP 7.4+
- Laravel 5.7+
- Guzzle HTTP 6.0|7.0
- Valid Viva Payments OAuth credentials (client_id, client_secret)

## Compatibility Matrix

| Feature | Legacy API | Smart Checkout |
|---------|------------|----------------|
| PHP 7.4 | ✅ | ✅ |
| Laravel 5.7 | ✅ | ✅ |
| Basic Auth | ✅ | ❌ |
| Bearer Token | ❌ | ✅ |
| Installments | Limited | ✅ |
| Tips | ❌ | ✅ |
| Enhanced Customer Info | ❌ | ✅ |
| Structured Requests | ❌ | ✅ |