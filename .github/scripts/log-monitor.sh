#!/usr/bin/env bash
# =============================================================================
# log-monitor.sh — Monitor Laravel log & kirim ERROR ke Telegram
#
# Usage:
#   ./log-monitor.sh <BOT_TOKEN> <CHAT_ID> <LOG_FILE> [APP_NAME]
#
# Contoh:
#   ./log-monitor.sh "123:ABC" "-100123456" "/var/www/levl/storage/logs/laravel.log" "LEVL-BE"
#
# Jalankan sebagai service: lihat levl-log-monitor.service di folder yang sama
# =============================================================================

# Tidak pakai set -e agar pipe tail tidak matikan service saat log di-rotate
set -uo pipefail

BOT_TOKEN="${1:?BOT_TOKEN wajib diisi}"
CHAT_ID="${2:?CHAT_ID wajib diisi}"
LOG_FILE="${3:?LOG_FILE wajib diisi}"
APP_NAME="${4:-LEVL-BE}"

# Cooldown antar notifikasi (detik) — cegah spam jika error beruntun
COOLDOWN="${COOLDOWN:-30}"

# Batas panjang pesan Telegram (max 4096 chars)
MAX_MSG_LEN=3800

# Direktori script ini berada (untuk memanggil telegram.sh secara relatif)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# ── Fungsi kirim Telegram — reuse telegram.sh yang sudah ada ─────────────────
send_telegram() {
  local message="$1"
  bash "${SCRIPT_DIR}/telegram.sh" "${BOT_TOKEN}" "${CHAT_ID}" "${message}" || true
}

# ── Validasi log file ─────────────────────────────────────────────────────────
if [[ ! -f "$LOG_FILE" ]]; then
  echo "[log-monitor] Log file tidak ditemukan: $LOG_FILE — menunggu..."
  while [[ ! -f "$LOG_FILE" ]]; do
    sleep 5
  done
fi

echo "[log-monitor] Mulai memantau: $LOG_FILE"

# ── Kirim notifikasi startup ──────────────────────────────────────────────────
HOSTNAME=$(hostname -s 2>/dev/null || echo "unknown")
send_telegram "👁 *LOG MONITOR STARTED*
🖥 *Server:* \`${HOSTNAME}\`
📦 *App:* \`${APP_NAME}\`
📄 *File:* \`${LOG_FILE}\`
⏱ *Cooldown:* ${COOLDOWN}s antar notifikasi"

# ── Variabel cooldown ─────────────────────────────────────────────────────────
LAST_SENT=0

# ── Main loop: tail -F dan filter ERROR ──────────────────────────────────────
# -F (bukan -f): otomatis reconnect jika log di-rotate oleh Laravel
tail -n 0 -F "$LOG_FILE" 2>/dev/null | while IFS= read -r line; do

  # Filter hanya baris yang mengandung ERROR (sesuai format log Laravel)
  if ! echo "$line" | grep -q "\.ERROR\|] ERROR\|ERROR:"; then
    continue
  fi

  NOW=$(date +%s)
  ELAPSED=$(( NOW - LAST_SENT ))

  # Terapkan cooldown
  if (( ELAPSED < COOLDOWN )); then
    continue
  fi

  LAST_SENT=$NOW

  # Ambil timestamp dari baris log jika ada (format: [2026-05-03 12:34:56])
  TIMESTAMP=$(echo "$line" | grep -oP '\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]' | head -1 || true)
  if [[ -z "$TIMESTAMP" ]]; then
    TIMESTAMP="[$(date '+%Y-%m-%d %H:%M:%S')]"
  fi

  # Potong pesan jika terlalu panjang
  EXCERPT="${line:0:$MAX_MSG_LEN}"
  if (( ${#line} > MAX_MSG_LEN )); then
    EXCERPT="${EXCERPT}... _(terpotong)_"
  fi

  MESSAGE="🔴 *LARAVEL ERROR DETECTED*
📦 *App:* \`${APP_NAME}\`
🖥 *Server:* \`${HOSTNAME}\`
🕐 *Time:* ${TIMESTAMP}
\`\`\`
${EXCERPT}
\`\`\`"

  send_telegram "$MESSAGE"
  echo "[log-monitor] ERROR terdeteksi & dikirim ke Telegram: ${TIMESTAMP}"

done
