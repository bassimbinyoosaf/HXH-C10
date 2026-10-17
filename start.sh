#!/usr/bin/env bash
# ==============================================================================
# HXH-C10 CTF Challenge Launcher
# ==============================================================================

PORT=${1:-3003}
HOST="0.0.0.0"
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

echo "===================================================================="
echo "  HUNTER × HUNTER : PROJECT HXH-C10 (HARD-TIER WEB CTF)"
echo "  Stage 1: Hunter Association Clearance Gate"
echo "===================================================================="
echo "[+] Starting PHP Server on http://${HOST}:${PORT}..."
echo "[+] Root directory: ${DIR}"
echo "[+] Press Ctrl+C to stop the challenge server."
echo "===================================================================="

php -S "${HOST}:${PORT}" -t "${DIR}" "${DIR}/router.php"
