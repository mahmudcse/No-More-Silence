# No More Silence — Moderator Handbook & Ethical Standard

This handbook is the operational guide for all moderators, verifiers, and administrators on **No More Silence**. It governs evidence verification, redaction, appeals, and psychological self-care.

---

## 1. Core Principles

1. **Absolute Victim Protection:** Under Section 14 of the Nari o Shishu Nirjatan Daman Ain 2000, disclosing the name, address, photo, or identifiable details of a survivor of sexual assault or violence is a criminal offence. Any evidence submitted containing victim identifiers MUST be redacted immediately before publishing.
2. **Neutrality & Facticity:** We document public words as spoken. We do not editorialize, embellish, or fabricate evidence.
3. **Due Process (Two-Person Rule):** No single person can verify and publish a record alone. At least two distinct verified moderators must examine each report.

---

## 2. The 8-Point Verification Standard

Before marking any report as **Verified** in the Filament Review Queue, complete every item in this checklist:

1. **Public Availability:** Confirm the comment was posted publicly (on a public post, page, or public group), not inside a private secret group or personal private DM.
2. **Commenter Attribution:** Confirm the comment was authored by the documented Facebook profile (check name, profile URL, and avatar timestamp matching).
3. **Permanent Canonical URL:** Normalize the profile URL to its canonical form (numeric ID or vanity username).
4. **Verbatim Fidelity:** Ensure the transcribed text matches the screenshot character-for-character without omission.
5. **Contextual Relevance:** Verify the comment constitutes sexual violence apologia, victim blaming, or threats, rather than satire, parody, or unrelated political commentary.
6. **Integrity & Authenticity:** Inspect screenshot for signs of digital tampering, mismatched font kerning, edited DOM elements, or fabricated mockups. Check SHA-256 and pHash checksums.
7. **Victim Redaction:** Confirm all references to the victim's name, school, home district, or photograph are fully redacted under solid black bounding boxes.
8. **Corroborating Archive:** Ensure original raw screenshot is safely stored in private R2 storage for legal/police handover if subpoenaed.

---

## 3. Finding Numeric Facebook IDs

Facebook vanity usernames (e.g. `facebook.com/john.doe`) can be changed by the user at any time. A numeric Facebook ID (`profile.php?id=1000...`) remains permanent forever.

### Method 1: Mobile URL Inspection
1. Change `www.facebook.com` to `m.facebook.com`.
2. Inspect profile links or click on their profile photo; the numeric ID frequently appears in URL query parameters (`id=1000...` or `fbid=...`).

### Method 2: Page Source Lookup
1. In Chrome / Firefox, open the profile page.
2. Press `Ctrl+U` (View Source).
3. Search for `"entity_id":"` or `"userID":"` or `"user_id":`.
4. The 15–16 digit number following is the permanent numeric Facebook ID.

---

## 4. Redaction Standard: Solid Black Boxes Only

- **Never Use Blur or Pixelation:** Image blur filters can be partially or fully reversed using deconvolution algorithms and machine learning.
- **Always Apply Solid Black Rectangles:** Use the Filament Redaction Tool to place solid `#000000` bounding boxes covering:
  - Victim's full name, nickname, or initials
  - Victim's photograph or family photos
  - Specific school, village, or street addresses
  - Innocent bystanders or minors appearing in screenshot avatars

---

## 5. Right of Reply & Appeals Workflow

We recognize that accounts can be hacked, compromised, or spoofed by malicious third parties.
1. All appeals received via `/appeals` appear in the Filament Appeals resource.
2. Review within **72 hours**.
3. If an appellant provides credible evidence (such as a Police General Diary / GD filing showing account compromise, or proof of impersonation by a duplicate profile):
   - Immediately transition the linked report's status to `retracted`.
   - Update the public record with an official clarification notice or unlist the profile entirely.
   - Record the decision and rationale in the immutable activity log.

---

## 6. Vicarious Trauma & Moderator Self-Care Protocol

Moderators on this platform read horrific statements defending sexual violence, rape threats, and severe misogyny. Prolonged exposure can cause vicarious trauma, depression, and burnout.

### Operational Guardrails:
1. **Mandatory Shift Limits:** No moderator may review reports for more than **90 consecutive minutes** or more than **3 hours per calendar day**.
2. **Mandatory Rotation:** Verifiers must rotate off review queues every two weeks to technical, editorial, or research duties.
3. **Never Moderate in Isolation:** Always keep open communication with fellow team members. If a particular report is triggering or involves someone you know, re-assign it immediately.
4. **Professional Mental Health Access:** If you experience sleep disturbances, intrusive thoughts, or acute anxiety, notify the team coordinator. Free, confidential counselling is provided via partner mental health organizations (Kaan Pete Roi hotline: 01779554391 / 01779554392).
