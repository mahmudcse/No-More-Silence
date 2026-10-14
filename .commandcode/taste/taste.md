# Taste

## Workflow

- When given a rough draft or initial idea, wants the agent to deeply reason about the design and propose improvements — including better alternatives to the user's own tech choices — rather than just implementing what was stated. Confidence: 0.65
- Wants implementation plans written out as detailed documents saved into the same directory as the source draft/idea. Confidence: 0.6

## Tooling

- Prefers a high-capability model (Opus 5.5) configured as the default for new sessions. Confidence: 0.6
- Uses the Laravel ecosystem with Laravel Sail as the dev environment (Postgres, Redis, Meilisearch, Mailpit). Confidence: 0.8
- Standardizes on Pest for testing, Larastan for static analysis, and Pint for code formatting, run as a quality gate before considering work done. Confidence: 0.8
- Writes domain code in a layered style: backed enums with a `label()` method, explicit state machines whose illegal transitions throw, single-responsibility Action classes, and domain events with auto-discovered listeners. Confidence: 0.7
- Enforces workflow/business rules in policy classes (e.g. two-person review rule, self-review guard) rather than in controllers or views. Confidence: 0.7
- Uses Filament with Filament Shield for the admin panel, with roles and permissions seeded via a seeder. Confidence: 0.7
- Supports bilingual data: fields carry both English and Bengali values (e.g. `name_en`/`name_bn`), with the app locale defaulting to Bengali. Confidence: 0.7
- Prefers accurate, sourced reference data (real geocodes/names) over fabricated placeholders for seed data. Confidence: 0.6
