# AGENTS.md

Guidance for AI coding agents and human contributors. This is the single source of truth; `CLAUDE.md` imports it.

## Project

table-banking is a record-keeping ledger for table banking groups (chamas): members, contributions, loans with interest, repayments, penalties and year-end dividends. It is NOT a bank or wallet. Real money stays in the group's own accounts (bank, M-Pesa, cash). The application records and calculates only.

## Stack

PHP 8.3, Laravel, Composer, Blade with Tailwind, Laravel Breeze (auth), MySQL, Pest (tests), Laravel Pint (formatting), spatie/laravel-permission in teams mode (team key: `group_id`).

Do not add dependencies, change the PHP version, or run `laravel/boost` installers without explicit approval.

## Commands

- Tests: `./vendor/bin/pest`
- Format check: `./vendor/bin/pint --test` (fix with `./vendor/bin/pint`)
- Tests run against the `table_banking_test` database, configured in `phpunit.xml`. Never point tests at the development database. Never print or commit credentials; `.env` is not committed.

## Non-negotiable rules

### Money
- Store money as integer minor units (cents). Store rates as integer basis points (1000 = 10.00%). Never use floats for money or rates.
- Ledger amounts are always positive integers; the `direction` (in/out) carries the sign.
- Interest rounds half up to the nearest minor unit. Principal is split equally across installments, with any remainder in the last.

### Ledger
- The ledger is append-only. Never update or delete a `ledger_entries` row. Correct mistakes with a reversing entry that references the original (`reverses_entry_id`).
- Wrap every ledger write in a database transaction.
- Each loan stores a snapshot of the interest method and rate at creation.

### Multi-group isolation
- Every tenant table carries `group_id` and uses the `BelongsToGroup` trait with `GroupScope`. The scope fails closed: with no current group, queries return nothing.
- Cross-group reads require an explicit `withoutGlobalScope(GroupScope::class)` and a stated reason.
- `group_id` is never in `$fillable`.

### Authorisation
- Authorise on permissions assigned to roles, never on role names in application code.
- Roles: admin, treasurer, secretary, member (`GroupRole`), provisioned per group.

### Data protection
- No national ID numbers in version 1 (Kenya Data Protection Act). Demo and seed data is synthetic only.
- Foreign keys restrict deletes on financial data. Members have a status (active, exited) and are never deleted.

### Migrations
- Never edit a merged migration. Add a new one. Do not regenerate `database/schema/mysql-schema.sql` as part of a feature branch.

## Code conventions

- Money logic lives in Action or Service classes (`app/Actions`, `app/Services`). Controllers stay thin and use Form Requests and Policies.
- `declare(strict_types=1);` in new classes; `final readonly` for value objects; explicit `$fillable`.
- Test first with Pest: write the failing test, then the code, then run the full suite.

## Workflow

- One feature branch per change (`feat/...`, `chore/...`), small commits, a pull request even when solo.
- Run Pint and the full suite before committing. Do not commit with failing tests.
