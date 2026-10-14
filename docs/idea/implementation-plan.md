# No More Silence — Product Design & Implementation Plan

> Status: Draft v1 · 2026-10-14
> Source idea: `docs/idea/idea.txt`

---

## 0. TL;DR

A public, moderated registry of **verified** abusive social-media comments (rape apologia, victim-blaming, sexual threats) made by identifiable Facebook profiles in Bangladesh, with:

- a public website (Bangla-first) — stats, district map, search by profile/name, recent verified records;
- an evidence submission flow (web + Android);
- a Filament admin panel with custom roles, a multi-step verification workflow, redaction tools, appeals and a full audit trail;
- a versioned JSON API used by the web's interactive parts and the Android app.

**Main changes from the original idea** (reasons in the sections below):

1. **The core product is the verification pipeline, not the public page.** Wrongly naming one innocent person (fake screenshot, same-name confusion, revenge report) can end the project legally and reputationally. Every public record must pass through evidence checks, redaction and two-person approval.
2. **Protect three groups by design:** the **victim** (never stored, never shown), the **reporters** (anonymous, EXIF stripped, minimal logs) and the **moderators** (pseudonymous, MFA, hosted outside BD jurisdiction pressure).
3. **The Facebook profile is the identity, not the name.** Search by URL/ID is the primary path. Name search exists, but results always show the profile link, photo snapshot and ID so same-name people are not confused.
4. **Android: start with an installable PWA shipped to Play Store as a Trusted Web Activity (TWA)**, including the Android "Share → No More Silence" target, so users can share a screenshot straight from the Facebook app. Build a native app only if the PWA proves insufficient. The API is still built in v1 and consumed by the web itself, so a native app later is cheap.
5. **Add an appeals/right-of-reply process and an "evidence package" export** (PDF for police cyber units / Facebook reporting). This turns the site from "name and shame" into "document and act", which is more defensible legally and more useful.
6. **"Publish to homepage" becomes a visibility model:** `hidden` → `public` (searchable, on profile page) → `featured` (homepage).

---

## 1. Risks that shape the design (read first)

| Risk | Impact | Mitigation built into the plan |
|---|---|---|
| **Defamation / false accusation** (fabricated screenshot, edited text, wrong profile) | Lawsuits, police cases against operators, loss of credibility | Permalink verification, two-person rule, evidence hashing + archive, neutral wording ("recorded comment"), appeals, quick takedown SLA |
| **Weaponisation** (people reporting enemies) | Innocent people harmed | Only verified records are public; reporter rate limits + reputation; duplicate/perceptual-hash detection; moderators see reporter history |
| **Victim re-identification** | Re-traumatisation; likely illegal (Bangladesh law restricts publishing identity of sexual-violence victims — confirm with lawyer) | No victim fields in the schema at all; mandatory redaction step before publish; incidents titled generically |
| **Reporter retaliation** | Physical danger | No public reporter identity; optional accounts; EXIF stripped; IP not stored long-term (hashed, short retention) |
| **Moderator/operator safety & legal pressure** | Arrest/threat | Pseudonymous admin display names; no public staff list; MFA mandatory; hosting + registrar outside BD; Cloudflare in front; consult a Bangladeshi lawyer **before launch** (cyber law changed several times: DSA 2018 → CSA 2023 → Cyber Security Ordinance 2025 — verify current status) |
| **Site blocking / takedown by hosting** | Downtime | Cloudflare, provider that tolerates controversial-but-legal content, offsite encrypted backups, ability to redeploy in hours (infra as code / Forge script) |
| **Play Store rejection or mass-reporting** | No Android distribution | PWA works without Play Store; comply with Google UGC policy (in-app report button, moderation, ToS) |
| **Scraping / harassment of listed people** | Mob violence against accused | Rate-limited search & API, no bulk export, no personal data beyond what was publicly posted (no phone, address, workplace, family) |

> **Rule of thumb for every feature:** *Would we be comfortable defending this exact public record in court, and if it turns out to be wrong, can we remove it within an hour?*

---

## 2. Users & roles

### 2.1 Public roles
| Role | Can do |
|---|---|
| **Visitor** | View stats, map, recent featured records, search, view profile pages, view incident pages |
| **Reporter (anonymous)** | Submit a report with Turnstile captcha; gets a secret tracking code to check status |
| **Reporter (account)** | Same + submission history, reputation, faster review queue. Email login (magic link) — **no phone number** (deanonymising) |
| **Profile owner (accused)** | Submit an appeal / right-of-reply via a public form; must prove ownership (post a code on their profile, or message from the profile) |

### 2.2 Admin roles (Filament)
Roles are **fully dynamic** (create role, tick permissions, assign to admins) using `spatie/laravel-permission` + **Filament Shield**. Ship these seeded defaults:

| Default role | Typical permissions |
|---|---|
| **Super Admin** | Everything, incl. roles, admins, settings, hard delete |
| **Triage** | View new submissions, mark spam/duplicate, request more info, merge duplicates |
| **Verifier** | Check permalink, link to profile/incident, mark *verified* / *rejected* |
| **Redactor** | Use the redaction tool on evidence |
| **Publisher** | Change visibility (`hidden`/`public`/`featured`) of verified records |
| **Appeals Officer** | Handle appeals, unpublish, add right-of-reply |
| **Analyst (read-only)** | Dashboards, stats, no PII of reporters |

**Workflow guards (enforced in code, not only by permissions):**
- A report cannot be **published by the same admin who verified it** (two-person rule). Configurable in settings so a small team can relax it temporarily, but it's logged.
- An admin can never review a report they submitted themselves.
- Reporter identity (email, IP hash) is visible only to a `view_reporter_identity` permission.

---

## 3. Domain model

```
District ─┐
          ├─< Incident >──< Report >── FacebookProfile ─< ProfileAlias
Division ─┘                   │  │                    └─< ProfileSnapshot
                              │  └─< Evidence (screenshots, redacted variants)
                              ├─< ReportReview (each workflow step)
                              └── Reporter (nullable)
Appeal >── FacebookProfile / Report
ActivityLog (every admin action)
StatsSnapshot (cached counters per district/day)
```

### 3.1 Tables (key columns)

**divisions** — `id, code, name_en, name_bn`
**districts** — `id, division_id, code (BBS geocode), name_en, name_bn, slug, population (optional, for per-capita map)` · seeded with all 64.

**incidents** — the news event the comments were made under.
`id, title_en, title_bn (generic, e.g. "Rape case reported — Noakhali, Sept 2026"), district_id (where the incident happened), occurred_on, summary (no victim identity), source_urls (json: news links, FB post link), status (draft/public), created_by`

**facebook_profiles** — the identity being recorded.
`id, fb_numeric_id (nullable, unique), canonical_url (unique), current_display_name, display_name_normalized (for search), district_id (nullable — only if self-declared publicly on profile), district_source (enum: self_declared/unknown), profile_status (active/deactivated/renamed/unknown), last_checked_at, public_reports_count (denormalized), first_recorded_at, last_recorded_at`

**profile_aliases** — vanity URLs & display names seen over time (people rename to escape).
`id, facebook_profile_id, type (url/name), value, value_normalized, seen_at`

**profile_snapshots** — archived name + profile picture at time of verification (moderator captured).
`id, facebook_profile_id, display_name, avatar_media_id, captured_at, captured_by`

**reports** — one abusive comment/post.
`id, uuid, tracking_code_hash, reporter_id (nullable), facebook_profile_id (nullable until linked), incident_id (nullable), submitted_profile_url, submitted_comment_url, comment_text (verbatim transcription), comment_text_bn_en (optional translation), comment_posted_at, category (enum), severity (1–3), workflow_status (enum), visibility (enum), submitted_via (web/pwa/android/api/admin), duplicate_of_id, rejected_reason, verified_at, verified_by, published_at, published_by, submitter_ip_hash, created_at …`

`category`: `rape_apologia`, `victim_blaming`, `sexual_threat`, `sexual_harassment`, `incitement`, `doxxing`, `other`.

`workflow_status` (state machine):
```
submitted → triaged → in_verification → verified
     │          │            │      └→ needs_info → in_verification
     └→ spam    └→ duplicate └→ rejected
verified → retracted (after appeal / error)
```
`visibility`: `hidden` (default) → `public` → `featured`. Only `verified` reports may be non-hidden (DB check + policy).

**evidence** (via `spatie/laravel-medialibrary` on Report) — collections:
- `original` (private disk, never public; EXIF stripped on upload; `sha256`, `phash` stored in custom properties)
- `redacted` (public derivative, generated by redaction tool, WebP)
- `archive` (optional: saved HTML/PDF of the comment page captured by moderator)

**report_reviews** — every step: `report_id, admin_id, from_status, to_status, checklist (json), note, created_at`. The checklist records what the verifier actually checked (see §5).

**reporters** — `id, email (nullable, encrypted), display_handle, reputation, is_banned, created_at`

**appeals** — `id, facebook_profile_id, report_id (nullable), contact (encrypted), ownership_proof, message, status (open/accepted/rejected/partially), outcome_note, right_of_reply_text (shown publicly if accepted), handled_by`

**stats_snapshots** — `date, district_id (nullable = national), received, verified, public, profiles` — refreshed by scheduled job + model events; public pages read only from cache/this table.

**activity_log** — `spatie/laravel-activitylog` for every admin action incl. *viewed reporter identity*.

### 3.2 Facebook URL normalisation (critical for "how many occurrences under this FB id")
Write a `FacebookUrlNormalizer` (well tested) that maps all of these to one canonical key:
- `facebook.com/profile.php?id=1000…`, `m.facebook.com/…`, `mbasic.facebook.com/…`, `web.facebook.com/…`, `fb.com/…`, `facebook.com/people/Name/1000…`, `facebook.com/username?mibextid=…` (strip tracking params), trailing slashes, case.
- Prefer `fb_numeric_id` when known; otherwise `username`. When a moderator later learns the numeric ID, profiles get **merged** (admin action, logged).
- The Graph API cannot look up arbitrary users, so the numeric ID is captured manually by the verifier (documented procedure in the moderator handbook).

---

## 4. Public website

**Bangla-first, English secondary** (`bn` default, toggle to `en`). Use Bangla numerals in `bn` locale. Fast on low-end Android over 3G: SSR, small JS, WebP images, lazy load.

### 4.1 Pages
1. **Home**
   - Headline counters: *Reports received · Verified · Profiles recorded · Districts* (from cache).
   - **District map** (choropleth, see §4.2).
   - **Recent featured records** — card: profile snapshot name + avatar (from snapshot, not hotlinked), profile link, category badge, verbatim comment, redacted screenshot thumbnail, incident link, date, "verified" badge.
   - Search box (prominent).
   - CTA: "Report a comment" + "How we verify" link.
2. **Search** — input accepts a **Facebook URL** (exact match after normalisation → straight to profile) or a **name** (fuzzy, Bangla + English transliteration). Results always show avatar snapshot, name, canonical URL, count of verified records. Show a "same name ≠ same person, always check the profile link" notice.
3. **Profile page** `/p/{slug-or-id}` — all public records for that profile, aliases (previous names/URLs), timeline, right-of-reply if any, "Is this you? Request review" link.
4. **Incident page** `/incidents/{slug}` — generic description, news sources, district, all public records under it. Helps context ("200 recorded comments under one news post").
5. **District page** `/districts/{slug}` — stats + records for that district.
6. **Report submission** (multi-step, works on mobile; see §6).
7. **Track my report** — enter tracking code → status.
8. **How we verify / Methodology** — publish the exact verification standard. This is the project's credibility.
9. **Appeals / Request review** form.
10. **About, Safety resources** (helplines for survivors: e.g. national 109 helpline, 999 — verify current numbers), **Terms, Privacy**.

### 4.2 The map — design decisions
- **What does "district density" mean?** Two different things; the idea mixes them:
  - **Incident district** — where the crime happened (reliable, from news).
  - **Commenter district** — only known if self-declared on the public profile (often missing, sometimes fake).
- **Recommendation:** map toggle with *"Where incidents happened"* (default, reliable) and *"Where commenters say they live"* (with an "unknown: N%" note). Optional per-100k-population normalisation (BBS Census 2022 data) so Dhaka doesn't always win.
- **Tech:** no tile server needed. Use a simplified **TopoJSON of 64 districts** (source: OCHA/HDX Bangladesh admin boundaries ADM2 or geoBoundaries, simplified with mapshaper to ~100–200 KB) rendered as **SVG with `d3-geo` + `topojson-client`** in a Vue component. Lighter and more controllable than Leaflet. Tooltips + tap to open district page. Check the dataset licence and attribute it.
- Data comes from `GET /api/v1/stats/districts` (cached).

### 4.3 Wording rules (legal hygiene)
- Use "**recorded comment**", "**verified record**". Never "rapist", "criminal" or "supporter of rape" as a label on a person.
- Show the verbatim comment + evidence; let the content speak.
- Every record shows "verified on DATE" and a link to the methodology.

---

## 5. Verification standard (the heart of the system)

A report becomes `verified` only if the verifier ticks a checklist (stored in `report_reviews.checklist`):

1. **Permalink exists** — the comment URL opens and the comment is visible **or** the comment is gone but there are ≥2 independent submissions with matching screenshots (pHash match / same text) — marked `verified_from_screenshots`.
2. **Profile match** — the comment's author link resolves to the recorded profile; numeric ID/username captured.
3. **Text match** — the transcribed text matches the comment exactly (verbatim, typos kept).
4. **Context** — the comment was made under the linked incident/post and the meaning is clear in context (not sarcasm against the abuser, not quoting someone to condemn them).
5. **Category** is assigned.
6. **Snapshot** of profile name + avatar captured.
7. **Redaction done** — victim, other commenters, reporter's own name/avatar (screenshots often show "Commenting as …") removed.
8. **Not a minor (as far as visible)** — if the profile appears to belong to a child, do not publish; keep hidden.

Then a **different admin** (Publisher) reviews and sets visibility. Featured requires extra permission.

**Evidence integrity:** store `sha256` of the original upload + upload timestamp; optional moderator-made archive (saved page/PDF). Facebook pages archive poorly in Wayback/archive.today, so don't rely on them, but try.

---

## 6. Submission flow (web, PWA share target, Android)

Steps (one screen each on mobile):
1. **Evidence** — upload 1–5 screenshots (or arrive via Android share sheet with images pre-filled). Client-side compression; server strips EXIF.
2. **Links** — commenter's profile URL (required), comment permalink (strongly encouraged; show a 10-second GIF of "how to copy a comment link" in the FB app), news post link (optional).
3. **Details** — transcribe comment (optional, verifier will do it), category, incident (autocomplete existing incidents or "new incident" free text + district).
4. **Submit** — Turnstile; returns **tracking code** (show once, copyable); optional email for updates.

**Anti-abuse:** rate limit per IP-hash/device (e.g. 10/day), Turnstile, instant duplicate detection (same normalised profile + comment URL → "already reported, thanks" and increment `corroborations`), reporter reputation (verified ratio) prioritises the queue, bans for fabricated evidence.

**Privacy:** store `ip_hash = HMAC(ip, rotating_secret)` only for rate limiting, purge after 30 days. No third-party analytics; use self-hosted Plausible/Umami or none.

---

## 7. Admin panel (Filament)

### 7.1 Resources & pages
- **Dashboard** — queue sizes by status, SLA (age of oldest unreviewed), submissions/day chart, top incidents, per-moderator throughput, open appeals.
- **Review Queue** (custom page, the main daily tool) — side-by-side: evidence viewer (zoom) | submitted links (open in new tab) | form: link/create profile (with normaliser + "possible matches" list), link/create incident, transcription, category, checklist, actions (Request info / Reject / Verify / Mark duplicate). Keyboard shortcuts. Locks a report while someone has it open (avoid double work).
- **Reports** resource — filters (status, visibility, category, district, incident, date, reporter), bulk actions (spam, assign), relation managers (evidence, reviews, activity).
- **Facebook Profiles** resource — aliases, snapshots, linked reports, **merge profiles** action.
- **Incidents** resource.
- **Appeals** resource — workflow + right-of-reply text.
- **Reporters** resource — reputation, ban (identity fields gated).
- **Redaction tool** — custom Filament page: canvas over the screenshot, draw rectangles; server applies **solid black boxes** (not blur — blur can be partially reversed) via Intervention Image and saves to `redacted` collection. Original stays private.
- **Evidence package export** — action on a profile or incident: generates a PDF (redacted screenshots, URLs, timestamps, hashes) to hand to police cyber unit / BTRC / Facebook reporting.
- **Admins, Roles & Permissions** — Filament Shield UI.
- **Activity Log** — searchable, read-only.
- **Settings** — two-person rule toggle, rate limits, homepage featured count, announcement banner (`spatie/laravel-settings` + Filament settings plugin).

### 7.2 Security
- MFA mandatory for all admins (Filament built-in MFA / app TOTP).
- Admin panel on a separate, non-obvious path or subdomain, optionally behind Cloudflare Access.
- Session timeout, login throttling, all actions logged, "view reporter identity" logged.
- Admin display name pseudonyms in all public-facing contexts (e.g. right-of-reply responses signed "Moderation team").

---

## 8. API (v1)

Laravel Sanctum (token auth for apps; SPA cookie auth for web). Versioned under `/api/v1`. Auto-generated OpenAPI docs with **Scramble** (`dedoc/scramble`). JSON:API-ish resources via Laravel API Resources. All public endpoints cached + rate limited.

| Method | Endpoint | Auth | Notes |
|---|---|---|---|
| GET | `/stats` | – | national counters |
| GET | `/stats/districts?mode=incident\|commenter` | – | map data |
| GET | `/reports/featured` | – | homepage feed, cursor pagination |
| GET | `/reports/{uuid}` | – | public record only |
| GET | `/search?q=` | – | URL or name; max 20 results; strict rate limit |
| GET | `/profiles/{id}` | – | profile + public records |
| GET | `/incidents`, `/incidents/{slug}` | – | |
| GET | `/districts`, `/districts/{slug}` | – | |
| POST | `/submissions` | optional | multipart (≤5 images) → returns tracking code |
| GET | `/submissions/track/{code}` | – | status only |
| POST | `/appeals` | – | |
| POST | `/auth/magic-link`, `/auth/verify` | – | reporter accounts |
| GET | `/me/submissions` | token | |

The web's interactive Vue components (search autocomplete, map, submission wizard) call these endpoints, so the API is **dogfooded from day one**.

---

## 9. Tech stack — recommendations vs. your original choices

| Area | Your choice | Recommendation | Why |
|---|---|---|---|
| Backend | Laravel | **Keep — latest stable Laravel** | Your strength; ecosystem fits perfectly |
| Admin | Filament | **Keep — latest stable Filament + Shield** | Dynamic roles out of the box |
| Public web | Vue | **Vue 3 + Inertia (SSR) + TypeScript + Tailwind**, starting from Laravel's official Vue starter kit | SSR = fast first paint + share previews; no separate SPA routing/auth |
| Android | Native app via API | **Phase 1: PWA (`vite-plugin-pwa`) + Web Share Target + Play Store via TWA (Bubblewrap). Phase 2 (only if needed): native Kotlin/Jetpack Compose or Flutter on the same API** | One codebase for a solo dev; share-from-Facebook works in PWA on Android Chrome; updates instantly without store review |
| DB | (unspecified) | **PostgreSQL** (`pg_trgm` + `unaccent` for fuzzy name search). MySQL 8 is fine if you prefer | Trigram fuzzy search handles Bangla/English names well without extra infra |
| Search | – | Start with Postgres trigram via **Laravel Scout database driver / custom scope**; move to **Meilisearch** if it grows | Avoid running extra infra until needed |
| Files | – | **S3-compatible: Cloudflare R2** (no egress cost), private bucket for originals, public for redacted | Images are the bulk of traffic |
| Media | – | `spatie/laravel-medialibrary` + Intervention Image | Conversions, collections, Filament plugin |
| Queue/cache | – | **Redis + Laravel Horizon** | Image processing, stats refresh |
| Audit | – | `spatie/laravel-activitylog` | Accountability |
| Settings | – | `spatie/laravel-settings` | Admin-editable settings |
| Captcha | – | Cloudflare **Turnstile** | Privacy-friendly, works in BD |
| Backup | – | `spatie/laravel-backup` → encrypted, different provider/region | Survive takedown |
| Tests | – | **Pest** (+ Larastan level 6+) | |
| Map | – | `d3-geo` + `topojson-client` SVG | No tiles, tiny |
| Local dev | – | **Laravel Sail** (Docker), `APP_PORT=8084` to avoid conflicts with other local projects | Consistent environment, easy onboarding, all services (Postgres, Redis, Meilisearch) in one `docker-compose.yml` |
| Hosting | – | VPS in EU (e.g. Hetzner) + **Cloudflare** in front, deployed via Forge/Ploi or Docker; or Laravel Cloud | Jurisdiction, DDoS protection, cheap |
| Monitoring | – | Sentry (errors), uptime checker, Laravel Pulse | |

Pin exact versions in Phase 0 and record them in the README.

### Code architecture
- **Action classes** (`app/Actions/Reports/SubmitReport`, `VerifyReport`, `PublishReport`, `MergeProfiles`, `RedactEvidence` …) hold all business logic. Filament, Inertia controllers and API controllers are thin and call the same actions → web, admin and app can't drift.
- **State machine** for `workflow_status` (`spatie/laravel-model-states` or a small enum + transition class) — illegal transitions throw.
- **Policies** for every model; Filament Shield generates permission names, the policies also enforce workflow guards (two-person rule).
- **Enums** for category, status, visibility (PHP backed enums, translated labels).
- **Domain events** (`ReportVerified`, `ReportPublished`, `ReportRetracted`) → listeners refresh stats, invalidate caches, notify reporter.

---

## 10. Implementation phases

Estimates assume one experienced Laravel developer part-time-ish; adjust freely.

### Phase 0 — Foundations (week 1)
- [ ] **Legal consultation** with a Bangladeshi lawyer (defamation, cyber law, victim-identity law). Draft ToS, privacy policy, methodology page.
- [ ] Decide: name/domain (registrar with privacy, outside BD), hosting, Cloudflare.
- [ ] Repo setup: Laravel + Vue starter kit (Inertia, TS, Tailwind), Pest, Larastan, Pint, ESLint/Prettier, GitHub Actions CI.
- [ ] **Laravel Sail** for local development: `php artisan sail:install` with PostgreSQL, Redis, Meilisearch (for later). Set `APP_PORT=8084` in `.env`. All developers run `./vendor/bin/sail up` to get a consistent environment.
- [ ] R2 buckets (private + public) for production; Sail's local disk for development.
- [ ] Horizon configured (runs inside Sail's Laravel container or a dedicated worker container).
- [ ] Sentry integration.
- [ ] i18n setup (`bn` default, `en`), Bangla font (e.g. Hind Siliguri / Noto Sans Bengali), numeral formatting helper.
- [ ] Seed divisions + 64 districts (BBS codes, bn/en names, population).

### Phase 1 — Domain core & admin (weeks 2–4)
- [ ] Migrations + models + enums + factories for all tables in §3.
- [ ] `FacebookUrlNormalizer` with exhaustive unit tests.
- [ ] Workflow state machine + Action classes + domain events.
- [ ] Filament panel: MFA, Shield, seeded roles, resources (Reports, Profiles, Incidents, Reporters, Appeals, Admins).
- [ ] **Review Queue** page with checklist + locking.
- [ ] Media handling: EXIF strip, sha256, pHash, private/public collections.
- [ ] **Redaction tool** (rectangle boxes → server-side render).
- [ ] Two-person rule + self-review guard (policy tests).
- [ ] Activity log + log viewer.
- [ ] Profile merge action.
- [ ] Admin can create reports manually (to import the existing Facebook group's records — **re-verify each one**, don't bulk-import as verified).

**Exit criteria:** a moderator team can take a report from submission to `public` entirely in the admin, with audit trail.

### Phase 2 — Public website (weeks 5–6)
- [ ] Layout, header, language switch, footer, dark/light (optional).
- [ ] Home: counters, map, featured feed, search box.
- [ ] District map component (TopoJSON prepared + licence attributed).
- [ ] Search (URL exact + fuzzy name, trigram), result page with same-name warning.
- [ ] Profile, Incident, District pages.
- [ ] Methodology, About, Safety resources, ToS, Privacy pages.
- [ ] Stats snapshot job + caching layer.
- [ ] SEO/OG tags for incident & district pages. **Decision needed:** `noindex` on individual profile pages? (Reduces harassment-by-Google and legal exposure, but reduces deterrence. Recommendation: `noindex` profile pages at launch, revisit.)

### Phase 3 — Submissions, API, appeals (weeks 7–8)
- [ ] API v1 (§8) with Sanctum, resources, rate limits, Scramble docs.
- [ ] Submission wizard (Vue) calling the API; Turnstile; tracking code; duplicate detection.
- [ ] Reporter magic-link accounts + "my submissions".
- [ ] Track-my-report page.
- [ ] Appeals form + admin workflow + right-of-reply display.
- [ ] Reporter notifications (email, optional).
- [ ] Evidence package PDF export.

### Phase 4 — PWA & Android (weeks 9–10)
- [ ] `vite-plugin-pwa`: manifest, icons, offline shell, cache strategy (stale-while-revalidate for stats/feed).
- [ ] **Web Share Target** (`share_target` in manifest, POST multipart) → opens submission wizard with images attached.
- [ ] Install prompt UX.
- [ ] TWA via Bubblewrap; Digital Asset Links (`/.well-known/assetlinks.json`); Play Console listing; content rating; UGC policy compliance (report button on every record, ToS).
- [ ] Test on low-end Android devices + slow 3G throttling.

### Phase 5 — Hardening & launch (weeks 11–12)
- [ ] Security review: authz tests on every endpoint, file upload validation (MIME sniffing, size, image re-encode), headers (CSP), Cloudflare WAF rules, admin behind Access.
- [ ] Load test public endpoints (a viral post can bring 100k visitors in an hour); confirm cache hit ratio.
- [ ] Backups + restore drill; "redeploy from scratch" runbook.
- [ ] **Moderator handbook** (verification standard, how to get numeric FB ID, redaction rules, appeal handling, self-care — moderators will see a lot of disturbing content).
- [ ] Soft launch with the existing Facebook group's moderators; seed with re-verified records.
- [ ] Public launch.

### Phase 6 — After launch (backlog)
- Native Android app (Compose/Flutter) only if PWA limits are hit (e.g. background uploads, push reliability).
- Push notifications (Web Push) for reporters.
- Meilisearch if search load/quality demands it.
- Public open-data export of **aggregate** statistics (no names) for journalists/researchers.
- Monthly transparency report (received/verified/rejected/appeals accepted) — builds trust.
- Expand beyond Facebook (YouTube, TikTok comments) — model already allows via `platform` column if added early (**recommendation: add `platform` enum to profiles now, default `facebook`**).
- Expiry/rehabilitation policy (e.g. hide records after N years, or on public apology accepted by appeals team).

---

## 11. Testing strategy
- **Unit:** URL normaliser (dozens of real-world URL shapes), state machine transitions, name normalisation (Bangla/English).
- **Feature (Pest):** every API endpoint (public data never leaks hidden/unverified reports or reporter identity), submission rate limits, two-person rule, appeals.
- **Filament:** Livewire tests for review queue actions and permission gating per seeded role.
- **Browser (optional, Pest browser plugin / Dusk):** submission wizard, share-target flow.
- **CI gate:** Pint, Larastan, Pest, ESLint, `vue-tsc`, build.

---

## 12. Design decisions (resolved)

1. **Facebook group admins become moderators: Yes.** The existing Facebook group's admins will be onboarded as the initial moderator team with seeded Triage/Verifier roles. Existing records from the group will be imported but must be **re-verified one by one** through the standard verification pipeline; no bulk-import as verified. The moderator handbook (Phase 5) will be shaped around their existing workflow knowledge.

2. **Profile photos: name + link only on public pages.** Profile photo snapshots are stored for moderator use (identity confirmation, merge decisions) but are **not shown publicly**. This reduces mis-identification risk (same-name, similar-looking people) and limits potential harassment. The public profile page shows: display name, canonical Facebook URL, category badges, and verified record count. If the project gains legal backing from an NGO later, this decision can be revisited.

3. **Anonymous submissions: allowed and the default.** Anonymous submission (no account required) is the primary, default submission path. Reporters optionally create an account (magic-link email) for submission history and status updates. Anonymous submissions receive the same queue priority as account-based ones at launch; reporter reputation scoring is tracked but only used to deprioritize reporters with a history of fabricated/rejected submissions (anti-abuse), not to penalize genuine anonymous reporters.

4. **Operator identity: pseudonymous collective at launch, pursue NGO partnership.** The site launches under a collective name (e.g. "No More Silence Team") with no individual names publicly attached. The About page states the mission and methodology transparently. In parallel, actively pursue partnership with an established women's-rights or legal-aid NGO in Bangladesh (e.g. BLAST, Ain o Salish Kendra, Bangladesh Mahila Parishad) for legal cover, credibility, and institutional continuity. The site's architecture and data portability should make it easy to transfer operational control to an NGO if a partnership materializes.

5. **Commenter-district on the map: yes, with a toggle and disclaimer.** The map defaults to "Where incidents happened" (reliable, from news sources). A secondary toggle shows "Where commenters say they live" with a prominent disclaimer: *"Based on self-declared location on public Facebook profiles. Unknown for N% of recorded profiles. May be inaccurate."* This data is interesting for pattern analysis but clearly labeled as unreliable. Per-100k-population normalization is included using BBS Census 2022 data.

6. **Track `docs/` in git.** Remove `docs/` from `.gitignore` so the implementation plan and idea document are version-controlled. Sensitive operational documents (moderator handbook with internal procedures, legal correspondence, incident response playbooks) should live in a **separate private repository or encrypted storage**, not in this public-facing repo.
