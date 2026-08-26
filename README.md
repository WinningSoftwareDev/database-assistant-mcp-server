# Database Assistant MCP Server

A lightweight PHP-based [Model Context Protocol](https://modelcontextprotocol.io) server that gives AI assistants read-only access to your MySQL databases.

## Features

- Expose multiple MySQL databases to any MCP-compatible client
- Read-only by design — only `SELECT`, `SHOW`, `DESCRIBE`, and `EXPLAIN` queries are permitted
- Simple YAML configuration
- Zero framework dependencies

## Requirements

- PHP 8.4+
- `ext-pdo` and `ext-pdo_mysql`
- `ext-yaml` (for config parsing)

## Installation

```bash
composer global require winningsoftware/database-assistant-mcp-server
```

## Configuration

Create a YAML config file anywhere on your system. The server accepts the path to this file as its only argument.

### Structure

```yaml
databases:
  my_app:
    host: '127.0.0.1'
    username: 'root'
    password: 'secret'
    port: 3306
    name: 'my_app_db'
  analytics:
    host: '192.168.1.50'
    username: 'readonly'
    password: 'readonly_pass'
    port: 3306
```

Each key under `databases` becomes a **target alias** that the AI client uses when issuing queries.

| Field      | Type   | Required | Default | Description                    |
|------------|--------|----------|---------|--------------------------------|
| `host`     | string | yes      | —       | MySQL hostname or IP           |
| `username` | string | yes      | —       | Database user                  |
| `password` | string | yes      | —       | Database password              |
| `port`     | int    | no       | 3306    | MySQL port                     |
| `name`     | string | no       | —       | Default database/schema name   |

### Example

```yaml
databases:
  shop:
    host: 'localhost'
    username: 'shop_reader'
    password: 'r3adOnly!'
    port: 3306
```

## Usage

Your MCP client starts and manages the server process automatically — you don't need to run it manually. Just add it to 
your client config (e.g. `mcp.json`):

```json
{
  "mcpServers": {
    "database-assistant": {
      "command": "php",
      "args": [
        "/path/to/database-assistant-mcp-server/bin/database-assistant-mcp-server",
        "/path/to/your/config.yaml"
      ]
    }
  }
}
```

If installed globally via Composer, the binary path will typically be `~/.composer/vendor/bin/database-assistant-mcp-server` 
or `~/.config/composer/vendor/bin/database-assistant-mcp-server`.

## AI Skill File

A `SKILL.md` file is included in the project root. Copy it into your AI client's skills directory to give the assistant 
context on how to use this server effectively (schema discovery workflow, output formatting, etc.).

## Security Considerations

- The server enforces read-only access at the application level. Only `SELECT`, `SHOW`, `DESCRIBE`, and `EXPLAIN` 
statements are executed.
- For defence in depth, connect with a MySQL user that has read-only privileges.
- Keep your config YAML outside of version control — it contains credentials.

## License

GPL-3.0-or-later
