# No More Silence

A public, moderated registry of **verified** abusive social-media comments in Bangladesh. Built to document, verify, and hold accountable those who post rape apologia, victim-blaming, sexual threats, and other forms of online harassment.

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 13 (PHP 8.5) |
| Admin Panel | Filament 3 + Shield |
| Frontend | Vue 3 + Inertia (SSR) + TypeScript + Tailwind CSS |
| Database | PostgreSQL 17 |
| Cache/Queue | Redis |
| Search | Meilisearch (later phases) |
| Local Dev | Laravel Sail (Docker) |
| Testing | Pest + Larastan |

## Quick Start

### Prerequisites
- Docker & Docker Compose
- Git

### Setup

```bash
# Clone the repository
git clone git@github.com:mahmudcse/No-More-Silence.git
cd No-More-Silence

# Copy environment file
cp .env.example .env

# Install dependencies (host PHP doesn't need extensions; Sail handles it)
composer install --ignore-platform-reqs

# Build and start containers
./vendor/bin/sail up -d

# Generate app key
./vendor/bin/sail artisan key:generate

# Run migrations
./vendor/bin/sail artisan migrate

# Seed the database (divisions + 64 districts)
./vendor/bin/sail artisan db:seed

# Install frontend dependencies and build
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
```

The app will be available at **http://localhost:8084**

### Daily Development

```bash
# Start containers
./vendor/bin/sail up -d

# Stop containers
./vendor/bin/sail down

# Run tests
./vendor/bin/sail test

# Run Pint (code style)
./vendor/bin/sail pint

# Run Larastan (static analysis)
./vendor/bin/sail php ./vendor/bin/phpstan analyse

# Access the database
./vendor/bin/sail psql

# View mail (Mailpit)
# http://localhost:8025
```

### Ports

| Service | Port |
|---|---|
| App | 8084 |
| PostgreSQL | 5432 |
| Redis | 6379 |
| Meilisearch | 7700 |
| Mailpit SMTP | 1025 |
| Mailpit Dashboard | 8025 |
| Vite Dev | 5173 |

## Project Structure

See [docs/idea/implementation-plan.md](docs/idea/implementation-plan.md) for the full product design and phased implementation plan.

## License

Proprietary. All rights reserved.
