<?php

namespace Sebdesign\VivaPayments\Test\Unit\Requests;

use PHPUnit\Framework\TestCase;
use Sebdesign\VivaPayments\Requests\Customer;

class CustomerTest extends TestCase
{
    /** @test */
    public function it_can_be_created_with_minimal_parameters()
    {
        $customer = new Customer();

        $this->assertNull($customer->email);
        $this->assertNull($customer->phone);
        $this->assertNull($customer->fullName);
        $this->assertNull($customer->requestLang);
        $this->assertNull($customer->countryCode);
    }

    /** @test */
    public function it_can_be_created_with_all_parameters()
    {
        $customer = new Customer(
            'test@example.com',
            '+1234567890',
            'John Doe',
            'en',
            'US'
        );

        $this->assertEquals('test@example.com', $customer->email);
        $this->assertEquals('+1234567890', $customer->phone);
        $this->assertEquals('John Doe', $customer->fullName);
        $this->assertEquals('en', $customer->requestLang);
        $this->assertEquals('US', $customer->countryCode);
    }

    /** @test */
    public function it_can_be_converted_to_array()
    {
        $customer = new Customer('test@example.com', '+1234567890', 'John Doe');

        $array = $customer->toArray();

        $this->assertArrayHasKey('email', $array);
        $this->assertArrayHasKey('phone', $array);
        $this->assertArrayHasKey('fullName', $array);
        $this->assertEquals('test@example.com', $array['email']);
        $this->assertEquals('+1234567890', $array['phone']);
        $this->assertEquals('John Doe', $array['fullName']);
    }

    /** @test */
    public function it_excludes_null_values_from_array()
    {
        $customer = new Customer('test@example.com');

        $array = $customer->toArray();

        $this->assertArrayHasKey('email', $array);
        $this->assertArrayNotHasKey('phone', $array);
        $this->assertArrayNotHasKey('fullName', $array);
        $this->assertArrayNotHasKey('requestLang', $array);
        $this->assertArrayNotHasKey('countryCode', $array);
    }
}