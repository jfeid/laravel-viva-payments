<?php

namespace Sebdesign\VivaPayments\Enums;

class TransactionStatus
{
    const E = 'E'; // Error
    const F = 'F'; // Failure
    const M = 'M'; // Merchant Cancelled
    const P = 'P'; // Pending (pre-auth)
    const R = 'R'; // Refunded
    const X = 'X'; // Expired
    const S = 'S'; // Success

    /**
     * Get all available transaction statuses.
     *
     * @return string[]
     */
    public static function all()
    {
        return [
            self::E,
            self::F,
            self::M,
            self::P,
            self::R,
            self::X,
            self::S,
        ];
    }

    /**
     * Check if the given status is valid.
     *
     * @param string $status
     * @return bool
     */
    public static function isValid($status)
    {
        return in_array($status, self::all());
    }

    /**
     * Get human readable status descriptions.
     *
     * @return string[]
     */
    public static function descriptions()
    {
        return [
            self::E => 'Error',
            self::F => 'Failure',
            self::M => 'Merchant Cancelled',
            self::P => 'Pending (pre-auth)',
            self::R => 'Refunded',
            self::X => 'Expired',
            self::S => 'Success',
        ];
    }
}