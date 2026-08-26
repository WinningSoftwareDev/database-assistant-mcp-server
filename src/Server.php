<?php

declare(strict_types=1);

namespace App;

class Server
{
    private DatabaseService $dbService;

    public function __construct(DatabaseService $dbService)
    {
        $this->dbService = $dbService;
    }

    /**
     * @throws \JsonException
     */
    public function listen(): void
    {
        while ($line = fgets(STDIN)) {
            $request = json_decode($line, true, 512, JSON_THROW_ON_ERROR);

            if (!is_array($request)) {
                continue;
            }

            $id = $request['id'] ?? null;
            $method = $request['method'] ?? '';

            switch ($method) {
                case 'initialize':
                    $this->respond($id, [
                        'protocolVersion' => '2024-11-05',
                        'capabilities' => ['tools' => (object) []],
                        'serverInfo' => [
                            'name' => 'winningsoftware/database-assistant-mcp-server',
                            'version' => '0.1.0',
                        ],
                    ]);

                    break;

                case 'tools/list':
                    $this->respond($id, ['tools' => $this->getToolsSchema()]);

                    break;

                case 'tools/call':
                    /**
                     * @var array{name: string, arguments: array<string, mixed>} $params
                     */
                    $params = is_array($request['params']) ? $request['params'] : [];
                    $this->handleToolCall($id, $params);

                    break;

                case 'notifications/initialized':
                    break;
            }
        }
    }

    /**
     * @return array<int, array{
     *     name: string,
     *     description: string,
     *     inputSchema: array{
     *         type: string,
     *         properties: array<string, array<string, string>>,
     *         required: string[],
     *     },
     * }>
     */
    private function getToolsSchema(): array
    {
        return [
            [
                'name' => 'query_database',
                'description' => 'Executes a read-only SELECT query against a target database alias.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'target' => [
                            'type' => 'string',
                            'description' => 'Target DB alias name (e.g. app_one, app_two)',
                        ],
                        'sql' => [
                            'type' => 'string',
                            'description' => 'The SELECT query to execute',
                        ],
                    ],
                    'required' => ['target', 'sql'],
                ],
            ],
        ];
    }

    /**
     * @param array{name: string, arguments: array<string, mixed>} $params
     *
     * @throws \JsonException
     */
    private function handleToolCall(mixed $id, array $params): void
    {
        $toolName = $params['name'];
        $args = $params['arguments'];

        if ($toolName === 'query_database') {
            try {
                $target = is_string($args['target']) ? $args['target'] : '';
                $sql = is_string($args['sql']) ? $args['sql'] : '';
                $results = $this->dbService->query($target, $sql);

                $this->respond($id, [
                    'content' => [[
                        'type' => 'text',
                        'text' => json_encode($results, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT),
                    ]],
                ]);
            } catch (\Exception $e) {
                $this->respond($id, [
                    'content' => [[
                        'type' => 'text',
                        'text' => 'Error: ' . $e->getMessage(),
                    ]],
                    'isError' => true,
                ]);
            }
        }
    }

    /**
     * @param array<int|string, mixed> $result
     *
     * @throws \JsonException
     */
    private function respond(mixed $id, array $result): void
    {
        if ($id === null) {
            return;
        }

        echo json_encode([
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => $result,
        ], JSON_THROW_ON_ERROR) . "\n";

        flush();
    }
}
