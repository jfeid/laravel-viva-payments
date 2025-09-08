<?php

namespace Sebdesign\VivaPayments\Test\Unit;

use Sebdesign\VivaPayments\Client;
use Sebdesign\VivaPayments\Order;
use Sebdesign\VivaPayments\Transaction;
use Sebdesign\VivaPayments\OAuth;
use Sebdesign\VivaPayments\VivaPaymentsFactory;
use Sebdesign\VivaPayments\Test\TestCase;

class MultiAccountTest extends TestCase
{
    /**
     * @test
     * @group unit
     */
    public function it_creates_client_with_custom_credentials()
    {
        $factory = app(VivaPaymentsFactory::class);
        
        $client = $factory->createForCredentials(
            'MERCHANT_123',
            'API_KEY_123',
            'demo',
            'CLIENT_123',
            'CLIENT_SECRET_123'
        );

        $this->assertInstanceOf(Client::class, $client);
        $this->assertEquals('MERCHANT_123', $client->getMerchantId());
        $this->assertEquals('CLIENT_123', $client->getClientId());
        $this->assertTrue($client->hasApiKey());
        $this->assertTrue($client->hasClientSecret());
    }

    /**
     * @test
     * @group unit
     */
    public function it_creates_client_from_config()
    {
        $factory = app(VivaPaymentsFactory::class);
        
        $config = [
            'merchant_id' => 'MERCHANT_456',
            'api_key' => 'API_KEY_456',
            'client_id' => 'CLIENT_456',
            'client_secret' => 'CLIENT_SECRET_456',
            'environment' => 'demo'
        ];
        
        $client = $factory->createFromConfig($config);

        $this->assertInstanceOf(Client::class, $client);
        $this->assertEquals('MERCHANT_456', $client->getMerchantId());
        $this->assertEquals('CLIENT_456', $client->getClientId());
    }

    /**
     * @test
     * @group unit
     */
    public function it_creates_order_with_custom_credentials()
    {
        $factory = app(VivaPaymentsFactory::class);
        
        $order = $factory->createOrderForCredentials(
            'MERCHANT_ORDER',
            'API_KEY_ORDER',
            'demo'
        );

        $this->assertInstanceOf(Order::class, $order);
    }

    /**
     * @test
     * @group unit
     */
    public function it_creates_transaction_with_custom_credentials()
    {
        $factory = app(VivaPaymentsFactory::class);
        
        $transaction = $factory->createTransactionForCredentials(
            'MERCHANT_TXN',
            'API_KEY_TXN',
            'demo'
        );

        $this->assertInstanceOf(Transaction::class, $transaction);
    }

    /**
     * @test
     * @group unit
     */
    public function it_creates_oauth_with_custom_credentials()
    {
        $factory = app(VivaPaymentsFactory::class);
        
        $oauth = $factory->createOAuthForCredentials(
            'CLIENT_OAUTH',
            'SECRET_OAUTH',
            'demo'
        );

        $this->assertInstanceOf(OAuth::class, $oauth);
    }

    /**
     * @test
     * @group unit
     */
    public function it_creates_order_from_static_method()
    {
        $order = Order::withCredentials(
            'MERCHANT_STATIC',
            'API_KEY_STATIC',
            'demo'
        );

        $this->assertInstanceOf(Order::class, $order);
    }

    /**
     * @test
     * @group unit
     */
    public function it_creates_transaction_from_static_method()
    {
        $transaction = Transaction::withCredentials(
            'MERCHANT_STATIC_TXN',
            'API_KEY_STATIC_TXN',
            'demo'
        );

        $this->assertInstanceOf(Transaction::class, $transaction);
    }

    /**
     * @test
     * @group unit
     */
    public function it_creates_order_from_config_static_method()
    {
        $config = [
            'merchant_id' => 'MERCHANT_CONFIG',
            'api_key' => 'API_KEY_CONFIG',
            'environment' => 'demo'
        ];

        $order = Order::fromConfig($config);

        $this->assertInstanceOf(Order::class, $order);
    }

    /**
     * @test
     * @group unit  
     */
    public function it_preserves_backward_compatibility_for_default_client()
    {
        // Default client should still work as before
        $defaultClient = app(Client::class);
        
        $this->assertInstanceOf(Client::class, $defaultClient);
        $this->assertEquals('test_merchant', $defaultClient->getMerchantId());
        $this->assertEquals('test_client_id', $defaultClient->getClientId());
    }

    /**
     * @test
     * @group unit
     */
    public function it_validates_oauth_requires_client_credentials()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('OAuth requires client_id and client_secret');
        
        $factory = app(VivaPaymentsFactory::class);
        
        $config = [
            'merchant_id' => 'MERCHANT_OAUTH_FAIL',
            'api_key' => 'API_KEY_OAUTH_FAIL',
            'environment' => 'demo'
            // Missing client_id and client_secret
        ];
        
        $factory->createOAuthFromConfig($config);
    }
}