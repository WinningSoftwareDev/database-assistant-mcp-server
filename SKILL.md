---
name: database-assistant
description: Instructions for querying MySQL databases via the MCP SQL server.
---

# Database Assistant Guidelines

Use this skill whenever the user asks for data, metrics, logs, sales, or user records from local database applications.

## 1. Target Alias Mapping

Map user intent to the database aliases defined in your config YAML (the file passed as an argument to the MCP server). Review the config to identify available aliases and their purpose.

## 2. Execution Workflow

1. **Schema Discovery (MANDATORY FIRST STEP):** ALWAYS run `SHOW TABLES` first to discover the actual table names before writing any data query. NEVER guess or assume table names. After identifying the relevant table, run `DESCRIBE <table_name>` to confirm column names before querying. Skip this step ONLY if you have already discovered the schema in this same session.
2. **Read-Only Queries:** Only generate read-only `SELECT`, `SHOW`, `DESCRIBE`, and `EXPLAIN` statements. The server enforces this constraint and will reject anything else.
3. **Optimized Requests:** Limit raw result sets (`LIMIT 50`) unless explicitly asked for full exports or explicit `COUNT()` aggregates.

## 3. Output Formatting

* Present tabular database results using clean Markdown tables.
* If a query returns zero results, summarize the query parameters used so the user can refine their request.
