# Infrastructure, Domain & Governance Decisions (Draft)

> **Status:** Operational Decision Record · Phase 0 Foundations

---

## 1. Domain Registration & Privacy

- **Registrar:** Registered with a privacy-focused international registrar located outside Bangladeshi jurisdiction (e.g., Njalla, Porkbun, or Gandi with full WHOIS privacy proxy).
- **DNS & Proxy:** Proxied behind **Cloudflare Enterprise/Pro** with DDoS protection, WAF managed rules, and SSL/TLS Full (Strict).
- **Origin Concealment:** Direct server IP address is firewalled to accept incoming HTTP/HTTPS traffic strictly from Cloudflare IP ranges.

---

## 2. Server Hosting & Storage

- **Hosting Provider:** Virtual Private Server (VPS) hosted in the European Union (e.g. Hetzner Cloud Falkenstein/Helsinki) to ensure GDPR-aligned data protection, stable power/connectivity, and immunity from local administrative pressure.
- **Object Storage (R2):** Cloudflare R2 object storage configured with dual buckets:
  - `nomoresilence-evidence-private`: Private bucket holding unredacted raw evidence, accessible only via server-signed credentials.
  - `nomoresilence-evidence-public`: Publicly accessible CDN-backed bucket holding WebP rendered, redacted images.
- **Cache & Queues:** In-memory Redis instance with Laravel Horizon queue manager for background image processing, EXIF stripping, perceptual hashing, and periodic statistics caching.

---

## 3. Disaster Recovery & Backups

- **Automated Encrypted Backups:** Scheduled via `spatie/laravel-backup` to run daily database dumps and configuration archives.
- **Offsite Storage:** Encrypted backups are transferred to a secondary, geographically isolated storage provider (e.g., Backblaze B2 in a different region).
- **Runbook:** "Deploy from Scratch" automated script allowing complete rebuild of the application and database restore in under 2 hours.

---

## 4. Governance & Organizational Stewardship

- **Launch Stewardship:** Operates as a pseudonymous civil-society collective ("No More Silence Team") to safeguard individual operators from harassment and extra-judicial retaliation.
- **NGO Partnership Transition:** Actively seek institutional partnership with an established Bangladeshi human-rights or legal-aid organization (e.g., BLAST, ASK, Bangladesh Mahila Parishad) to transition governance, provide direct legal defense support, and establish long-term institutional stability.
