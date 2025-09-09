<?php

namespace Sebdesign\VivaPayments\Test\Unit\Requests;

use PHPUnit\Framework\TestCase;
use Sebdesign\VivaPayments\Requests\CreatePaymentOrder;
use Sebdesign\VivaPayments\Requests\Customer;

class CreatePaymentOrderTest extends TestCase
{
    /** @test */
    public function it_can_be_created_with_minimal_parameters()
    {
        $order = new CreatePaymentOrder(1000);

        $this->assertEquals(1000, $order->amount);
        $this->assertEquals(1800, $order->paymentTimeOut);
        $this->assertEquals('Default', $order->sourceCode);
        $this->assertFalse($order->preauth);
        $this->assertFalse($order->allowRecurring);
    }

    /** @test */
    public function it_can_be_created_with_all_parameters()
    {
        $customer = new Customer('test@example.com', '+1234567890', 'John Doe');
        
        $order = new CreatePaymentOrder(
            1000,
            'customer-trns-123',
            $customer,
            3600,
            'EUR',
            true,
            true,
            12,
            true,
            100,
            true,
            false,
            false,
            50,
            'TestSource',
            'merchant-trns-123',
            ['tag1', 'tag2'],
            ['token1', 'token2'],
            'reseller-code'
        );

        $this->assertEquals(1000, $order->amount);
        $this->assertEquals('customer-trns-123', $order->customerTrns);
        $this->assertInstanceOf(Customer::class, $order->customer);
        $this->assertEquals(3600, $order->paymentTimeOut);
        $this->assertEquals('EUR', $order->currencyCode);
        $this->assertTrue($order->preauth);
        $this->assertTrue($order->allowRecurring);
        $this->assertEquals(12, $order->maxInstallments);
        $this->assertTrue($order->paymentNotification);
        $this->assertEquals(100, $order->tipAmount);
        $this->assertTrue($order->disableExactAmount);
        $this->assertFalse($order->disableCash);
        $this->assertFalse($order->disableWallet);
        $this->assertEquals(50, $order->isvAmount);
        $this->assertEquals('TestSource', $order->sourceCode);
        $this->assertEquals('merchant-trns-123', $order->merchantTrns);
        $this->assertEquals(['tag1', 'tag2'], $order->tags);
        $this->assertEquals(['token1', 'token2'], $order->cardTokens);
        $this->assertEquals('reseller-code', $order->resellerSourceCode);
    }

    /** @test */
    public function it_can_be_converted_to_array()
    {
        $customer = new Customer('test@example.com');
        $order = new CreatePaymentOrder(1000, 'test-trns', $customer);

        $array = $order->toArray();

        $this->assertArrayHasKey('amount', $array);
        $this->assertArrayHasKey('customerTrns', $array);
        $this->assertArrayHasKey('customer', $array);
        $this->assertArrayHasKey('paymentTimeOut', $array);
        $this->assertArrayHasKey('sourceCode', $array);
        $this->assertEquals(1000, $array['amount']);
        $this->assertEquals('test-trns', $array['customerTrns']);
        $this->assertInstanceOf(Customer::class, $array['customer']);
    }

    /** @test */
    public function it_excludes_null_values_from_array()
    {
        $order = new CreatePaymentOrder(1000);

        $array = $order->toArray();

        $this->assertArrayNotHasKey('customerTrns', $array);
        $this->assertArrayNotHasKey('customer', $array);
        $this->assertArrayNotHasKey('currencyCode', $array);
        $this->assertArrayHasKey('amount', $array);
        $this->assertArrayHasKey('paymentTimeOut', $array);
        $this->assertArrayHasKey('sourceCode', $array);
    }
}