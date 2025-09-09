<?php

namespace Sebdesign\VivaPayments\Requests;

class Customer
{
    /**
     * @var string|null Customer email address
     */
    public $email;

    /**
     * @var string|null Customer phone number
     */
    public $phone;

    /**
     * @var string|null Customer full name
     */
    public $fullName;

    /**
     * @var string|null Customer request language (ISO 639-1 code)
     */
    public $requestLang;

    /**
     * @var string|null Customer country code (ISO 3166-1 alpha-2)
     */
    public $countryCode;

    /**
     * Constructor.
     */
    public function __construct(
        $email = null,
        $phone = null,
        $fullName = null,
        $requestLang = null,
        $countryCode = null
    ) {
        $this->email = $email;
        $this->phone = $phone;
        $this->fullName = $fullName;
        $this->requestLang = $requestLang;
        $this->countryCode = $countryCode;
    }

    /**
     * Convert the customer to an array for JSON serialization.
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