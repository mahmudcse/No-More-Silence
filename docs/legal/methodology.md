# Verification Standards & Methodology (Draft)

> **Status:** Standard Operating Procedure & Public Methodology Document  
> **Purpose:** Public Transparency, Legal Defensibility, and Operational Consistency.

---

## 1. Principles of Verification

The fundamental legitimacy of No More Silence rests upon rigorous verification. Because false attributions can cause irreparable harm to innocent individuals, **no report is ever published automatically upon submission**. Every record must satisfy strict evidentiary criteria.

---

## 2. The Verification Pipeline

```
[Submission] 
     │
     ▼
[Step 1: Automated Ingestion]
  - EXIF data stripped
  - sha256 + perceptual hash (pHash) generated for deduplication
  - Tracking code hash generated for submitter
     │
     ▼
[Step 2: Triage Review]
  - Reviewer checks for spam, trolling, or duplicate submission
  - Matches against existing pHash or canonical URL
  - Outcome: Mark duplicate, mark spam, or advance to 'in_verification'
     │
     ▼
[Step 3: Verification Officer Review]
  - Permalink verification: officer inspects live post or web archive
  - Attribution check: confirms the account posting the comment matches submitted URL
  - Facebook URL normalisation: maps vanity URLs and profile URLs to canonical structure
  - Extraction/Confirmation of numerical ID (`fb_numeric_id`)
  - Incident linking: associates comment with a known generic incident record
  - Verbatim transcription check
  - Checklist logged in database (`report_reviews.checklist`)
     │
     ▼
[Step 4: Redaction Review]
  - Redactor applies rectangular bounding masks to obscure victim names, bystander accounts, and unrelated context
  - Server renders irreversible WebP flattened image for public display
     │
     ▼
[Step 5: Publishing Review (The Two-Person Rule)]
  - Enforced by application policy: an admin who verified a report CANNOT publish that same report
  - Publishing officer verifies redaction and classification
  - Visibility transition: 'hidden' → 'public' (or 'featured' for homepage feed)
```

---

## 3. Categories of Recorded Content

Reports are categorized into specific, objective classes:
1. **Rape Apologia (`rape_apologia`):** Justifying, defending, celebrating, or rationalizing sexual assault or rape.
2. **Victim Blaming (`victim_blaming`):** Assigning responsibility or fault to the survivor based on clothing, presence in public spaces, lifestyle, gender, or behavior.
3. **Sexual Threat (`sexual_threat`):** Explicit threats of sexual violence, assault, or rape directed at any individual or group.
4. **Sexual Harassment (`sexual_harassment`):** Explicit sexual advances, non-consensual vulgar harassment, or sexualised humiliation.
5. **Incitement (`incitement`):** Calling on mobs or others to commit sexual violence or physical harm against individuals.
6. **Doxxing (`doxxing`):** Publishing private details of individuals with intent to facilitate harassment or violence.

---

## 4. Normalization and Duplicate Detection

4.1. **URL Normalisation:** All variations of Facebook profile URLs (e.g., `m.facebook.com`, `web.facebook.com`, `facebook.com/profile.php?id=...`, trailing parameters such as `?mibextid=...`) are parsed into a single normalized identity via `App\Support\FacebookUrlNormalizer`.

4.2. **Perceptual Image Hashing:** Uploaded screenshots are hashed using a 64-bit difference perceptual hash (`pHash`). Re-submissions of identical or lightly cropped screenshots are flagged to prevent duplicate entries and queue bloating.

---

## 5. Right of Reply and Retraction SLA

- **Right of Reply:** Profile owners who submit an authenticated right-of-reply have their statement appended directly to the public incident record under a dedicated "Owner Reply" section.
- **Retraction Standard:** If evidence is shown to be doctored, fabricated, or from an impersonated account verified as false, the report is retracted within 24 hours and a public log update recorded in the transparency registry.
