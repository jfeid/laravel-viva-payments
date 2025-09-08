# TSD-1416 Fork Implementation Guide

## sebdesign/laravel-viva-payments Fork για Multi-Account Support

### Στόχος
Δημιουργία προσαρμοσμένου fork του sebdesign/laravel-viva-payments που υποστηρίζει πολλαπλούς λογαριασμούς Viva Wallet για διαφορετικά σχολεία.

### Βασική Πληροφορία
- **Base Version**: v5.3.1 (τελευταία συμβατή με Laravel 5.7)
- **Target Version**: v5.3.1-multi-account
- **Compatibility**: Laravel 5.7.29, PHP 7.4

## Development Strategy

### Fork Repository Setup
```bash
# 1. Fork το repository στο GitHub/GitLab
git clone https://github.com/sebdesign/laravel-viva-payments.git
cd laravel-viva-payments

# 2. Checkout στην έκδοση 5.3.1 που χρησιμοποιεί το έργο
git checkout v5.3.1

# 3. Δημιουργία νέου remote για το fork
git remote add fork https://github.com/your-organization/laravel-viva-payments.git
git remote set-url origin https://github.com/your-organization/laravel-viva-payments.git

# 4. Δημιουργία development branch από την v5.3.1
git checkout -b feature/multi-account-support-5.3.1
```

### Development Environment
```bash
# Local development setup
mkdir -p ~/Development/viva-payments-fork
cd ~/Development/viva-payments-fork
git clone https://github.com/your-organization/laravel-viva-payments.git .

# Δημιουργία ddev environment για τον fork
ddev config --project-type=php --php-version=7.4
ddev start
ddev composer install
```

## Προτεινόμενες Τροποποιήσεις Κώδικα

### 1. Client Class Modifications
**Αρχείο**: `src/Client.php`

```php
<?php

namespace Sebdesign\VivaPayments;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\RequestOptions;
// ... existing imports

class Client
{
    // ... existing properties
    
    /**
     * @var string|null
     */
    protected $merchantId;
    
    /**
     * @var string|null
     */
    protected $apiKey;

    /**
     * Constructor.
     */
    public function __construct(
        GuzzleClient $client, 
        string $environment,
        ?string $merchantId = null,
        ?string $apiKey = null
    ) {
        $this->client = $client;

        if (! in_array($environment, ['demo', 'production'])) {
            throw new InvalidArgumentException(
                'The Viva Payments environment must be demo or production.'
            );
        }

        $this->environment = $environment;
        $this->merchantId = $merchantId ?? config('services.viva.merchant_id');
        $this->apiKey = $apiKey ?? config('services.viva.api_key');
    }

    // ... existing methods

    /**
     * Authenticate using basic auth.
     */
    public function authenticateWithBasicAuth(): array
    {
        return [
            RequestOptions::AUTH => [
                $this->merchantId,
                $this->apiKey,
            ],
        ];
    }

    /**
     * Set runtime credentials.
     */
    public function setCredentials(string $merchantId, string $apiKey): self
    {
        $this->merchantId = $merchantId;
        $this->apiKey = $apiKey;
        
        return $this;
    }
    
    /**
     * Get current merchant ID.
     */
    public function getMerchantId(): ?string
    {
        return $this->merchantId;
    }
    
    /**
     * Get current API key (για debugging - προσοχή στην ασφάλεια).
     */
    public function hasApiKey(): bool
    {
        return !empty($this->apiKey);
    }
}
```

### 2. Service Provider Enhancement
**Αρχείο**: `src/VivaPaymentsServiceProvider.php`

```php
<?php

namespace Sebdesign\VivaPayments;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\ServiceProvider;

class VivaPaymentsServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/services.php',
            'services'
        );

        // Bind Client με support για runtime parameters
        $this->app->bind(Client::class, function ($app, $parameters = []) {
            return new Client(
                $this->buildGuzzleClient(),
                $parameters['environment'] ?? $app->make('config')->get('services.viva.environment'),
                $parameters['merchant_id'] ?? null,
                $parameters['api_key'] ?? null
            );
        });
        
        // Register Factory για εύκολη χρήση
        $this->app->singleton(VivaPaymentsFactory::class, function ($app) {
            return new VivaPaymentsFactory($app);
        });
    }

    // ... existing methods
}
```

### 3. Factory Class για Multi-Account
**Νέο Αρχείο**: `src/VivaPaymentsFactory.php`

```php
<?php

namespace Sebdesign\VivaPayments;

use Illuminate\Contracts\Container\Container;

class VivaPaymentsFactory
{
    /**
     * @var Container
     */
    protected $app;

    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * Create Client instance με custom credentials.
     */
    public function createForCredentials(
        string $merchantId, 
        string $apiKey, 
        string $environment = 'production'
    ): Client {
        return $this->app->make(Client::class, [
            'environment' => $environment,
            'merchant_id' => $merchantId,
            'api_key' => $apiKey,
        ]);
    }
    
    /**
     * Create Order instance με custom credentials.
     */
    public function createOrderForCredentials(
        string $merchantId, 
        string $apiKey, 
        string $environment = 'production'
    ): Order {
        $client = $this->createForCredentials($merchantId, $apiKey, $environment);
        return new Order($client);
    }
    
    /**
     * Create Transaction instance με custom credentials.
     */
    public function createTransactionForCredentials(
        string $merchantId, 
        string $apiKey, 
        string $environment = 'production'
    ): Transaction {
        $client = $this->createForCredentials($merchantId, $apiKey, $environment);
        return new Transaction($client);
    }
    
    /**
     * Create από School Integration config.
     */
    public function createFromConfig(array $config): Client
    {
        return $this->createForCredentials(
            $config['merchant_id'],
            $config['api_key'],
            $config['environment'] ?? 'production'
        );
    }
}
```

### 4. Helper Methods στις Κύριες Κλάσεις

**Προσθήκη στο Order.php**:
```php
/**
 * Create Order με custom client.
 */
public static function withCredentials(
    string $merchantId, 
    string $apiKey, 
    string $environment = 'production'
): self {
    $factory = app(VivaPaymentsFactory::class);
    $client = $factory->createForCredentials($merchantId, $apiKey, $environment);
    return new static($client);
}
```

**Προσθήκη στο Transaction.php**:
```php
/**
 * Create Transaction με custom client.
 */
public static function withCredentials(
    string $merchantId, 
    string $apiKey, 
    string $environment = 'production'
): self {
    $factory = app(VivaPaymentsFactory::class);
    $client = $factory->createForCredentials($merchantId, $apiKey, $environment);
    return new static($client);
}
```

## Integration με Total School

### Composer Configuration
**Αρχείο**: `composer.json`

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/your-organization/laravel-viva-payments"
    }
  ],
  "require": {
    "your-organization/laravel-viva-payments": "dev-feature/multi-account-support-5.3.1"
  }
}
```

**Σημείωση**: Αντικατάσταση του `"sebdesign/laravel-viva-payments": "^5.3"` με το custom fork.

### VivaController Modifications
**Αρχείο**: `app/Http/Controllers/VivaController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Events\PaymentConfirmed;
use App\SchoolPayment;
use App\SchoolPaymentTransaction;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Sebdesign\VivaPayments\Order;
use Sebdesign\VivaPayments\Transaction;
use Sebdesign\VivaPayments\VivaPaymentsFactory;
use Sebdesign\VivaPayments\WebhookController;

class VivaController extends WebhookController
{
    /**
     * @var VivaPaymentsFactory
     */
    protected $vivaFactory;

    public function __construct(VivaPaymentsFactory $vivaFactory)
    {
        $this->vivaFactory = $vivaFactory;
        parent::__construct(app(\Sebdesign\VivaPayments\Webhook::class));
    }

    public function redirect(SchoolPayment $schoolPayment)
    {
        try {
            $this->preValidateSchoolPayment($schoolPayment);

            if (empty($schoolPayment->order_code)) {
                $orderCode = $this->createPaymentOrder($schoolPayment);
                $schoolPayment->update(['order_code' => $orderCode]);
            }

            $checkoutUrl = $this->getCheckoutUrl($schoolPayment);

            return redirect()->away($checkoutUrl);

        } catch (Exception $e) {
            Log::channel('payment')->error($e);
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    // ... existing methods

    /**
     * Create payment order using school-specific Viva credentials.
     */
    private function createPaymentOrder(SchoolPayment $schoolPayment): string
    {
        Log::channel('payment')->debug('Creating Viva payment order.', ['school_payment_id' => $schoolPayment->id]);

        $school = $schoolPayment->school;
        $vivaIntegration = $school->integrations()
            ->where('type', 'viva')->first();
            
        if (!$vivaIntegration) {
            throw new Exception('Viva integration not configured for school: ' . $school->name);
        }

        $config = $vivaIntegration->config;
        $order = $this->vivaFactory->createOrderForCredentials(
            $config['merchant_id'],
            $config['api_key'],
            $config['environment'] ?? 'production'
        );

        $parameters = [
            'sourceCode' => $config['source_code'] ?? 'Default',
            'merchantTrns' => $schoolPayment->id,
            'customerTrns' => 'Πληρωμή ' . config('app.name'),
            'customer' => [
                'email' => $school->email,
                'fullName' => $school->name,
                'phone' => $school->phone_number,
                'countryCode' => $school->country,
                'requestLang' => 'el-GR'
            ],
        ];

        $orderAmount = (int) bcmul($schoolPayment->amount, 100, 0);
        $orderCode = $order->create($orderAmount, $parameters);

        Log::channel('payment')->info('Created Viva payment order', [
            'school' => $school->id,
            'amount' => $orderAmount,
            'order_code' => $orderCode,
            'parameters' => $parameters,
        ]);

        return (string) $orderCode;
    }

    /**
     * Get checkout URL for school-specific order.
     */
    private function getCheckoutUrl(SchoolPayment $schoolPayment): string
    {
        $school = $schoolPayment->school;
        $vivaIntegration = $school->integrations()
            ->where('type', 'viva')->first();

        if (!$vivaIntegration) {
            throw new Exception('Viva integration not configured for school: ' . $school->name);
        }

        $config = $vivaIntegration->config;
        $order = $this->vivaFactory->createOrderForCredentials(
            $config['merchant_id'],
            $config['api_key'],
            $config['environment'] ?? 'production'
        );

        return $order->getCheckoutUrl($schoolPayment->order_code);
    }

    /**
     * Retrieve transaction using school-specific credentials.
     */
    private function retrieveTransaction($transactionId, SchoolPayment $schoolPayment)
    {
        Log::channel('payment')->debug('Retrieving transaction.', ['transaction_id' => $transactionId]);

        $school = $schoolPayment->school;
        $vivaIntegration = $school->integrations()
            ->where('type', 'viva')->first();

        if (!$vivaIntegration) {
            throw new Exception('Viva integration not configured for school: ' . $school->name);
        }

        $config = $vivaIntegration->config;
        $transaction = $this->vivaFactory->createTransactionForCredentials(
            $config['merchant_id'],
            $config['api_key'],
            $config['environment'] ?? 'production'
        );

        $response = $transaction->get($transactionId);

        Log::channel('payment')->debug('Transaction retrieved.', [
            'transaction_id' => $transactionId, 
            'response' => $response
        ]);

        return Arr::get($response, 0);
    }

    /**
     * Retrieve order using school-specific credentials.
     */
    private function retrieveOrder($orderCode, SchoolPayment $schoolPayment = null)
    {
        Log::channel('payment')->debug('Retrieving order.', ['order_code' => $orderCode]);

        if (!$schoolPayment) {
            $schoolPayment = $this->getSchoolPayment($orderCode);
        }

        if (!$schoolPayment) {
            throw new Exception('School payment not found for order: ' . $orderCode);
        }

        $school = $schoolPayment->school;
        $vivaIntegration = $school->integrations()
            ->where('type', 'viva')->first();

        if (!$vivaIntegration) {
            throw new Exception('Viva integration not configured for school: ' . $school->name);
        }

        $config = $vivaIntegration->config;
        $order = $this->vivaFactory->createOrderForCredentials(
            $config['merchant_id'],
            $config['api_key'],
            $config['environment'] ?? 'production'
        );

        $response = $order->get($orderCode);

        Log::channel('payment')->debug('Order retrieved.', [
            'order_code' => $orderCode, 
            'response' => $response
        ]);

        return $response;
    }

    // ... rest of existing methods με την ίδια λογική προσαρμογής
}
```

## Testing Strategy

### 1. Unit Tests για το Fork
```bash
# Στον fork φάκελο
ddev exec vendor/bin/phpunit
```

### 2. Integration Tests
```php
// tests/Integration/MultiAccountTest.php
class MultiAccountTest extends TestCase
{
    public function test_different_schools_use_different_credentials()
    {
        $school1 = factory(School::class)->create();
        $school2 = factory(School::class)->create();
        
        // Setup different Viva integrations για κάθε σχολείο
        $school1->integrations()->create([
            'integration_id' => Integration::where('type', 'viva')->first()->id,
            'config' => [
                'merchant_id' => 'MERCHANT_1',
                'api_key' => 'API_KEY_1',
                'environment' => 'demo'
            ]
        ]);
        
        $school2->integrations()->create([
            'integration_id' => Integration::where('type', 'viva')->first()->id,
            'config' => [
                'merchant_id' => 'MERCHANT_2', 
                'api_key' => 'API_KEY_2',
                'environment' => 'demo'
            ]
        ]);
        
        $factory = app(VivaPaymentsFactory::class);
        
        $client1 = $factory->createFromConfig($school1->integrations()->first()->config);
        $client2 = $factory->createFromConfig($school2->integrations()->first()->config);
        
        $this->assertEquals('MERCHANT_1', $client1->getMerchantId());
        $this->assertEquals('MERCHANT_2', $client2->getMerchantId());
    }
}
```

### 3. Manual Testing με ddev
```bash
# Στο total-school έργο
ddev composer update your-organization/laravel-viva-payments
ddev php artisan tinker

# Test factory functionality
$factory = app(\Sebdesign\VivaPayments\VivaPaymentsFactory::class);
$client = $factory->createForCredentials('TEST_MERCHANT', 'TEST_KEY', 'demo');
$order = new \Sebdesign\VivaPayments\Order($client);
```

## Development Workflow

### 1. Development στον Fork
```bash
cd ~/Development/viva-payments-fork
ddev start

# Κάνε αλλαγές
git add .
git commit -m "Add multi-account support functionality"
git push origin feature/multi-account-support
```

### 2. Testing στο Total School
```bash
cd ~/Projects/verisys/total-school
ddev composer update your-organization/laravel-viva-payments
ddev php artisan test
```

### 3. Version Release
```bash
# Στον fork
git tag v5.3.1-multi-account
git push origin v5.3.1-multi-account

# Update total-school composer.json για stable version
"your-organization/laravel-viva-payments": "~5.3.1"
```

**Version Strategy**: Χρήση v5.3.1 ως base με suffix "-multi-account" για να δείχνει ότι είναι custom version.

## Migration από Existing Implementation

### Current State
```json
// composer.json - τρέχουσα κατάσταση
"require": {
    "sebdesign/laravel-viva-payments": "^5.3",
    // ... other packages
}
```

### Migration Steps
```bash
# 1. Backup τρέχουσας κατάστασης
ddev composer show sebdesign/laravel-viva-payments
# Επιβεβαίωση ότι είναι v5.3.1

# 2. Update composer.json
# Αφαίρεση: "sebdesign/laravel-viva-payments": "^5.3"
# Προσθήκη: "your-organization/laravel-viva-payments": "dev-feature/multi-account-support-5.3.1"
# Προσθήκη repository

# 3. Install custom fork
ddev composer update

# 4. Verify installation
ddev composer show your-organization/laravel-viva-payments
```

### Backward Compatibility
- ✅ Η υπάρχουσα functionality παραμένει ανέπαφη
- ✅ Οι υπάρχοντες controller calls θα συνεχίσουν να δουλεύουν  
- ✅ Χρήση των default config values όταν δεν υπάρχει custom config

### Testing Migration
```php
// Επαλήθευση ότι η παλιά λειτουργικότητα δουλεύει
$order = app(\Sebdesign\VivaPayments\Order::class);
$transaction = app(\Sebdesign\VivaPayments\Transaction::class);

// Επαλήθευση νέας λειτουργικότητας
$factory = app(\Sebdesign\VivaPayments\VivaPaymentsFactory::class);
$customOrder = $factory->createOrderForCredentials('TEST', 'KEY', 'demo');
```

## Implementation Checklist

- [ ] Fork repository setup
- [ ] Development environment με ddev
- [ ] Client class modifications
- [ ] Service provider enhancements  
- [ ] Factory class implementation
- [ ] VivaController adaptations
- [ ] Unit tests για νέα functionality
- [ ] Integration tests με School entities
- [ ] Manual testing με different credentials
- [ ] Documentation updates
- [ ] Version tagging και release

## Notes

### Version Compatibility
- **Base Version**: sebdesign/laravel-viva-payments v5.3.1 (Μάρτιος 2023)
- **Laravel Version**: 5.7.29 (συμβατό)
- **PHP Version**: 7.4 (συμβατό)
- **Approach**: Προσθήκη functionality χωρίς breaking changes

### Development Notes
- Διατήρηση backward compatibility με existing functionality
- Προσοχή στην ασφάλεια των credentials (encryption στη βάση)
- Testing με demo environment πριν το production  
- Monitoring logs για debugging multi-account functionality
- Χρήση default config values για fallback behavior

### Migration Strategy
- Η μετάβαση γίνεται χωρίς διακοπή της τρέχουσας λειτουργικότητας
- Οι υπάρχοντες school payments θα συνεχίσουν να λειτουργούν
- Νέα multi-account functionality θα είναι opt-in μέσω school integrations