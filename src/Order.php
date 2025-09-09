<?php

namespace Sebdesign\VivaPayments;

use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\UriInterface;

class Order
{
    const PENDING = 0;
    const EXPIRED = 1;
    const CANCELED = 2;
    const PAID = 3;

    /**
     * @var \Sebdesign\VivaPayments\Client
     */
    protected $client;

    /**
     * Constructor.
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Create a payment order.
     *
     * @see https://developer.vivawallet.com/api-reference-guide/payment-api/#tag/Payments/paths/~1api~1orders/post
     *
     * @param  int  $amount  The requested amount in the currency's smallest unit of measurement.
     * @param  array  $parameters  Optional parameters
     * @param  array  $guzzleOptions  Additional parameters for the Guzzle client
     * @return int
     */
    public function create(
        int $amount,
        array $parameters = [],
        array $guzzleOptions = []
    ) {
        $parameters = array_merge_recursive(['amount' => $amount], $parameters);

        $response = $this->client->post(
            $this->client->getUrl()->withPath('/api/orders'),
            array_merge_recursive(
                [RequestOptions::JSON => $parameters],
                $this->client->authenticateWithBasicAuth(),
                $guzzleOptions
            )
        );

        return $response->OrderCode;
    }

    /**
     * Retrieve information about an order.
     *
     * @see https://developer.vivawallet.com/api-reference-guide/payment-api/#tag/Payments/paths/~1api~1orders/post
     *
     * @param  int  $orderCode  The 16-digit orderCode for which you wish to retrieve information.
     * @param  array  $guzzleOptions  Additional parameters for the Guzzle client
     * @return \stdClass
     */
    public function get($orderCode, array $guzzleOptions = [])
    {
        return $this->client->get(
            $this->client->getUrl()->withPath("/api/orders/{$orderCode}"),
            array_merge_recursive(
                $this->client->authenticateWithBasicAuth(),
                $guzzleOptions
            )
        );
    }

    /**
     * Update certain information of an order.
     *
     * @see https://developer.vivawallet.com/api-reference-guide/payment-api/#tag/Payments/paths/~1api~1orders~1{orderCode}/patch
     *
     * @param  int  $orderCode  The 16-digit orderCode for which you requested information.
     * @param  array  $parameters
     * @param  array  $guzzleOptions  Additional parameters for the Guzzle client
     * @return null
     */
    public function update(
        $orderCode,
        array $parameters,
        array $guzzleOptions = []
    ) {
        return $this->client->patch(
            $this->client->getUrl()->withPath("/api/orders/{$orderCode}"),
            array_merge_recursive(
                [RequestOptions::JSON => $parameters],
                $this->client->authenticateWithBasicAuth(),
                $guzzleOptions
            )
        );
    }

    /**
     * Cancel an order.
     *
     * @see https://developer.vivawallet.com/api-reference-guide/payment-api/#tag/Payments/paths/~1api~1orders~1{orderCode}/delete
     *
     * @param  int  $orderCode  The 16-digit orderCode for which you requested information.
     * @param  array  $guzzleOptions  Additional parameters for the Guzzle client
     * @return \stdClass
     */
    public function cancel($orderCode, array $guzzleOptions = [])
    {
        return $this->client->delete(
            $this->client->getUrl()->withPath("/api/orders/{$orderCode}"),
            array_merge_recursive(
                $this->client->authenticateWithBasicAuth(),
                $guzzleOptions
            )
        );
    }

    /**
     * Get the checkout URL for an order.
     *
     * @param  int  $orderCode
     */
    public function getCheckoutUrl($orderCode): UriInterface
    {
        return Uri::withQueryValue(
            $this->client->getUrl()->withPath('/web/checkout'),
            'ref',
            (string) $orderCode
        );
    }

    /**
     * Create Order με custom client.
     */
    public static function withCredentials(
        string $merchantId, 
        string $apiKey, 
        string $environment = 'production',
        ?string $clientId = null,
        ?string $clientSecret = null
    ): self {
        $factory = app(VivaPaymentsFactory::class);
        $client = $factory->createForCredentials($merchantId, $apiKey, $environment, $clientId, $clientSecret);
        return new static($client);
    }

    /**
     * Create Order από config array.
     */
    public static function fromConfig(array $config): self
    {
        $factory = app(VivaPaymentsFactory::class);
        return $factory->createOrderFromConfig($config);
    }

    /**
     * Create payment order using Smart-Checkout v2 API.
     *
     * @see https://developer.vivawallet.com/apis-for-payments/payment-api/#tag/Payments/paths/~1checkout~1v2~1orders/post
     *
     * @param int $amount The requested amount in the currency's smallest unit
     * @param array $parameters Smart-Checkout order parameters
     * @param array $guzzleOptions Additional parameters for the Guzzle client
     * @return string Order code
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws VivaException
     */
    public function createSmartCheckoutOrder($amount, array $parameters = [], array $guzzleOptions = [])
    {
        $orderRequest = new \Sebdesign\VivaPayments\Requests\CreatePaymentOrder(
            $amount,
            isset($parameters['customerTrns']) ? $parameters['customerTrns'] : null,
            isset($parameters['customer']) ? $parameters['customer'] : null,
            isset($parameters['paymentTimeOut']) ? $parameters['paymentTimeOut'] : 1800,
            isset($parameters['currencyCode']) ? $parameters['currencyCode'] : null,
            isset($parameters['preauth']) ? $parameters['preauth'] : false,
            isset($parameters['allowRecurring']) ? $parameters['allowRecurring'] : false,
            isset($parameters['maxInstallments']) ? $parameters['maxInstallments'] : 0,
            isset($parameters['paymentNotification']) ? $parameters['paymentNotification'] : false,
            isset($parameters['tipAmount']) ? $parameters['tipAmount'] : 0,
            isset($parameters['disableExactAmount']) ? $parameters['disableExactAmount'] : false,
            isset($parameters['disableCash']) ? $parameters['disableCash'] : false,
            isset($parameters['disableWallet']) ? $parameters['disableWallet'] : false,
            isset($parameters['isvAmount']) ? $parameters['isvAmount'] : null,
            isset($parameters['sourceCode']) ? $parameters['sourceCode'] : 'Default',
            isset($parameters['merchantTrns']) ? $parameters['merchantTrns'] : null,
            isset($parameters['tags']) ? $parameters['tags'] : null,
            isset($parameters['cardTokens']) ? $parameters['cardTokens'] : null,
            isset($parameters['resellerSourceCode']) ? $parameters['resellerSourceCode'] : null
        );

        $response = $this->client->post(
            $this->client->getApiUrl()->withPath('/checkout/v2/orders'),
            array_merge_recursive(
                [RequestOptions::JSON => $orderRequest->toArray()],
                $this->client->authenticateWithBearerToken(),
                $guzzleOptions
            )
        );

        return isset($response->orderCode) ? (string) $response->orderCode : '';
    }

    /**
     * Get the Smart-Checkout redirect URL for an order.
     *
     * @see https://developer.vivawallet.com/smart-checkout/smart-checkout-integration/#step-2-redirect-the-customer-to-smart-checkout-to-pay-the-payment-order
     *
     * @param string $ref Order code reference
     * @param string|null $color Color theme for checkout page
     * @param int|null $paymentMethod Specific payment method to use
     * @return UriInterface
     */
    public function getSmartCheckoutUrl($ref, $color = null, $paymentMethod = null)
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
}
