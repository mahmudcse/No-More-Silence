# CLAUDE.md - Project Rules for No More Silence

## Project Overview

No More Silence is a public, moderated registry of verified abusive social-media comments in Bangladesh. The stack is **Laravel + Filament + Vue 3 (Inertia SSR) + PostgreSQL + Redis**. The public API is versioned under `/api/v1` and consumed by the web frontend and a future PWA/Android client.

Refer to `docs/idea/idea.txt` and `docs/idea/implementation-plan.md` for the full product design and phased implementation plan.

---

## Local Development

- Use **Laravel Sail** for local development. All services (PostgreSQL, Redis, Meilisearch) run inside Docker via `./vendor/bin/sail up`.
- **Local port is `8084`** (`APP_PORT=8084` in `.env`). Do not change this; other local projects occupy other ports.
- Run tests with `./vendor/bin/sail test`.
- Run Pint with `./vendor/bin/sail pint`.
- Run Larastan with `./vendor/bin/sail php ./vendor/bin/phpstan analyse`.

---

## General Code Rules

### Writing Style in Comments and Documentation

- **Never use m-dashes (`—`) in comments, docblocks, commit messages, or documentation.** Use commas, semicolons, parentheses, or rewrite the sentence instead.
- Keep comments concise and purposeful. Do not state the obvious. A comment should explain *why*, not *what*.
- Use standard ASCII characters only in code comments. No smart quotes, no en-dashes, no em-dashes.

### Code Quality Standards

Write code as a **senior engineer** would:

- Favor clarity over cleverness. If a junior developer cannot understand the code in one read, simplify it.
- Use descriptive, intention-revealing names for variables, methods, classes, and database columns.
- Keep methods short and focused. A method should do one thing well.
- Avoid deep nesting. Use early returns, guard clauses, and `match` expressions.
- Prefer explicit over implicit. Avoid magic strings and numbers; use constants, enums, or config values.
- No dead code, no commented-out code, no TODO comments without a linked issue.
- Handle errors explicitly. Do not silently swallow exceptions.

### SOLID Principles

All code must follow SOLID:

1. **Single Responsibility** - Each class has one reason to change. Action classes hold business logic. Controllers are thin. Filament resources handle only admin UI concerns.
2. **Open/Closed** - Extend behavior through new classes (new actions, new listeners), not by modifying existing ones. Use domain events (`ReportVerified`, `ReportPublished`, etc.) and listeners.
3. **Liskov Substitution** - Subtypes must be substitutable for their base types. Do not override parent behavior in ways that break callers.
4. **Interface Segregation** - Do not force classes to implement methods they do not use. Keep interfaces (and contracts) small and focused.
5. **Dependency Inversion** - Depend on abstractions, not concretions. Type-hint interfaces in constructors and method signatures. Use Laravel's service container for binding.

---

## Architecture Rules

### Directory Structure and Patterns

- **Action classes** (`app/Actions/`) hold all business logic. Example: `SubmitReport`, `VerifyReport`, `PublishReport`, `MergeProfiles`, `RedactEvidence`.
- **Controllers** (both Inertia and API) are thin. They validate input, call an action, and return a response. No business logic in controllers.
- **Filament resources** handle admin panel concerns only. They call the same action classes that the API and web controllers use.
- **Enums** (`app/Enums/`) for all finite sets: category, workflow status, visibility, platform. Use PHP backed enums with translated labels.
- **Domain events** (`app/Events/`) for state transitions. Listeners handle side effects (cache invalidation, stats refresh, notifications).
- **Policies** for every model. Policies enforce both permission checks and workflow guards (e.g., two-person rule).
- **Data Transfer Objects** or Form Requests for passing validated data into actions. Do not pass raw request arrays into business logic.

### Database and Models

- Always use migrations. Never modify the database manually.
- Use foreign key constraints and database-level checks where appropriate.
- Prefer `uuid` for public-facing identifiers. Use auto-incrementing `id` internally.
- Keep models lean. No business logic in models beyond accessors, mutators, scopes, and relationship definitions.
- Denormalized counters (e.g., `public_reports_count`) must be updated through events or observer patterns, not inline.

### API Design

- All public API endpoints return data through Laravel API Resources. Never return raw models.
- Public endpoints must never leak hidden/unverified reports or reporter identity.
- Rate limit all public endpoints. Stricter limits on search.
- Use cursor pagination for feed endpoints.

---

## Testing Rules

### Philosophy

- **Do not write unnecessary test cases.** Every test must verify a meaningful behavior, a critical edge case, or a regression scenario.
- Do not test framework internals. Do not test that Laravel's `validate()` works. Test your validation rules, your business logic, your policies.
- Do not test getters, setters, or trivial accessors unless they contain transformation logic.
- Do not write tests that just assert a method was called without verifying the outcome.

### What to Test

- **Unit tests** for pure logic: `FacebookUrlNormalizer` (exhaustive URL shapes), state machine transitions, name normalization, enum behavior.
- **Feature tests** for HTTP endpoints: assert correct responses, status codes, and data shapes. Assert that unauthorized access is blocked. Assert that hidden/unverified data never leaks.
- **Policy tests** for authorization: two-person rule, self-review guard, permission gating per role.
- **Filament/Livewire tests** for admin actions: review queue workflows, permission-gated UI elements.

### Test Code Quality

- Tests must be readable. Use descriptive test names that explain the scenario and expected outcome (e.g., `it_rejects_publication_by_the_same_admin_who_verified`).
- Use Pest syntax. Prefer `it()` and `test()` over class-based tests.
- Use factories and seeders for test data. Do not hardcode IDs or rely on database state from other tests.
- Each test must be independent. No shared mutable state between tests.

---

## Laravel-Specific Conventions

- Use **Pint** for code formatting. Run it before every commit.
- Use **Larastan** at level 6 or higher. Fix all reported issues.
- Use strict types: `declare(strict_types=1);` in every PHP file.
- Use PHP 8.2+ features: readonly properties, enums, named arguments, match expressions, first-class callables, fibers where appropriate.
- Use `config()` and `.env` for environment-specific values. Never hardcode credentials, URLs, or environment-dependent values.
- Queue heavy operations (image processing, stats refresh, PDF generation). Do not block HTTP requests.

## Vue / TypeScript Conventions

- Use **TypeScript** for all frontend code. No `any` types unless absolutely unavoidable (and document why).
- Use Vue 3 Composition API with `<script setup>` syntax.
- Components should be small and focused. Extract reusable logic into composables (`composables/`).
- Use **Tailwind CSS** for styling. No inline styles. No custom CSS unless Tailwind cannot express the design.
- Internationalization: all user-facing strings must go through the i18n system. Bangla (`bn`) is the default locale.

---

## Git and Workflow

- Write clear, imperative commit messages (e.g., "Add report submission rate limiting", not "Added rate limiting" or "rate limiting stuff").
- One logical change per commit. Do not mix refactors with feature work.
- Branch naming: `feature/`, `fix/`, `refactor/`, `chore/` prefixes.

---

## Security Reminders

- Strip EXIF from all uploaded images before storage.
- Hash and never store raw IPs long-term.
- Encrypt sensitive fields (reporter email, appeal contact) at rest using Laravel's encryption.
- Never expose reporter identity through any public endpoint or response.
- MFA is mandatory for all admin accounts.
- Log every admin action, especially "viewed reporter identity" events.
