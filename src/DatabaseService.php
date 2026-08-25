<?php

declare(strict_types=1);

namespace App;

class DatabaseService
{
    private array $config;

    /**
     * @var array<string, \PDO>
     */
    private array $connections = [];

    public function __construct(array $config)
    {
        if (!ConfigValidator::validate($config)) {
            throw new \RuntimeException('Invalid configuration');
        }

        $this->config = $config;
    }

    /**
     * @throws \Exception
     */
    public function query(string $target, string $sql): array
    {
        if (!isset($this->config['databases'][$target])) {
            $valid = implode(', ', array_keys($this->config['databases']));
            throw new \RuntimeException(sprintf('Unknown database "%s". Available aliases: %s', $target, $valid));
        }

        // Enforce read-only constraint
        if (!preg_match('/^\s*(SELECT|SHOW|DESC|DESCRIBE|EXPLAIN)\b/i', trim($sql))) {
            throw new \RuntimeException('Security Violation: Only SELECT, SHOW, DESC, DESCRIBE, and EXPLAIN queries are permitted.');
        }

        $pdo = $this->getConnection($target);

        return $pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getConnection(string $target): \PDO
    {
        if (isset($this->connections[$target])) {
            return $this->connections[$target];
        }

        $db = $this->config['databases'][$target];
        $dsn = "mysql:host={$db['host']};charset=utf8mb4";

        $pdo = new \PDO($dsn, $db['username'], $db['password'], [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        ]);

        $this->connections[$target] = $pdo;

        return $pdo;
    }
}
