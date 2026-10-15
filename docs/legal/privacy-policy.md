# Privacy Policy (Draft)

> **Status:** Draft for Legal Review  
> **Key Commitments:** Zero Victim PII · Reporter Anonymity · EXIF Scrubbing · Minimal Data Retention · Accused Profile Verification Standards

---

## 1. Core Privacy Philosophy

No More Silence is built with privacy-by-design to balance public interest transparency with data protection and human safety. The architecture guarantees three levels of protection:
1. **Victim Protection:** Absolute exclusion of survivor identity.
2. **Reporter Protection:** Anonymity by default, cryptographic hashing of identifiers, and automatic metadata stripping.
3. **Operator/Moderator Protection:** Pseudonymous display names and strict administrative access controls.

---

## 2. Information We Collect and Process

### 2.1. From Reporters
- **Anonymous Submissions:** We collect no name, phone number, or physical address. Reporters receive an alphanumeric tracking code (hashed via argon2id/bcrypt in the database) to query the status of their report.
- **Account Submissions (Optional):** Reporters opting for an account provide only an email address for passwordless magic-link authentication. Email addresses are encrypted at rest in the database (`reporters.email`).
- **Technical Upload Metadata:** 
  - All uploaded images are immediately processed on the server to **strip all EXIF metadata** (GPS coordinates, camera model, timestamps, software version).
  - Submitter IP addresses are **never stored in raw text**. Only a salted, one-way hash (`submitter_ip_hash`) is temporarily retained for rate limiting and sybil abuse detection, pruned automatically according to our data retention schedule.

### 2.2. Concerning Recorded Social Media Profiles
- **Target of Record:** Only public comments posted on open social media surfaces (Facebook public posts, public pages, public groups) are catalogued.
- **Data Points Collected:**
  - Canonical profile URL and numerical ID (`fb_numeric_id`).
  - Public display name at the time the comment was made.
  - Verbatim text of the abusive comment.
  - Moderator-captured verification snapshot (archived privately; profile pictures are **not** published publicly to prevent same-name harassment).
- **Excluded Information:** We **never** catalogue or publish private personal data (phone numbers, physical residential addresses, national identification numbers, private employers, or innocent family members).

---

## 3. Storage and Data Retention

3.1. **Original Evidence:** Uploaded original evidence is stored on private, encrypted cloud storage (Cloudflare R2 private bucket). It is accessible only by authorized verification staff and never exposed directly via public web URLs.

3.2. **Redacted Evidence:** Public evidence displayed on the website has undergone redaction using the server-side redaction utility. Bystander profile pictures, bystander names, and any references to victims are permanently covered by blackout masks before export to WebP format.

3.3. **Logs & Audit Trail:** Administrative activities (review decisions, redaction changes, profile merges) are logged via `spatie/laravel-activitylog` for accountability. Access to the unredacted reporter IP hash is restricted to administrators holding the explicit `view_reporter_identity` permission.

---

## 4. Rights and Appeals for Profile Owners

4.1. **Verification of Identity:** Profile owners who believe their account was falsely cited, compromised, or mistakenly attributed may file a formal Appeal. To protect against malicious third parties claiming an account, verification proof (such as posting a temporary verification hash on the verified Facebook account or direct messaging from that account) is required.

4.2. **Rectification and Retraction:** When an appeal is upheld, the affected report is immediately marked `retracted`, removed from the public website, search index, and cached counter aggregations.

---

## 5. Third-Party Services & Cookies

- **Cloudflare Turnstile:** Used for bot prevention and DDoS mitigation on submission and auth forms. Turnstile does not track users across websites or drop persistent tracking cookies.
- **No Marketing Cookies / Trackers:** We do not use third-party behavioral advertising trackers, Google Analytics, Facebook Pixel, or commercial marketing SDKs.
