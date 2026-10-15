# Android Trusted Web Activity (TWA) & Google Play Console Guide

This guide describes how to package **No More Silence** as an Android App via Trusted Web Activity (TWA) using Google's official Bubblewrap CLI, configure Digital Asset Links, and meet Google Play Store requirements.

---

## 1. Overview & Architecture

- **Approach:** PWA (Progressive Web App) wrapped into an Android APK / AAB using Trusted Web Activity (TWA).
- **Package Name:** `org.nomoresilence.twa`
- **Minimum Android Version:** Android 5.0 (API Level 21+)
- **Target Android Version:** Android 14+ (API Level 34+)
- **Browser Engine:** Android Chrome / Custom Tabs (zero WebView overhead, full modern JavaScript & Canvas support).

---

## 2. Web Share Target Integration

When an Android user selects **Share** from the official Facebook app or screenshot gallery, Android triggers the system share sheet:
1. User taps **No More Silence**.
2. Android browser issues `POST /submit/share-target` with `multipart/form-data` containing:
   - `evidence`: Uploaded image screenshots
   - `text`: Text content (frequently containing post URL copied from Facebook)
   - `url`: Direct link (if shared via browser)
3. Laravel stores the temporary share in session, parses any Facebook URL, and redirects to `/submit?share_id={id}`.
4. `Submissions/Create.vue` automatically populates the screenshots into Step 1 and the Facebook URL into Step 2 with a notification banner.

---

## 3. Digital Asset Links Verification

TWA requires domain ownership verification to remove the browser address bar (running in true full-screen standalone mode):

1. File location: `public/.well-known/assetlinks.json`
2. Served publicly at: `https://your-domain.org/.well-known/assetlinks.json`
3. Content structure:
```json
[
  {
    "relation": [
      "delegate_permission/common.handle_all_urls"
    ],
    "target": {
      "namespace": "android_app",
      "package_name": "org.nomoresilence.twa",
      "sha256_cert_fingerprints": [
        "14:6D:E9:7D:0F:52:AB:FC:A4:44:03:7E:8E:B4:98:97:77:46:75:A8:86:16:B3:AC:70:92:DE:0D:39:69:2B:64"
      ]
    }
  }
]
```

When building your production release key in Android Studio / Bubblewrap, update `sha256_cert_fingerprints` with your actual signing key fingerprint:
```bash
keytool -list -v -keystore android.keystore -alias android
```

---

## 4. Building with Bubblewrap CLI

### Prerequisites
- Node.js 18+
- Java JDK 17+
- Android SDK Command-line Tools

### Step-by-Step Build
```bash
# 1. Install Bubblewrap CLI
npm install -g @bubblewrap/cli

# 2. Initialize project from live manifest
bubblewrap init --manifest=https://your-domain.org/manifest.webmanifest

# 3. Enter configuration prompts:
# - Application name: No More Silence
# - Short name: No More Silence
# - Package name: org.nomoresilence.twa
# - Display mode: standalone
# - Theme color: #020617
# - Navigation color: #020617

# 4. Build signed Android App Bundle (.aab)
bubblewrap build

# Result: app-release-bundle.aab ready for Play Console
```

---

## 5. Google Play Console Compliance Checklist

### 5.1 User Generated Content (UGC) Policy (§ 4.7)
- [x] **In-App Reporting:** Every public report card has a prominent "আপত্তি / আপিল (Dispute)" link pointing to `/appeals`.
- [x] **Terms of Service:** Publicly readable at `/terms` declaring prohibited conduct and zero tolerance for doxxing or victim harassment.
- [x] **Moderation Queue:** 8-point verification standard and two-person verification rule before any user-submitted content is made public.
- [x] **Blocking/Banning:** Malicious submitters and abusive profiles can be banned with one click in the Filament admin panel.

### 5.2 Content Rating (IARC)
- **Category:** News / Documentation / Civic Archive.
- **Violence/Threats:** Select "Yes — Documentation of real-world public statements and social commentary without glorification or incitement."
- **Victim Protection:** Confirm compliance with Section 14 Nari o Shishu Nirjatan Daman Ain 2000 (victim identity redaction).

### 5.3 Privacy & Data Safety Section
- **Data Collected:**
  - Screenshots uploaded voluntarily by user (Ephemeral until processed; original stored securely; redacted public copy).
  - Optional email for magic-link authentication (Encrypted with AES-256 in DB).
  - Submitter IP address: Stored solely as HMAC-SHA256 hash for 30-day rate-limiting, never shared with third parties.
- **Third-Party Trackers:** Zero third-party trackers, zero advertising SDKs.
