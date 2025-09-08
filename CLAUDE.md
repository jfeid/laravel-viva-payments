# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Package Overview

This is a Laravel package for integrating with the Viva Wallet payment gateway. It provides support for multiple payment methods including Redirect Checkout, Native Checkout v2, and Simple Checkout, as well as webhook handling.

## Development Commands

### Testing
- Run all tests: `composer test` or `vendor/bin/phpunit --colors=always`
- Run unit tests only: `phpunit --group unit`
- Run functional tests only: `phpunit --group functional` (requires `.env` with credentials)

### Code Quality
- The package uses StyleCI for code style checking
- Scrutinizer for code quality analysis
- PHPUnit for testing with coverage reporting

## Architecture

### Core Components

**Client** (`src/Client.php`): The main HTTP client that handles:
- Environment switching between demo and production
- Authentication methods (Basic Auth, Public Key, Bearer Token)
- HTTP request methods (GET, POST, PATCH, DELETE)
- Error handling via VivaException

**Service Provider** (`src/VivaPaymentsServiceProvider.php`): Laravel service provider that:
- Registers the Client as a singleton
- Configures Guzzle HTTP client with SSL cipher list handling for cURL/NSS compatibility
- Merges package configuration with Laravel's services config

### Payment Method Classes

**Order** (`src/Order.php`): Handles redirect checkout workflow with order states (PENDING, PAID, EXPIRED, CANCELED)

**Transaction** (`src/Transaction.php`): Manages payment transactions including creation, retrieval, and refunds

**NativeCheckout** (`src/NativeCheckout.php`): Implements Native Checkout v2 with tokenization and 3DS support

**OAuth** (`src/OAuth.php`): Manages OAuth token generation for Native Checkout authentication

**Source** (`src/Source.php`): Handles payment source configuration

**Webhook** (`src/Webhook.php`) & **WebhookController** (`src/WebhookController.php`): Webhook verification and event handling

### Configuration

The package expects configuration in `config/services.php` under the 'viva' key:
- `api_key`: API authentication key
- `merchant_id`: Merchant identifier  
- `public_key`: For Simple Checkout only
- `environment`: 'demo' or 'production'
- `client_id` & `client_secret`: For Native Checkout v2

### Environment Endpoints

The Client class defines different URLs for demo vs production:
- Main: `demo.vivapayments.com` / `www.vivapayments.com`
- Accounts: `demo-accounts.vivapayments.com` / `accounts.vivapayments.com` 
- API: `demo-api.vivapayments.com` / `api.vivapayments.com`

### Testing Structure

Tests are organized into:
- `tests/Unit/`: Unit tests for individual components
- `tests/Functional/`: Integration tests requiring API credentials
- Test configuration supports both groups separately

## Laravel Integration

- Auto-discovery supported for Laravel 5.5+
- Service provider registers Client as singleton
- Supports Laravel versions 5.5 through 10.0
- Uses Guzzle 6.0|7.0 for HTTP requests