<?php

namespace Sebdesign\VivaPayments;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\ServiceProvider;
use Sebdesign\VivaPayments\Services;

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

        // Keep singleton behavior for default Client (backward compatibility)
        $this->app->singleton(Client::class, function ($app) {
            return new Client(
                $this->buildGuzzleClient(),
                $app->make('config')->get('services.viva.environment')
            );
        });

        // Bind ClientFactory για multi-account support
        $this->app->bind('viva.client.factory', function ($app, $parameters = []) {
            return new Client(
                $this->buildGuzzleClient(),
                $parameters['environment'] ?? $app->make('config')->get('services.viva.environment'),
                $parameters['merchant_id'] ?? null,
                $parameters['api_key'] ?? null,
                $parameters['client_id'] ?? null,
                $parameters['client_secret'] ?? null
            );
        });
        
        // Register Factory για εύκολη χρήση
        $this->app->singleton(VivaPaymentsFactory::class, function ($app) {
            return new VivaPaymentsFactory($app);
        });

        // Register SmartCheckout service
        $this->app->bind(Services\SmartCheckout::class, function ($app) {
            return new Services\SmartCheckout($app->make(Client::class));
        });
    }

    /**
     * Build the Guzzlehttp client.
     */
    protected function buildGuzzleClient(): GuzzleClient
    {
        return new GuzzleClient([
            'curl' => $this->curlDoesntUseNss()
                ? [CURLOPT_SSL_CIPHER_LIST => 'TLSv1.2']
                : [],
        ]);
    }

    /**
     * Check if cURL doens't use NSS.
     *
     * @return bool
     */
    protected function curlDoesntUseNss()
    {
        $curl = curl_version();

        return ! preg_match('/NSS/', $curl['ssl_version']);
    }

    /**
     * Determine if the provider is deferred.
     *
     * @return bool
     */
    public function isDeferred()
    {
        return true;
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            Client::class,
            'viva.client.factory',
            VivaPaymentsFactory::class,
            Services\SmartCheckout::class
        ];

    }
}
