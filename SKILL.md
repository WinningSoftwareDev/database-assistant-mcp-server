---
name: database-assistant
description: Instructions for querying MySQL databases via the MCP SQL server.
---

# Database Assistant Guidelines

Use this skill whenever the user asks for data, metrics, logs, sales, or user records from local database applications.

## 1. Target Alias Mapping

Map user intent to the database aliases defined in your config YAML (the file passed as an argument to the MCP server). Review the config to identify available aliases and their purpose.

## 2. Execution Workflow

1. **Schema Discovery (MANDATORY FIRST STEP):** Connections may not specify a default database, so ALWAYS start discovery with `SHOW DATABASES`, then `SHOW TABLES FROM <schema>` to find tables within the relevant schema. NEVER guess or assume table or schema names. After identifying the relevant table, run `DESCRIBE <schema>.<table_name>` to confirm column names before querying. When querying, always fully qualify table names (e.g. `SELECT * FROM my_schema.my_table`). Skip this step ONLY if you have already discovered the schema in this same session.
2. **Read-Only Queries:** Only generate read-only `SELECT`, `SHOW`, `DESCRIBE`, and `EXPLAIN` statements. The server enforces this constraint and will reject anything else.
3. **Optimized Requests:** Limit raw result sets (`LIMIT 50`) unless explicitly asked for full exports or explicit `COUNT()` aggregates.

## 3. Output Formatting

* Present tabular database results using clean Markdown tables.
* If a query returns zero results, summarize the query parameters used so the user can refine their request.
