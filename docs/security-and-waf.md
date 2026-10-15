# Security, Cloudflare WAF & Admin Protection Architecture

This document outlines the defense-in-depth security architecture designed to protect **No More Silence** against DDoS, coordinated trolling, brute-force attempts, and unauthorized administrative access.

---

## 1. Network Boundary: Cloudflare WAF Configuration

All external web and API traffic must route through Cloudflare Edge (Enterprise or Pro Plan recommended).

### 1.1 Custom WAF Rulesets
1. **Block Malicious User Agents & Scrapers:**
   - Filter: `(cf.client.bot) or (http.user_agent contains "sqlmap") or (http.user_agent contains "nikto")`
   - Action: `Block`

2. **Search & Submission Rate Limiting:**
   - Public Search (`/api/v1/search` and `/search`):
     - Threshold: 30 requests per 1 minute per IP
     - Action: Managed Challenge (Cloudflare Turnstile)
   - Report Submission (`/api/v1/submissions`):
     - Threshold: 10 requests per 10 minutes per IP
     - Action: Managed Challenge / Block

3. **Geographic & Attack Mitigation:**
   - Enable Cloudflare "Under Attack" Mode toggle via API if coordinated DDoS or bot flood occurs.
   - Cloudflare Bot Fight Mode enabled to challenge automated headless browsers.

---

## 2. Admin Panel & Internal Tool Isolation

The Filament administrative panel (`/admin`) and Laravel Horizon (`/horizon`) contain sensitive verification queues and audit logs. They must NEVER be directly exposed to the open internet.

### 2.1 Cloudflare Access (Zero Trust)
Wrap `/admin*` and `/horizon*` behind **Cloudflare Zero Trust Access Application**:
1. Policy: Allowed staff email domains only.
2. Authentication: Hardware security key (FIDO2 / WebAuthn) or OTP to pre-approved moderator emails.
3. IP Access Restriction: If feasible, restrict to VPN / static egress IP.

### 2.2 Application-Level Defense
1. **Mandatory MFA:** Filament built-in TOTP MFA enforced for all User accounts.
2. **Two-Person Rule:** `ReportPolicy` strictly prevents a moderator from publishing a report they verified or reviewing a report they submitted.
3. **Audit Logging:** Every view of reporter identity, profile merge, or status change is irreversibly logged via `spatie/laravel-activitylog`.

---

## 3. Sentry Error Tracking & Real-Time Alerting

Integrated via `sentry/sentry-laravel`:
- Captures unhandled backend exceptions, database connection drops, and queue job failures in Laravel Horizon.
- PII Sanitization: Sentry config sanitizes Authorization headers, session IDs, and submitter IP addresses before transmitting telemetry to Sentry servers.
- Alert Channels: Critical alerts route to Telegram bot and encrypted moderator Slack/Discord channel.
