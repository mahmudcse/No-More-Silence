# Database Backup & Disaster Recovery Runbook

This runbook defines backup procedures, encrypted off-site replication, and step-by-step disaster recovery instructions to redeploy **No More Silence** from zero state in under 15 minutes.

---

## 1. Backup Strategy & Architecture

- **Database:** PostgreSQL 17 automated logical dump (`pg_dump`) compressed with gzip.
- **Encryption:** AES-256 GCM encryption via OpenSSL prior to transmission.
- **Target Storage:** Off-site, geographically isolated object storage outside Bangladesh jurisdiction (e.g. Backblaze B2 / Hetzner Storage Box / AWS S3 in Frankfurt or Stockholm).
- **Retention Schedule:**
  - Hourly snapshots: Retained for 48 hours
  - Daily snapshots: Retained for 30 days
  - Weekly snapshots: Retained for 12 weeks
  - Monthly archives: Retained indefinitely

---

## 2. Automated Backup Execution

### Standalone Backup Script (`scripts/backup-db.sh`)
```bash
#!/usr/bin/env bash
set -euo pipefail

TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="/var/backups/no-more-silence"
DUMP_FILE="${BACKUP_DIR}/nms_db_${TIMESTAMP}.sql.gz"
ENCRYPTED_FILE="${DUMP_FILE}.enc"

mkdir -p "${BACKUP_DIR}"

# 1. Take compressed pg_dump
docker exec -t nms-postgres-1 pg_dump -U "${DB_USERNAME}" -d "${DB_DATABASE}" | gzip -9 > "${DUMP_FILE}"

# 2. Encrypt with OpenSSL using environment master key
openssl enc -aes-256-cbc -salt -pbkdf2 -in "${DUMP_FILE}" -out "${ENCRYPTED_FILE}" -pass env:BACKUP_ENCRYPTION_KEY

# 3. Replicate to remote S3 bucket
aws s3 cp "${ENCRYPTED_FILE}" "s3://${BACKUP_S3_BUCKET}/db/${TIMESTAMP}/" --endpoint-url "${BACKUP_S3_ENDPOINT}"

# 4. Clean up raw plaintext dump
rm -f "${DUMP_FILE}"
```

---

## 3. Disaster Recovery: Redeploy From Scratch Runbook

Target Recovery Time Objective (RTO): < 15 minutes
Target Recovery Point Objective (RPO): < 1 hour

### Step 1: Provision Clean Server
- Ubuntu 24.04 LTS (Hetzner Cloud or equivalent EU VPS).
- Install Docker Engine, Docker Compose, Git, and Nginx.

### Step 2: Clone Repository & Configure Environment
```bash
git clone https://github.com/organization/No-More-Silence.git /var/www/no-more-silence
cd /var/www/no-more-silence

# Copy production environment template
cp .env.example .env
# Populate:
# - APP_KEY
# - DB_DATABASE, DB_USERNAME, DB_PASSWORD
# - R2_ACCESS_KEY_ID, R2_SECRET_ACCESS_KEY
# - MEILISEARCH_KEY
```

### Step 3: Decrypt and Restore Database
```bash
# 1. Download latest encrypted backup from S3/R2
aws s3 cp "s3://${BACKUP_S3_BUCKET}/db/latest.sql.gz.enc" ./latest.sql.gz.enc --endpoint-url "${BACKUP_S3_ENDPOINT}"

# 2. Decrypt dump
openssl enc -d -aes-256-cbc -pbkdf2 -in latest.sql.gz.enc -out latest.sql.gz -pass env:BACKUP_ENCRYPTION_KEY

# 3. Start PostgreSQL container
docker compose up -d postgres redis meilisearch

# 4. Stream database into Postgres
gunzip -c latest.sql.gz | docker exec -i nms-postgres-1 psql -U "${DB_USERNAME}" -d "${DB_DATABASE}"
```

### Step 4: Run Post-Restore Health & Cache Commands
```bash
# 1. Start application and queue workers
docker compose up -d app horizon

# 2. Run pending migrations (if any)
docker compose exec -T app php artisan migrate --force

# 3. Optimize framework for production
docker compose exec -T app php artisan config:cache
docker compose exec -T app php artisan route:cache
docker compose exec -T app php artisan view:cache
docker compose exec -T app php artisan icons:cache

# 4. Warm statistics cache
docker compose exec -T app php artisan stats:snapshot

# 5. Verify application health check
curl -f http://127.0.0.1/up
```

---

## 4. Verification Drill Procedure
Perform this restoration test once every calendar quarter in a staging container:
1. Verify checksum of latest database snapshot.
2. Spin up isolated SQLite or Postgres container.
3. Import dump and assert `reports`, `facebook_profiles`, and `incidents` record counts match production.
4. Verify decryption key integrity.
