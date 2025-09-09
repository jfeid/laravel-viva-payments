<?php

namespace Sebdesign\VivaPayments\Enums;

class Environment
{
    const DEMO = 'demo';
    const PRODUCTION = 'production';

    /**
     * Get all available environments.
     *
     * @return string[]
     */
    public static function all()
    {
        return [
            self::DEMO,
            self::PRODUCTION,
        ];
    }

    /**
     * Check if the given environment is valid.
     *
     * @param string $environment
     * @return bool
     */
    public static function isValid($environment)
    {
        return in_array($environment, self::all());
    }
}