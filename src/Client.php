<?php

namespace Sebdesign\VivaPayments;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\RequestOptions;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

class Client
{
    /**
     * Demo environment URL.
     */
    const DEMO_URL = 'https://demo.vivapayments.com';

    /**
     * Production environment URL.
     */
    const PRODUCTION_URL = 'https://www.vivapayments.com';

    /**
     * Demo environment accounts URL.
     */
    const DEMO_ACCOUNTS_URL = 'https://demo-accounts.vivapayments.com';

    /**
     * Production environment accounts URL.
     */
    const PRODUCTION_ACCOUNTS_URL = 'https://accounts.vivapayments.com';

    /**
     * Demo environment URL.
     */
    const DEMO_API_URL = 'https://demo-api.vivapayments.com';

    /**
     * Production environment URL.
     */
    const PRODUCTION_API_URL = 'https://api.vivapayments.com';

    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $environment;

    /**
     * @var string
     */
    protected $token;

    /**
     * @var string|null
     */
    protected $merchantId;
    
    /**
     * @var string|null
     */
    protected $apiKey;

    /**
     * @var string|null
     */
    protected $clientId;
    
    /**
     * @var string|null
     */
    protected $clientSecret;

    /**
     * Constructor.
     */
    public function __construct(
        GuzzleClient $client, 
        string $environment,
        ?string $merchantId = null,
        ?string $apiKey = null,
        ?string $clientId = null,
        ?string $clientSecret = null
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
        $this->clientId = $clientId ?? config('services.viva.client_id');
        $this->clientSecret = $clientSecret ?? config('services.viva.client_secret');
    }

    /**
     * Make a GET request.
     *
     * @param  string  $url
     * @param  array  $options
     * @return \stdClass
     */
    public function get(string $url, array $options = [])
    {
        $response = $this->client->get($url, $options);

        return $this->getBody($response);
    }

    /**
     * Make a POST request.
     *
     * @param  string  $url
     * @param  array  $options
     * @return \stdClass
     */
    public function post(string $url, array $options = [])
    {
        $response = $this->client->post($url, $options);

        return $this->getBody($response);
    }

    /**
     * Make a PATCH request.
     *
     * @param  string  $url
     * @param  array  $options
     * @return \stdClass|null
     */
    public function patch(string $url, array $options = [])
    {
        $response = $this->client->patch($url, $options);

        return $this->getBody($response);
    }

    /**
     * Make a DELETE request.
     *
     * @param  string  $url
     * @param  array  $options
     * @return \stdClass
     */
    public function delete(string $url, array $options = [])
    {
        $response = $this->client->delete($url, $options);

        return $this->getBody($response);
    }

    /**
     * Get the response body.
     *
     * @param  \Psr\Http\Message\ResponseInterface  $response
     * @return \stdClass|null
     *
     * @throws \Sebdesign\VivaPayments\VivaException
     */
    protected function getBody(ResponseInterface $response)
    {
        /** @var \stdClass|null $body */
        $body = json_decode($response->getBody(), false, 512, JSON_BIGINT_AS_STRING);

        if (isset($body->ErrorCode) && $body->ErrorCode !== 0) {
            throw new VivaException($body->ErrorText, $body->ErrorCode);
        }

        return $body;
    }

    /**
     * Get the URL.
     */
    public function getUrl(): UriInterface
    {
        $uris = [
            'production' => self::PRODUCTION_URL,
            'demo' => self::DEMO_URL,
        ];

        return new Uri($uris[$this->environment]);
    }

    /**
     * Get the accounts URL.
     */
    public function getAccountsUrl(): UriInterface
    {
        $uris = [
            'production' => self::PRODUCTION_ACCOUNTS_URL,
            'demo' => self::DEMO_ACCOUNTS_URL,
        ];

        return new Uri($uris[$this->environment]);
    }

    /**
     * Get the API URL.
     */
    public function getApiUrl(): UriInterface
    {
        $uris = [
            'production' => self::PRODUCTION_API_URL,
            'demo' => self::DEMO_API_URL,
        ];

        return new Uri($uris[$this->environment]);
    }

    /**
     * Get the Guzzle client.
     */
    public function getClient(): GuzzleClient
    {
        return $this->client;
    }

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
     * Authenticate using the public key as a query string.
     */
    public function authenticateWithPublicKey(): array
    {
        return [
            RequestOptions::QUERY => [
                'key' => config('services.viva.public_key'),
            ],
        ];
    }

    /**
     * Authenticate using the bearer token as an authorization header.
     */
    public function authenticateWithBearerToken(): array
    {
        return [
            RequestOptions::HEADERS => [
                'Authorization' => "Bearer {$this->token}",
            ],
        ];
    }

    public function withToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Set runtime credentials for basic auth.
     */
    public function setCredentials(string $merchantId, string $apiKey): self
    {
        $this->merchantId = $merchantId;
        $this->apiKey = $apiKey;
        
        return $this;
    }

    /**
     * Set runtime OAuth credentials.
     */
    public function setOAuthCredentials(string $clientId, string $clientSecret): self
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        
        return $this;
    }

    /**
     * Set all credentials at once.
     */
    public function setAllCredentials(
        string $merchantId,
        string $apiKey,
        string $clientId,
        string $clientSecret
    ): self {
        $this->merchantId = $merchantId;
        $this->apiKey = $apiKey;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        
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
     * Get current client ID.
     */
    public function getClientId(): ?string
    {
        return $this->clientId;
    }
    
    /**
     * Check if API key is set.
     */
    public function hasApiKey(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Check if client secret is set.
     */
    public function hasClientSecret(): bool
    {
        return !empty($this->clientSecret);
    }

    /**
     * Get API key (for internal use).
     */
    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    /**
     * Get client secret (for internal use).
     */
    public function getClientSecret(): ?string
    {
        return $this->clientSecret;
    }
}
