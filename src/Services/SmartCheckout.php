<?php

namespace Sebdesign\VivaPayments\Services;

use GuzzleHttp\RequestOptions;
use Psr\Http\Message\UriInterface;
use Sebdesign\VivaPayments\Client;
use Sebdesign\VivaPayments\Requests\CreatePaymentOrder;
use Sebdesign\VivaPayments\VivaException;

class SmartCheckout
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * Constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Create payment order using Smart-Checkout v2 API.
     *
     * @see https://developer.vivawallet.com/apis-for-payments/payment-api/#tag/Payments/paths/~1checkout~1v2~1orders/post
     *
     * @param CreatePaymentOrder $order Payment order details
     * @param array $guzzleOptions Additional parameters for the Guzzle client
     * @return string Order code
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws VivaException
     */
    public function createOrder(CreatePaymentOrder $order, array $guzzleOptions = [])
    {
        $response = $this->client->post(
            $this->client->getApiUrl()->withPath('/checkout/v2/orders'),
            array_merge_recursive(
                [RequestOptions::JSON => $order->toArray()],
                $this->client->authenticateWithBearerToken(),
                $guzzleOptions
            )
        );

        return isset($response->orderCode) ? (string) $response->orderCode : '';
    }

    /**
     * Get the redirect URL to the Smart Checkout for an order.
     *
     * @see https://developer.vivawallet.com/smart-checkout/smart-checkout-integration/#step-2-redirect-the-customer-to-smart-checkout-to-pay-the-payment-order
     *
     * @param string $ref Order code reference
     * @param string|null $color Color theme for checkout page
     * @param int|null $paymentMethod Specific payment method to use
     * @return UriInterface
     */
    public function getRedirectUrl($ref, $color = null, $paymentMethod = null)
    {
        $query = ['ref' => $ref];

        if ($color !== null) {
            $query['color'] = $color;
        }

        if ($paymentMethod !== null) {
            $query['paymentMethod'] = $paymentMethod;
        }

        return $this->client->getUrl()
            ->withPath('/web/checkout')
            ->withQuery(http_build_query($query));
    }

    /**
     * Create a simple order with amount and basic parameters (helper method).
     *
     * @param int $amount Amount in cents
     * @param string|null $customerTrns Customer transaction reference
     * @param array $options Additional order options
     * @param array $guzzleOptions Additional Guzzle options
     * @return string Order code
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws VivaException
     */
    public function createSimpleOrder($amount, $customerTrns = null, array $options = [], array $guzzleOptions = [])
    {
        $order = new CreatePaymentOrder(
            $amount,
            $customerTrns,
            isset($options['customer']) ? $options['customer'] : null,
            isset($options['paymentTimeOut']) ? $options['paymentTimeOut'] : 1800,
            isset($options['currencyCode']) ? $options['currencyCode'] : null,
            isset($options['preauth']) ? $options['preauth'] : false,
            isset($options['allowRecurring']) ? $options['allowRecurring'] : false,
            isset($options['maxInstallments']) ? $options['maxInstallments'] : 0,
            isset($options['paymentNotification']) ? $options['paymentNotification'] : false,
            isset($options['tipAmount']) ? $options['tipAmount'] : 0,
            isset($options['disableExactAmount']) ? $options['disableExactAmount'] : false,
            isset($options['disableCash']) ? $options['disableCash'] : false,
            isset($options['disableWallet']) ? $options['disableWallet'] : false,
            isset($options['isvAmount']) ? $options['isvAmount'] : null,
            isset($options['sourceCode']) ? $options['sourceCode'] : 'Default',
            isset($options['merchantTrns']) ? $options['merchantTrns'] : null,
            isset($options['tags']) ? $options['tags'] : null,
            isset($options['cardTokens']) ? $options['cardTokens'] : null,
            isset($options['resellerSourceCode']) ? $options['resellerSourceCode'] : null
        );

        return $this->createOrder($order, $guzzleOptions);
    }
}