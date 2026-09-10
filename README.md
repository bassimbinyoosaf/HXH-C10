# HXH-C10 — Hunter × Hunter CTF Web Challenge

A hard-tier web exploitation CTF challenge set in the **Hunter × Hunter** universe.

---

## 🎯 Challenge Synopsis
- **Title**: HXH-C10: Hunter Association Secret Gate & Greed Island Economy
- **Category**: Web Exploitation / Source Code Reconnaissance & Broken Object-Level Authorization (IDOR)
- **Difficulty**: Hard-Tier Progressive Challenge
- **Theme**: Hunter × Hunter (Emerald Nen Aura, Multi-Subdomain Portal, Player Roster, P2P In-Game Trades)
- **Database**: MySQL / MariaDB (with standalone zero-dependency auto-migration)

---

## 🚀 Quick Start & Running Locally

### Option 1: Standalone PHP CLI (Instant Zero-Dependency)
```bash
./start.sh 3003
```
Then visit `http://127.0.0.1:3003` in your web browser.

### Option 2: Docker Compose (Production Web + MariaDB Containers)
```bash
docker-compose up -d --build
```
Access the challenge at `http://127.0.0.1:3003`.

---

## 🎮 Challenge Stages & Architecture

### Stage 1: Credential Reconnaissance
1. **Source Inspection**: Inspecting `index.php` source reveals `<script src="assets/js/nen_gatekeeper.js"></script>`.
2. **JS Analysis**: Inspecting `nen_gatekeeper.js` reveals `assets/leaks/hunter_exam_roster.txt`.
3. **Leaked Credentials**:
   - **Username**: `leorio_paradinight`
   - **Password**: `dragon` (Top 100 word in `rockyou.txt`)
4. **Login**: Authenticates into `dashboard.php`.

### Stage 2: Post-Login Workflow, Subdomain Menubar & Account IDOR Takeover
1. **Post-Login Central Hub (`dashboard.php`)**:
   - Shows active Hunter session (`@leorio_paradinight`) and real physical cash in hand (**0 Ⓙ**).
   - Features a clean **Menubar** (`Home`, `Profile`, `Bank`, `Shop`).
   - Displays the **Sector 10 Hunter Registry & Wealth Leaderboard** table showing massive declared net worth estimates for all 15 candidates to make everyone appear rich on the public board.
2. **Account IDOR Takeover (`profile.php?username=<name>`)**:
   - Navigating to `profile.php` opens Leorio's profile (0 Ⓙ in physical cash).
   - Inspecting other users reveals that their declared millions are unbacked/illiquid (0 Ⓙ in real physical cash).
   - Changing the URL query parameter (`profile.php?username=milluki_zoldyck`) completely switches the account session to **Milluki Zoldyck** with zero trace of the previous user, revealing his **10,000 Ⓙ (Jenny)** in actual physical liquid funds.
3. **P2P Trade Execution (`wallet.php`)**:
   - As Milluki, navigate to `wallet.php` and transfer 10,000 Ⓙ to `leorio_paradinight`.
4. **Switch Session Back & Claim Flag (`shop.php`)**:
   - Change the URL back to `profile.php?username=leorio_paradinight` to switch back to Leorio (now holding 10,000 Ⓙ in hand).
    - Purchasing the 1,000 Ⓙ Greed Island Intelligence Dossier on `shop.php` reveals the Stage 2 flag:
    `SSP{MILLUKI_BANK_IDOR_DRAINED_98A41}` and unlocks `gateway.php`.
    - Navigating to `gateway.php` and entering the Stage 2 Flag authenticates the portal and initializes the digitized `hunter_pouch` cookie.

### Stage 3: Client-Side State Tampering (`hunter_pouch` Cookie)
1. **Cookie Inspection**: Authenticating at `gateway.php` issues a Base64-encoded `hunter_pouch` cookie:
   `{"user": "leorio_paradinight", "balance": 0, "role": "hunter_candidate", ...}`
2. **Balance Tampering**: Player modifies `"balance": 0` ➔ `"balance": 50000` (which adds 50,000 Ⓙ to their in-hand 9,000 Ⓙ for a total of 59,000 Ⓙ), Base64-encodes the JSON, and updates the `hunter_pouch` cookie in their browser.
3. **Master Relic Purchase**: Refreshing `shop.php` recognizes **59,000 Ⓙ** in total pouch. Purchasing the **Dark Continent Master Relic (20,000 Ⓙ)** reveals an indirect clue that an automated override signal has been transmitted to `gateway.php`.
4. **Gateway Auto-Unlock & Real Flag Extraction**: Returning to `gateway.php` displays the core override broadcast containing the **Real Final Master Flag**:
   `SSP{WEB_HUNTER_LICENSE_DA91FC72}`.

---

## 📁 Directory Structure
```
HXH-C10/
├── assets/
│   ├── css/
│   │   └── hunter_style.css        # Cyber-anime Nen aura & subdomain menubar styling
│   ├── js/
│   │   ├── audio_effects.js        # Web Audio synthesizer SFX
│   │   └── nen_gatekeeper.js       # External JS with leak locator
│   ├── images/
│   │   ├── logo.png                # Hunter x Hunter Emblem Logo
│   │   ├── grp1.jpeg               # Transparent Login Page Artwork
│   │   └── grp2.jpeg               # Transparent Dashboard Artwork
│   └── leaks/
│       └── hunter_exam_roster.txt  # Leaked credentials dump
├── api/
│   ├── transfer.php                # Vulnerable P2P Game Money Transfer API
│   └── buy_clue.php                # In-Game Item Purchase API
├── bank.php                        # Redirect alias to wallet.php
├── candidates.php                  # Forward to directory / friends table
├── config.php                      # Core settings, MySQL configs & game constants
├── dashboard.php                   # Post-Login Main Hub (User list, Profile & Subdomains)
├── database.sql                    # Full MySQL database initialization script
├── db.php                          # First-class MySQL PDO engine & persistence layer
├── Dockerfile                      # Apache container configuration
├── docker-compose.yml              # Multi-container Web + MariaDB setup
├── friends.php                     # Standalone Hunter Guild Directory view
├── index.php                       # Challenge entrypoint (Login Gate)
├── logout.php                      # Session destroyer
├── nav_header.php                  # Reusable Navbar & Subdomain Menubar UI
├── profile.php                     # Player Profile & IDOR Account Takeover (?username=...)
├── README.md                       # Challenge documentation
├── router.php                      # Built-in PHP server router
├── shop.php                        # Greed Island Item & Clue Merchant
├── solution/
│   ├── STAGE1_WRITEUP.md           # Stage 1 walkthrough
│   ├── STAGE2_WRITEUP.md           # Stage 2 walkthrough
│   └── STAGE3_WRITEUP.md           # Stage 3 walkthrough
├── start.sh                        # Standalone runner
└── wallet.php                      # Player Jenny Wallet & P2P Trade Station
```
