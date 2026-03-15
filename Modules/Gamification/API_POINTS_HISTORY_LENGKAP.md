# API Points History - Dokumentasi Lengkap

## Endpoint
```
GET /api/v1/user/points-history
```

## Authentication
Bearer Token (Required)

## Response Structure
Response sudah lengkap dan terstruktur dengan baik:
- ✅ Data points dengan informasi lengkap
- ✅ Context (lesson, unit, course, assignment)
- ✅ Labels yang sudah diterjemahkan
- ✅ Pagination metadata
- ✅ Urutan descending berdasarkan created_at (terbaru dulu)

## Query Parameters yang Tersedia

### 1. Pagination
```
per_page=15          # Jumlah item per halaman (default: 15, max: 100)
page=1               # Halaman yang diminta (default: 1)
```

### 2. Sorting
```
sort=created_at      # Ascending berdasarkan tanggal
sort=-created_at     # Descending berdasarkan tanggal (default)
sort=points          # Ascending berdasarkan jumlah poin
sort=-points         # Descending berdasarkan jumlah poin
sort=source_type     # Ascending berdasarkan tipe sumber
sort=-source_type    # Descending berdasarkan tipe sumber
sort=reason          # Ascending berdasarkan alasan
sort=-reason         # Descending berdasarkan alasan
```

### 3. Filters

#### Filter by Source Type
```
filter[source_type]=lesson       # Hanya poin dari lesson
filter[source_type]=assignment   # Hanya poin dari assignment
filter[source_type]=course       # Hanya poin dari course
filter[source_type]=unit         # Hanya poin dari unit
```

#### Filter by Reason
```
filter[reason]=lesson_completed        # Menyelesaikan pelajaran
filter[reason]=assignment_submitted    # Mengumpulkan tugas
filter[reason]=quiz_completed          # Menyelesaikan kuis
filter[reason]=quiz_passed             # Lulus kuis
filter[reason]=perfect_score           # Nilai sempurna
filter[reason]=first_submission        # Pengumpulan pertama
filter[reason]=engagement              # Engagement (forum/reaction)
```

#### Filter by Period
```
filter[period]=today        # Hari ini
filter[period]=this_week    # Minggu ini
filter[period]=this_month   # Bulan ini
filter[period]=this_year    # Tahun ini
```

#### Filter by Date Range
```
filter[date_from]=2026-03-01    # Dari tanggal tertentu
filter[date_to]=2026-03-15      # Sampai tanggal tertentu
```

#### Filter by Points Range
```
filter[points_min]=50     # Minimal poin
filter[points_max]=100    # Maksimal poin
```

## Contoh Penggunaan

### 1. Basic Request (Default)
```bash
GET /api/v1/user/points-history
```

### 2. Filter by Source Type
```bash
GET /api/v1/user/points-history?filter[source_type]=lesson
```

### 3. Filter by Period
```bash
GET /api/v1/user/points-history?filter[period]=this_month
```

### 4. Filter by Date Range
```bash
GET /api/v1/user/points-history?filter[date_from]=2026-03-01&filter[date_to]=2026-03-15
```

### 5. Sort by Points (Highest First)
```bash
GET /api/v1/user/points-history?sort=-points
```

### 6. Kombinasi Multiple Filters
```bash
GET /api/v1/user/points-history?filter[source_type]=lesson&filter[period]=this_week&sort=-points&per_page=20
```

### 7. Filter by Reason
```bash
GET /api/v1/user/points-history?filter[reason]=lesson_completed
```

### 8. Filter by Points Range
```bash
GET /api/v1/user/points-history?filter[points_min]=50&filter[points_max]=100
```

## Response Example

```json
{
  "success": true,
  "message": "Riwayat poin berhasil diambil.",
  "data": [
    {
      "id": 8,
      "points": 100,
      "source_type": "assignment",
      "source_type_label": "Tugas",
      "reason": "assignment_submitted",
      "reason_label": "Mengumpulkan Tugas",
      "description": "Submitted assignment: Quisquam porro ex rerum distinctio natus hic numquam.",
      "context": {
        "assignment": {
          "id": 263,
          "title": "Quisquam porro ex rerum distinctio natus hic numquam."
        },
        "unit": {
          "id": 129,
          "title": "Getting Started"
        },
        "course": {
          "id": 43,
          "title": "Laravel PHP Framework Masterclass"
        }
      },
      "created_at": "2026-03-15T03:09:10.000000Z"
    }
  ],
  "meta": {
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 6,
      "last_page": 1,
      "from": 1,
      "to": 6,
      "has_next": false,
      "has_prev": false
    }
  },
  "errors": null
}
```

## Available Values

### Source Types
- `lesson` - Pelajaran
- `assignment` - Tugas
- `course` - Kursus
- `unit` - Unit
- `grade` - Nilai
- `attempt` - Percobaan

### Reasons
- `lesson_completed` - Menyelesaikan Pelajaran
- `assignment_submitted` - Mengumpulkan Tugas
- `quiz_completed` - Menyelesaikan Kuis
- `quiz_passed` - Lulus Kuis
- `perfect_score` - Nilai Sempurna
- `first_attempt` - Percobaan Pertama
- `first_submission` - Pengumpulan Pertama
- `daily_streak` - Streak Harian
- `forum_post` - Posting Forum
- `forum_reply` - Balasan Forum
- `reaction_received` - Menerima Reaksi
- `engagement` - Engagement (forum/reaction)
- `bonus` - Bonus
- `penalty` - Penalti
- `completion` - Penyelesaian (legacy)
- `score` - Skor (legacy)

## Notes

1. **Default Sorting**: Data diurutkan dari yang terbaru (`-created_at`)
2. **Pagination**: Default 15 items per page, maksimal 100
3. **Cache**: Response di-cache selama 5 menit dengan tag `gamification` dan `points`
4. **Context**: Setiap point memiliki context yang menunjukkan relasi ke lesson, unit, dan course
5. **Labels**: Semua enum sudah diterjemahkan ke Bahasa Indonesia
6. **Filters**: Bisa dikombinasikan untuk query yang lebih spesifik
