<?php

declare(strict_types=1);

namespace App;

/**
 * @phpstan-type ToolConfig array{
 *     databases: array<string, array{
 *         host: string,
 *         username: string,
 *         password: string,
 *         port?: int | numeric-string,
 *         name?: string,
 *     }>,
 * }
 */
class DatabaseService
{
    /**
     * @var array<string, \PDO>
     */
    private array $connections = [];

    /**
     * @param ToolConfig $config
     */
    public function __construct(private readonly array $config)
    {
        if (!ConfigValidator::validate($config)) {
            throw new \RuntimeException('Invalid configuration');
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     *
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
        $query = $pdo->query($sql);

        if (!$query) {
            throw new \RuntimeException('Query failed');
        }

        /**
         * @var array<int, array<string, mixed>> $result
         */
        $result = $query->fetchAll(\PDO::FETCH_ASSOC);

        return $result;
    }

    private function getConnection(string $target): \PDO
    {
        if (isset($this->connections[$target])) {
            return $this->connections[$target];
        }

        $db = $this->config['databases'][$target];
        $port = $db['port'] ?? 3306;
        $dsn = "mysql:host={$db['host']};port={$port};charset=utf8mb4";

        if (!empty($db['name'])) {
            $dsn .= ";dbname={$db['name']}";
        }

        $pdo = new \PDO($dsn, $db['username'], $db['password'], [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        ]);

        $this->connections[$target] = $pdo;

        return $pdo;
    }
}
