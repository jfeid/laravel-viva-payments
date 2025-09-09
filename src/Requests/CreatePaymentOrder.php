<?php

namespace Sebdesign\VivaPayments\Requests;

class CreatePaymentOrder
{
    /**
     * @var int The requested amount in the currency's smallest unit of measurement (minimum 30)
     */
    public $amount;

    /**
     * @var string|null Optional customer transaction reference
     */
    public $customerTrns;

    /**
     * @var Customer|null Customer information
     */
    public $customer;

    /**
     * @var int Payment timeout in seconds (0-432000, default 1800)
     */
    public $paymentTimeOut;

    /**
     * @var string|null Currency code (EUR, USD, etc.)
     */
    public $currencyCode;

    /**
     * @var bool Whether this is a preauthorization transaction
     */
    public $preauth;

    /**
     * @var bool Whether to allow recurring payments
     */
    public $allowRecurring;

    /**
     * @var int Maximum number of installments (0-36)
     */
    public $maxInstallments;

    /**
     * @var bool Whether to send payment notification
     */
    public $paymentNotification;

    /**
     * @var int Tip amount in the currency's smallest unit
     */
    public $tipAmount;

    /**
     * @var bool Whether to disable exact amount requirement
     */
    public $disableExactAmount;

    /**
     * @var bool Whether to disable cash payments
     */
    public $disableCash;

    /**
     * @var bool Whether to disable wallet payments
     */
    public $disableWallet;

    /**
     * @var int|null ISV amount
     */
    public $isvAmount;

    /**
     * @var string Source code identifier
     */
    public $sourceCode;

    /**
     * @var string|null Merchant transaction reference
     */
    public $merchantTrns;

    /**
     * @var string[]|null Array of transaction tags
     */
    public $tags;

    /**
     * @var string[]|null Array of card tokens
     */
    public $cardTokens;

    /**
     * @var string|null Reseller source code
     */
    public $resellerSourceCode;

    /**
     * Constructor.
     */
    public function __construct(
        $amount,
        $customerTrns = null,
        $customer = null,
        $paymentTimeOut = 1800,
        $currencyCode = null,
        $preauth = false,
        $allowRecurring = false,
        $maxInstallments = 0,
        $paymentNotification = false,
        $tipAmount = 0,
        $disableExactAmount = false,
        $disableCash = false,
        $disableWallet = false,
        $isvAmount = null,
        $sourceCode = 'Default',
        $merchantTrns = null,
        $tags = null,
        $cardTokens = null,
        $resellerSourceCode = null
    ) {
        $this->amount = $amount;
        $this->customerTrns = $customerTrns;
        $this->customer = $customer;
        $this->paymentTimeOut = $paymentTimeOut;
        $this->currencyCode = $currencyCode;
        $this->preauth = $preauth;
        $this->allowRecurring = $allowRecurring;
        $this->maxInstallments = $maxInstallments;
        $this->paymentNotification = $paymentNotification;
        $this->tipAmount = $tipAmount;
        $this->disableExactAmount = $disableExactAmount;
        $this->disableCash = $disableCash;
        $this->disableWallet = $disableWallet;
        $this->isvAmount = $isvAmount;
        $this->sourceCode = $sourceCode;
        $this->merchantTrns = $merchantTrns;
        $this->tags = $tags;
        $this->cardTokens = $cardTokens;
        $this->resellerSourceCode = $resellerSourceCode;
    }

    /**
     * Convert the request to an array for JSON serialization.
     *
     * @return array
     */
    public function toArray()
    {
        $data = [];

        foreach (get_object_vars($this) as $key => $value) {
            if ($value !== null) {
                $data[$key] = $value;
            }
        }

        return $data;
    }
}