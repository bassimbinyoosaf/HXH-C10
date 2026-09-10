# Solution Walkthrough — HXH-C10 (Stage 1)

## 📌 Challenge Overview
The player is presented with the Hunter Association Secret Clearance Gate (`index.php`).

## 🔍 Step-by-Step Exploitation

### Step 1: Initial Inspection
View the HTML source code of `http://127.0.0.1:3003/index.php`.
Near the bottom of the document, notice the inclusion of external JavaScript assets:
```html
<script src="assets/js/audio_effects.js"></script>
<script src="assets/js/nen_gatekeeper.js"></script>
```

### Step 2: Analyze `nen_gatekeeper.js`
Fetch or view `http://127.0.0.1:3003/assets/js/nen_gatekeeper.js`.
The header comments contain an explicit operational note:
```javascript
/**
 * BACKUP ROSTER LOCATOR: assets/leaks/hunter_exam_roster.txt
 */
```

### Step 3: Extract Credentials from Leak
Fetch `http://127.0.0.1:3003/assets/leaks/hunter_exam_roster.txt`:
```text
[CLEARANCE ENTRY #403]
Status          : ACTIVE (Double-Star Hunter Candidate)
Codename        : Leorio Paradinight
Affiliation     : Zodiac Twelve (Codename: "Boar") / Medical Science Division
Username        : leorio_paradinight
Passphrase      : dragon
```

Note: `dragon` is verified to be within the top 100 entries of the standard `rockyou.txt` wordlist (ranked at #17).

### Step 4: Authenticate & Clear Stage 1
1. Navigate back to `index.php`.
2. Input:
   - **Username**: `leorio_paradinight`
   - **Password**: `dragon`
3. Click **"Verify Clearance"**.
4. The server validates the session and grants access to `dashboard.php` (Hunter Inner Terminal).
