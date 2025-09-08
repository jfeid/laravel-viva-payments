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
        string $environment = 'production',
        ?string $clientId = null,
        ?string $clientSecret = null
    ): Client {
        return $this->app->make('viva.client.factory', [
            'environment' => $environment,
            'merchant_id' => $merchantId,
            'api_key' => $apiKey,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ]);
    }
    
    /**
     * Create Order instance με custom credentials.
     */
    public function createOrderForCredentials(
        string $merchantId, 
        string $apiKey, 
        string $environment = 'production',
        ?string $clientId = null,
        ?string $clientSecret = null
    ): Order {
        $client = $this->createForCredentials($merchantId, $apiKey, $environment, $clientId, $clientSecret);
        return new Order($client);
    }
    
    /**
     * Create Transaction instance με custom credentials.
     */
    public function createTransactionForCredentials(
        string $merchantId, 
        string $apiKey, 
        string $environment = 'production',
        ?string $clientId = null,
        ?string $clientSecret = null
    ): Transaction {
        $client = $this->createForCredentials($merchantId, $apiKey, $environment, $clientId, $clientSecret);
        return new Transaction($client);
    }

    /**
     * Create OAuth instance με custom credentials.
     */
    public function createOAuthForCredentials(
        string $clientId,
        string $clientSecret,
        string $environment = 'production'
    ): OAuth {
        $client = $this->app->make('viva.client.factory', [
            'environment' => $environment,
            'merchant_id' => null,
            'api_key' => null,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ]);
        return new OAuth($client);
    }

    /**
     * Create NativeCheckout instance με custom credentials.
     */
    public function createNativeCheckoutForCredentials(
        string $clientId,
        string $clientSecret,
        string $environment = 'production'
    ): NativeCheckout {
        $client = $this->app->make('viva.client.factory', [
            'environment' => $environment,
            'merchant_id' => null,
            'api_key' => null,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ]);
        return new NativeCheckout($client);
    }
    
    /**
     * Create από School Integration config.
     */
    public function createFromConfig(array $config): Client
    {
        return $this->createForCredentials(
            $config['merchant_id'],
            $config['api_key'],
            $config['environment'] ?? 'production',
            $config['client_id'] ?? null,
            $config['client_secret'] ?? null
        );
    }

    /**
     * Create Order από School Integration config.
     */
    public function createOrderFromConfig(array $config): Order
    {
        $client = $this->createFromConfig($config);
        return new Order($client);
    }

    /**
     * Create Transaction από School Integration config.
     */
    public function createTransactionFromConfig(array $config): Transaction
    {
        $client = $this->createFromConfig($config);
        return new Transaction($client);
    }

    /**
     * Create OAuth από School Integration config.
     */
    public function createOAuthFromConfig(array $config): OAuth
    {
        if (empty($config['client_id']) || empty($config['client_secret'])) {
            throw new \InvalidArgumentException('OAuth requires client_id and client_secret');
        }

        return $this->createOAuthForCredentials(
            $config['client_id'],
            $config['client_secret'],
            $config['environment'] ?? 'production'
        );
    }

    /**
     * Create NativeCheckout από School Integration config.
     */
    public function createNativeCheckoutFromConfig(array $config): NativeCheckout
    {
        if (empty($config['client_id']) || empty($config['client_secret'])) {
            throw new \InvalidArgumentException('NativeCheckout requires client_id and client_secret');
        }

        return $this->createNativeCheckoutForCredentials(
            $config['client_id'],
            $config['client_secret'],
            $config['environment'] ?? 'production'
        );
    }

    /**
     * Create Webhook instance με custom credentials.
     */
    public function createWebhookForCredentials(
        string $merchantId, 
        string $apiKey, 
        string $environment = 'production'
    ): Webhook {
        $client = $this->createForCredentials($merchantId, $apiKey, $environment);
        return new Webhook($client);
    }

    /**
     * Create Webhook από School Integration config.
     */
    public function createWebhookFromConfig(array $config): Webhook
    {
        return $this->createWebhookForCredentials(
            $config['merchant_id'],
            $config['api_key'],
            $config['environment'] ?? 'production'
        );
    }
}