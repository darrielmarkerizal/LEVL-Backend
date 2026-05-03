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

set -euo pipefail

BOT_TOKEN="${1:?BOT_TOKEN wajib diisi}"
CHAT_ID="${2:?CHAT_ID wajib diisi}"
LOG_FILE="${3:?LOG_FILE wajib diisi}"
APP_NAME="${4:-LEVL-BE}"

# Cooldown antar notifikasi (detik) — cegah spam jika error beruntun
COOLDOWN="${COOLDOWN:-30}"

# Batas panjang pesan Telegram (max 4096 chars)
MAX_MSG_LEN=3800

# ── Fungsi kirim Telegram ─────────────────────────────────────────────────────
send_telegram() {
  local message="$1"
  curl -s -X POST "https://api.telegram.org/bot${BOT_TOKEN}/sendMessage" \
    -d chat_id="${CHAT_ID}" \
    -d parse_mode="Markdown" \
    --data-urlencode "text=${message}" \
    > /dev/null 2>&1 || true
}

# ── Validasi log file ─────────────────────────────────────────────────────────
if [[ ! -f "$LOG_FILE" ]]; then
  echo "[log-monitor] Log file tidak ditemukan: $LOG_FILE — menunggu..."
  # Tunggu sampai file ada (misal setelah deploy pertama)
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

# ── Main loop: tail -f dan filter ERROR ──────────────────────────────────────
tail -n 0 -F "$LOG_FILE" 2>/dev/null | while IFS= read -r line; do

  # Filter hanya baris yang mengandung ERROR (case-insensitive)
  if ! echo "$line" | grep -qi "\.ERROR\|] ERROR\|ERROR:"; then
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
