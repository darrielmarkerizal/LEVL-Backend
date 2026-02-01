#!/bin/bash

# ==================================================
# BENCHMARK SCRIPT: Laravel Octane vs PHP-FPM
# Untuk Tugas Akhir - Perbandingan Performa
# ==================================================

# Konfigurasi
OCTANE_PORT=8000
OCTANE_HOST="levl-backend.site"
PHPFPM_PORT=80
PHPFPM_HOST="serve.levl-backend.site"
REQUESTS=1000
CONCURRENCY=20
WARMUP_REQUESTS=10
RUNS=3
OUTPUT_DIR="tests/reports/benchmark"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
REPORT_FILE="$OUTPUT_DIR/report_${TIMESTAMP}.txt"
JSON_FILE="$OUTPUT_DIR/report_${TIMESTAMP}.json"

# Warna output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# ==================================================
# INIT
# ==================================================
mkdir -p $OUTPUT_DIR

echo -e "${GREEN}=================================================="
echo "BENCHMARK: Laravel Octane (Swoole) vs PHP-FPM"
echo "Timestamp: $(date)"
echo "Requests: $REQUESTS | Concurrency: $CONCURRENCY | Runs: $RUNS"
echo -e "==================================================${NC}"
echo ""

# Header CSV
echo "Run,RPS,Mean_Latency,P50,P95,P99,Failed" > "$OUTPUT_DIR/octane_results_${TIMESTAMP}.csv"
echo "Run,RPS,Mean_Latency,P50,P95,P99,Failed" > "$OUTPUT_DIR/phpfpm_results_${TIMESTAMP}.csv"

# Temp files untuk kumpulkan raw values
> /tmp/bench_octane_rps.txt
> /tmp/bench_octane_mean.txt
> /tmp/bench_octane_p50.txt
> /tmp/bench_octane_p95.txt
> /tmp/bench_octane_p99.txt
> /tmp/bench_phpfpm_rps.txt
> /tmp/bench_phpfpm_mean.txt
> /tmp/bench_phpfpm_p50.txt
> /tmp/bench_phpfpm_p95.txt
> /tmp/bench_phpfpm_p99.txt

# ==================================================
# FUNGSI
# ==================================================

flush_redis() {
    echo -e "${YELLOW}  Flushing Redis cache...${NC}"
    redis-cli FLUSHALL > /dev/null 2>&1
    sleep 1
}

warmup() {
    local port=$1
    local name=$2
    local host=$3
    echo -e "${YELLOW}  Warming up $name...${NC}"
    for i in $(seq 1 $WARMUP_REQUESTS); do
        curl -s -H "Host: $host" "http://127.0.0.1:$port/api/v1/benchmark/users" > /dev/null
    done
    sleep 2
}

run_benchmark() {
    local port=$1
    local name=$2
    local run_num=$3
    local host=$4
    local output_file="$OUTPUT_DIR/${name}_run${run_num}_${TIMESTAMP}.txt"

    echo -e "${GREEN}  Running $name benchmark (Run $run_num/$RUNS)...${NC}"
    ab -n $REQUESTS -c $CONCURRENCY -H "Host: $host" "http://127.0.0.1:$port/api/v1/benchmark/users" > "$output_file" 2>&1

    # Extract metrics
    local rps=$(grep "Requests per second" "$output_file" | awk '{print $4}')
    local mean=$(grep "Time per request" "$output_file" | head -1 | awk '{print $4}')
    local p50=$(grep "^  50%" "$output_file" | awk '{print $2}')
    local p95=$(grep "^  95%" "$output_file" | awk '{print $2}')
    local p99=$(grep "^  99%" "$output_file" | awk '{print $2}')
    local failed=$(grep "Failed requests" "$output_file" | awk '{print $3}')

    echo -e "  ${BLUE}RPS: $rps | Mean: ${mean}ms | P50: ${p50}ms | P95: ${p95}ms | P99: ${p99}ms | Failed: $failed${NC}"

    # Simpan ke CSV
    echo "$run_num,$rps,$mean,$p50,$p95,$p99,$failed" >> "$OUTPUT_DIR/${name}_results_${TIMESTAMP}.csv"

    # Simpan raw values ke temp files untuk perhitungan average
    echo "$rps" >> "/tmp/bench_${name}_rps.txt"
    echo "$mean" >> "/tmp/bench_${name}_mean.txt"
    echo "$p50" >> "/tmp/bench_${name}_p50.txt"
    echo "$p95" >> "/tmp/bench_${name}_p95.txt"
    echo "$p99" >> "/tmp/bench_${name}_p99.txt"
}

# Hitung average dari file temp
calc_avg() {
    local file=$1
    awk '{sum+=$1; count++} END {if(count>0) printf "%.2f", sum/count; else print "0"}' "$file"
}

# ==================================================
# PHASE 1: OCTANE
# ==================================================
echo -e "${GREEN}=== PHASE 1: Laravel Octane (Swoole) ===${NC}"
echo -e "${BLUE}  Target: 127.0.0.1:${OCTANE_PORT} | Host: ${OCTANE_HOST}${NC}"
echo ""

for run in $(seq 1 $RUNS); do
    flush_redis
    warmup $OCTANE_PORT "octane" "$OCTANE_HOST"
    run_benchmark $OCTANE_PORT "octane" $run "$OCTANE_HOST"
    echo ""
    sleep 3
done

# ==================================================
# PHASE 2: PHP-FPM
# ==================================================
echo -e "${GREEN}=== PHASE 2: PHP-FPM (via Nginx) ===${NC}"
echo -e "${BLUE}  Target: 127.0.0.1:${PHPFPM_PORT} | Host: ${PHPFPM_HOST}${NC}"
echo ""

for run in $(seq 1 $RUNS); do
    flush_redis
    warmup $PHPFPM_PORT "phpfpm" "$PHPFPM_HOST"
    run_benchmark $PHPFPM_PORT "phpfpm" $run "$PHPFPM_HOST"
    echo ""
    sleep 3
done

# ==================================================
# HITUNG AVERAGES
# ==================================================
OCT_AVG_RPS=$(calc_avg /tmp/bench_octane_rps.txt)
OCT_AVG_MEAN=$(calc_avg /tmp/bench_octane_mean.txt)
OCT_AVG_P50=$(calc_avg /tmp/bench_octane_p50.txt)
OCT_AVG_P95=$(calc_avg /tmp/bench_octane_p95.txt)
OCT_AVG_P99=$(calc_avg /tmp/bench_octane_p99.txt)

FPM_AVG_RPS=$(calc_avg /tmp/bench_phpfpm_rps.txt)
FPM_AVG_MEAN=$(calc_avg /tmp/bench_phpfpm_mean.txt)
FPM_AVG_P50=$(calc_avg /tmp/bench_phpfpm_p50.txt)
FPM_AVG_P95=$(calc_avg /tmp/bench_phpfpm_p95.txt)
FPM_AVG_P99=$(calc_avg /tmp/bench_phpfpm_p99.txt)

# Hitung perbandingan (Octane vs PHP-FPM)
RPS_DIFF=$(awk "BEGIN {if($FPM_AVG_RPS>0) printf \"%.1f\", (($OCT_AVG_RPS - $FPM_AVG_RPS) / $FPM_AVG_RPS) * 100; else print \"N/A\"}")
MEAN_DIFF=$(awk "BEGIN {if($FPM_AVG_MEAN>0) printf \"%.1f\", (($FPM_AVG_MEAN - $OCT_AVG_MEAN) / $FPM_AVG_MEAN) * 100; else print \"N/A\"}")

# ==================================================
# GENERATE TEXT REPORT
# ==================================================
cat > "$REPORT_FILE" << EOF
==================================================
BENCHMARK REPORT: Laravel Octane vs PHP-FPM
==================================================
Timestamp   : $(date)
VPS Specs   : $(nproc) cores | $(free -m | awk '/Mem:/{print $2}') MB RAM
Requests    : $REQUESTS
Concurrency : $CONCURRENCY
Runs        : $RUNS
--------------------------------------------------

[OCTANE - Per Run]
$(cat "$OUTPUT_DIR/octane_results_${TIMESTAMP}.csv")

[PHP-FPM - Per Run]
$(cat "$OUTPUT_DIR/phpfpm_results_${TIMESTAMP}.csv")

--------------------------------------------------
[AVERAGES]
--------------------------------------------------
Metric          | Octane (Swoole)  | PHP-FPM          | Diff
----------------|------------------|------------------|------------------
RPS             | $OCT_AVG_RPS     | $FPM_AVG_RPS     | ${RPS_DIFF}%
Mean Latency ms | $OCT_AVG_MEAN    | $FPM_AVG_MEAN    | ${MEAN_DIFF}%
P50 ms          | $OCT_AVG_P50     | $FPM_AVG_P50     |
P95 ms          | $OCT_AVG_P95     | $FPM_AVG_P95     |
P99 ms          | $OCT_AVG_P99     | $FPM_AVG_P99     |
--------------------------------------------------

Octane RPS lebih tinggi ${RPS_DIFF}% dari PHP-FPM.
Octane Mean Latency lebih rendah ${MEAN_DIFF}% dari PHP-FPM.

==================================================
EOF

# ==================================================
# GENERATE JSON REPORT
# ==================================================
cat > "$JSON_FILE" << EOF
{
  "meta": {
    "timestamp": "$(date -Iseconds)",
    "vps_cores": $(nproc),
    "vps_ram_mb": $(free -m | awk '/Mem:/{print $2}'),
    "requests": $REQUESTS,
    "concurrency": $CONCURRENCY,
    "runs": $RUNS
  },
  "octane": {
    "avg_rps": $OCT_AVG_RPS,
    "avg_mean_latency_ms": $OCT_AVG_MEAN,
    "avg_p50_ms": $OCT_AVG_P50,
    "avg_p95_ms": $OCT_AVG_P95,
    "avg_p99_ms": $OCT_AVG_P99
  },
  "phpfpm": {
    "avg_rps": $FPM_AVG_RPS,
    "avg_mean_latency_ms": $FPM_AVG_MEAN,
    "avg_p50_ms": $FPM_AVG_P50,
    "avg_p95_ms": $FPM_AVG_P95,
    "avg_p99_ms": $FPM_AVG_P99
  },
  "comparison": {
    "rps_diff_percent": "$RPS_DIFF",
    "mean_latency_diff_percent": "$MEAN_DIFF"
  }
}
EOF

# ==================================================
# PRINT SUMMARY KE TERMINAL
# ==================================================
echo -e "${GREEN}=================================================="
echo "SUMMARY"
echo -e "==================================================${NC}"
echo ""
printf "${BLUE}%-20s | %-18s | %-18s | %-10s${NC}\n" "Metric" "Octane (Swoole)" "PHP-FPM" "Diff"
printf "${BLUE}%-20s-+-%-18s-+-%-18s-+-%-10s${NC}\n" "--------------------" "------------------" "------------------" "----------"
printf "%-20s | %-18s | %-18s | ${GREEN}%-10s${NC}\n" "RPS" "$OCT_AVG_RPS" "$FPM_AVG_RPS" "${RPS_DIFF}%"
printf "%-20s | %-18s | %-18s | ${GREEN}%-10s${NC}\n" "Mean Latency (ms)" "$OCT_AVG_MEAN" "$FPM_AVG_MEAN" "${MEAN_DIFF}%"
printf "%-20s | %-18s | %-18s |\n" "P50 (ms)" "$OCT_AVG_P50" "$FPM_AVG_P50"
printf "%-20s | %-18s | %-18s |\n" "P95 (ms)" "$OCT_AVG_P95" "$FPM_AVG_P95"
printf "%-20s | %-18s | %-18s |\n" "P99 (ms)" "$OCT_AVG_P99" "$FPM_AVG_P99"
echo ""
echo -e "${YELLOW}Output files:${NC}"
echo "  Raw ab output : $OUTPUT_DIR/octane_run*  /  $OUTPUT_DIR/phpfpm_run*"
echo "  CSV results   : $OUTPUT_DIR/octane_results_${TIMESTAMP}.csv  /  $OUTPUT_DIR/phpfpm_results_${TIMESTAMP}.csv"
echo "  Text report   : $REPORT_FILE"
echo "  JSON report   : $JSON_FILE"
echo ""
echo -e "${GREEN}Benchmark completed at $(date)${NC}"

# Cleanup temp files
rm -f /tmp/bench_octane_*.txt /tmp/bench_phpfpm_*.txt