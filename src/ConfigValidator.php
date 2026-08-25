<?php

declare(strict_types=1);

namespace App;

class ConfigValidator
{
    /**
     * @param array<string, mixed> $config
     */
    public static function validate(array $config): bool
    {
        if (empty($config)) {
            return false;
        }

        if (!array_key_exists('databases', $config) || !is_array($config['databases'])) {
            return false;
        }

        foreach ($config['databases'] as $database) {
            if (!is_array($database)) {
                return false;
            }

            if (!array_key_exists('host', $database) || !is_string($database['host'])) {
                return false;
            }

            if (!array_key_exists('username', $database) || !is_string($database['username'])) {
                return false;
            }

            if (!array_key_exists('password', $database) || !is_string($database['password'])) {
                return false;
            }
        }

        return true;
    }
}
