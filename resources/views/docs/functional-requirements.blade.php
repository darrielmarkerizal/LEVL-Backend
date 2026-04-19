<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kebutuhan Fungsional — TA PREP LSP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .endpoint-badge {
            font-family: 'JetBrains Mono', 'Fira Code', monospace;
            font-size: 0.7rem;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">
    <div class="max-w-[1400px] mx-auto px-4 py-8">
        <div class="mb-10 text-center">
            <h1 class="text-3xl font-bold text-gray-900">Kebutuhan Fungsional</h1>
            <p class="text-gray-500 mt-2">TA PREP LSP — Backend API</p>
            <p class="text-xs text-gray-400 mt-1">Di-generate pada: {{ now()->format('d M Y, H:i') }} WIB</p>
        </div>

        <nav class="mb-8 flex flex-wrap gap-2 justify-center">
            <a href="#publik" class="px-4 py-2 bg-green-100 text-green-800 rounded-lg text-sm font-medium hover:bg-green-200 transition">Publik</a>
            <a href="#administrator" class="px-4 py-2 bg-red-100 text-red-800 rounded-lg text-sm font-medium hover:bg-red-200 transition">Administrator</a>
            <a href="#instruktur" class="px-4 py-2 bg-blue-100 text-blue-800 rounded-lg text-sm font-medium hover:bg-blue-200 transition">Instruktur</a>
            <a href="#asesi" class="px-4 py-2 bg-purple-100 text-purple-800 rounded-lg text-sm font-medium hover:bg-purple-200 transition">Asesi</a>
        </nav>
        <section id="publik" class="mb-12">
            <h2 class="text-2xl font-bold mb-4 text-gray-900">Kebutuhan Fungsional — Publik</h2>
            <div class="overflow-x-auto rounded-lg shadow">
                <table class="w-full border-collapse bg-white">
                    <thead>
                        <tr class="bg-green-600 text-white">
                            <th class="px-4 py-3 text-left text-sm font-semibold w-12">No</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold w-48">Fitur</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Detail</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold w-96">Endpoint</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">1</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">SwaggerController</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Publik dapat melihat api (api)</li>
                                <li class="py-0.5">Publik dapat melihat api (oauth2Callback)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/documentation</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/oauth2-callback</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">2</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Otentikasi</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Publik dapat membuat/mengirim autentikasi (verifyEmail)</li>
                                <li class="py-0.5">Publik dapat melihat autentikasi (googleCallback)</li>
                                <li class="py-0.5">Publik dapat melihat autentikasi (googleRedirect)</li>
                                <li class="py-0.5">Publik dapat melakukan login</li>
                                <li class="py-0.5">Publik dapat memperbarui token autentikasi</li>
                                <li class="py-0.5">Publik dapat melakukan registrasi akun</li>
                                <li class="py-0.5">Publik dapat melihat dev (generateDevTokens)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/auth/email/verify</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/auth/google/callback</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/auth/google/redirect</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/auth/login</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/auth/refresh</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/auth/register</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/dev/tokens</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">3</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Reset Password</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Publik dapat membuat/mengirim autentikasi (forgot)</li>
                                <li class="py-0.5">Publik dapat membuat/mengirim autentikasi (confirmForgot)</li>
                                <li class="py-0.5">Publik dapat membuat/mengirim autentikasi (reset)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/auth/password/forgot</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/auth/password/forgot/confirm</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/auth/password/reset</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">4</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Benchmark</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Publik dapat melihat daftar pengguna</li>
                                <li class="py-0.5">Publik dapat menambah pengguna</li>
                                <li class="py-0.5">Publik dapat menghapus pengguna</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/benchmark/users</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/benchmark/users</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/benchmark/users</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">5</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Manajemen Kategori</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Publik dapat melihat daftar kategori</li>
                                <li class="py-0.5">Publik dapat melihat detail kategori</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/categories</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/categories/{category}</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">6</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Pencarian Konten</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Publik dapat mencari pencarian</li>
                                <li class="py-0.5">Publik dapat melihat pencarian (autocomplete)</li>
                                <li class="py-0.5">Publik dapat melakukan pencarian global</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/search</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/search/autocomplete</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/search/global</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">7</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Manajemen Kursus</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Publik dapat melihat daftar kursus</li>
                                <li class="py-0.5">Publik dapat melihat detail kursus</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">8</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Unit Kompetensi</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Publik dapat melihat daftar kursus</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">9</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Konfigurasi Level</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Publik dapat melihat daftar konfigurasi level</li>
                                <li class="py-0.5">Publik dapat melihat detail konfigurasi level</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/level-configs</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/level-configs/{level_config}</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">10</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Master Data</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Publik dapat melihat daftar master data</li>
                                <li class="py-0.5">Publik dapat melihat master data (types)</li>
                                <li class="py-0.5">Publik dapat melihat daftar master data</li>
                                <li class="py-0.5">Publik dapat melihat master data (get)</li>
                                <li class="py-0.5">Publik dapat melihat detail master data</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/master-data/courses</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/master-data/types</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/master-data/{type}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/master-data/{type}/all</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/master-data/{type}/{id}</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">11</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">TagController</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Publik dapat melihat daftar tags</li>
                                <li class="py-0.5">Publik dapat melihat detail tags</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/tags</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/tags/{tag}</span></li>
                                </ul>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
        <section id="administrator" class="mb-12">
            <h2 class="text-2xl font-bold mb-4 text-gray-900">Kebutuhan Fungsional — Administrator (Superadmin & Admin)</h2>
            <div class="overflow-x-auto rounded-lg shadow">
                <table class="w-full border-collapse bg-white">
                    <thead>
                        <tr class="bg-red-600 text-white">
                            <th class="px-4 py-3 text-left text-sm font-semibold w-12">No</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold w-48">Fitur</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Detail</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold w-96">Endpoint</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">1</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">ActivityLogController</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Administrator dapat melihat daftar activity-logs</li>
                                <li class="py-0.5">Administrator dapat melihat detail activity-logs</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/activity-logs</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/activity-logs/{id}</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">2</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Post/Pengumuman Notifikasi</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Administrator dapat menambah post/pengumuman</li>
                                <li class="py-0.5">Administrator dapat melihat daftar post/pengumuman</li>
                                <li class="py-0.5">Administrator dapat membuat/mengirim post/pengumuman (bulkDelete)</li>
                                <li class="py-0.5">Administrator dapat membuat/mengirim post/pengumuman (bulkPublish)</li>
                                <li class="py-0.5">Administrator dapat melihat post/pengumuman (trash)</li>
                                <li class="py-0.5">Administrator dapat melihat detail post/pengumuman</li>
                                <li class="py-0.5">Administrator dapat mengubah post/pengumuman</li>
                                <li class="py-0.5">Administrator dapat menghapus post/pengumuman</li>
                                <li class="py-0.5">Administrator dapat membuat/mengirim post/pengumuman (cancelSchedule)</li>
                                <li class="py-0.5">Administrator dapat menghapus permanen post/pengumuman</li>
                                <li class="py-0.5">Administrator dapat mempublikasikan post/pengumuman</li>
                                <li class="py-0.5">Administrator dapat memulihkan post/pengumuman</li>
                                <li class="py-0.5">Administrator dapat membuat/mengirim post/pengumuman (schedule)</li>
                                <li class="py-0.5">Administrator dapat membuat/mengirim post/pengumuman (togglePin)</li>
                                <li class="py-0.5">Administrator dapat membatalkan publikasi post/pengumuman</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/admin/posts</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/admin/posts</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/admin/posts/bulk-delete</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/admin/posts/bulk-publish</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/admin/posts/trash</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/admin/posts/{uuid}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/admin/posts/{uuid}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/admin/posts/{uuid}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/admin/posts/{uuid}/cancel-schedule</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/admin/posts/{uuid}/force</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/admin/posts/{uuid}/publish</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/admin/posts/{uuid}/restore</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/admin/posts/{uuid}/schedule</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/admin/posts/{uuid}/toggle-pin</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/admin/posts/{uuid}/unpublish</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">3</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Media Post</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Administrator dapat membuat/mengirim post/pengumuman (uploadImage)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/admin/posts/upload-image</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">4</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Manajemen Pengumuman</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Administrator dapat menambah pengumuman</li>
                                <li class="py-0.5">Administrator dapat mengubah pengumuman</li>
                                <li class="py-0.5">Administrator dapat menghapus pengumuman</li>
                                <li class="py-0.5">Administrator dapat mempublikasikan pengumuman</li>
                                <li class="py-0.5">Administrator dapat membuat/mengirim pengumuman (schedule)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/announcements</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/announcements/{announcement}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/announcements/{announcement}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/announcements/{announcement}/publish</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/announcements/{announcement}/schedule</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">5</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Tugas</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Administrator dapat menambah tugas</li>
                                <li class="py-0.5">Administrator dapat mengubah tugas</li>
                                <li class="py-0.5">Administrator dapat menghapus tugas</li>
                                <li class="py-0.5">Administrator dapat mengarsipkan tugas</li>
                                <li class="py-0.5">Administrator dapat membuat/mengirim tugas (duplicate)</li>
                                <li class="py-0.5">Administrator dapat mempublikasikan tugas</li>
                                <li class="py-0.5">Administrator dapat membatalkan publikasi tugas</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/assignments</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/assignments/{assignment}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/assignments/{assignment}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/assignments/{assignment}/archived</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/assignments/{assignment}/duplicate</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/assignments/{assignment}/publish</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/assignments/{assignment}/unpublish</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">6</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Submisi Tugas</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Administrator dapat mencari submisi</li>
                                <li class="py-0.5">Administrator dapat membuat/mengirim submisi (grade)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/submissions/search</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/submissions/{submission}/grades</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">7</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Log Audit</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Administrator dapat melihat daftar log audit</li>
                                <li class="py-0.5">Administrator dapat melihat log audit (actions)</li>
                                <li class="py-0.5">Administrator dapat melihat detail log audit</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/audit-logs</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/audit-logs/meta/actions</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/audit-logs/{id}</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">8</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Manajemen Badge</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Administrator dapat menambah badge</li>
                                <li class="py-0.5">Administrator dapat mengubah badge</li>
                                <li class="py-0.5">Administrator dapat menghapus badge</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/badges</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/badges/{badge}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/badges/{badge}</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">9</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Manajemen Kategori</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Administrator dapat menambah kategori</li>
                                <li class="py-0.5">Administrator dapat mengubah kategori</li>
                                <li class="py-0.5">Administrator dapat menghapus kategori</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/categories</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/categories/{category}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/categories/{category}</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">10</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Persetujuan Konten</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Administrator dapat melihat daftar konten menunggu review</li>
                                <li class="py-0.5">Administrator dapat menyetujui konten</li>
                                <li class="py-0.5">Administrator dapat menolak konten</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/content/pending-review</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/content/{type}/{id}/approve</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/content/{type}/{id}/reject</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">11</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Manajemen Kursus</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Administrator dapat menambah kursus</li>
                                <li class="py-0.5">Administrator dapat generate slug kursus</li>
                                <li class="py-0.5">Administrator dapat mengubah kursus</li>
                                <li class="py-0.5">Administrator dapat menghapus kursus</li>
                                <li class="py-0.5">Administrator dapat mengarsipkan kursus</li>
                                <li class="py-0.5">Administrator dapat mengubah kunci enrollment</li>
                                <li class="py-0.5">Administrator dapat menghapus kunci enrollment</li>
                                <li class="py-0.5">Administrator dapat generate kunci enrollment</li>
                                <li class="py-0.5">Administrator dapat mempublikasikan kursus</li>
                                <li class="py-0.5">Administrator dapat mengubah kursus (unarchive)</li>
                                <li class="py-0.5">Administrator dapat membatalkan publikasi kursus</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/courses</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/courses/generate-slug</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/archive</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/enrollment-key</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/enrollment-key</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/enrollment-key/generate</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/publish</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/unarchive</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/unpublish</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">12</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Pengumuman Kursus</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Administrator dapat menambah kursus</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/announcements</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">13</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Asesmen</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Administrator dapat melihat daftar kursus</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/assessments</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">14</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Manajemen Enrollment</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Administrator dapat menyetujui enrollment secara massal</li>
                                <li class="py-0.5">Administrator dapat membuat enrollment manual</li>
                                <li class="py-0.5">Administrator dapat menolak enrollment secara massal</li>
                                <li class="py-0.5">Administrator dapat menghapus enrollment secara massal</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/enrollments/approve/bulk</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/enrollments/create</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/enrollments/decline/bulk</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/enrollments/remove/bulk</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">15</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Laporan Enrollment</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Administrator dapat melihat kursus (exportEnrollmentsCsv)</li>
                                <li class="py-0.5">Administrator dapat melihat kursus (courseCompletionRate)</li>
                                <li class="py-0.5">Administrator dapat melihat laporan (enrollmentFunnel)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/exports/enrollments-csv</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/reports/completion-rate</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/reports/enrollment-funnel</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">16</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Kuis</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Administrator dapat menambah kuis</li>
                                <li class="py-0.5">Administrator dapat mengubah kuis</li>
                                <li class="py-0.5">Administrator dapat menghapus kuis</li>
                                <li class="py-0.5">Administrator dapat mengarsipkan kuis</li>
                                <li class="py-0.5">Administrator dapat mempublikasikan kuis</li>
                                <li class="py-0.5">Administrator dapat membuat/mengirim kuis (addQuestion)</li>
                                <li class="py-0.5">Administrator dapat membuat/mengirim kuis (reorderQuestions)</li>
                                <li class="py-0.5">Administrator dapat melihat kuis (showQuestion)</li>
                                <li class="py-0.5">Administrator dapat mengubah kuis (updateQuestion)</li>
                                <li class="py-0.5">Administrator dapat menghapus kuis (deleteQuestion)</li>
                                <li class="py-0.5">Administrator dapat membatalkan publikasi kuis</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/quizzes</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/quizzes/{quiz}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/quizzes/{quiz}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/quizzes/{quiz}/archived</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/quizzes/{quiz}/publish</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/quizzes/{quiz}/questions</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/quizzes/{quiz}/questions/reorder</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/quizzes/{quiz}/questions/{question}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/quizzes/{quiz}/questions/{question}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/quizzes/{quiz}/questions/{question}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/quizzes/{quiz}/unpublish</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">17</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Unit Kompetensi</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Administrator dapat menambah kursus</li>
                                <li class="py-0.5">Administrator dapat mengubah kursus (reorder)</li>
                                <li class="py-0.5">Administrator dapat mengubah kursus</li>
                                <li class="py-0.5">Administrator dapat menghapus kursus</li>
                                <li class="py-0.5">Administrator dapat melihat kursus (getContentOrder)</li>
                                <li class="py-0.5">Administrator dapat mengubah kursus (reorderContent)</li>
                                <li class="py-0.5">Administrator dapat membuat/mengirim kursus (storeContent)</li>
                                <li class="py-0.5">Administrator dapat mempublikasikan kursus</li>
                                <li class="py-0.5">Administrator dapat membatalkan publikasi kursus</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/reorder</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}/content-order</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}/content-order</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}/contents</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}/publish</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}/unpublish</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">18</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Manajemen Materi</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Administrator dapat menambah kursus</li>
                                <li class="py-0.5">Administrator dapat mengubah kursus</li>
                                <li class="py-0.5">Administrator dapat menghapus kursus</li>
                                <li class="py-0.5">Administrator dapat mempublikasikan kursus</li>
                                <li class="py-0.5">Administrator dapat membatalkan publikasi kursus</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}/lessons</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}/lessons/{lesson}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}/lessons/{lesson}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}/lessons/{lesson}/publish</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}/lessons/{lesson}/unpublish</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">19</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">LessonBlockController</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Administrator dapat menambah kursus</li>
                                <li class="py-0.5">Administrator dapat mengubah kursus</li>
                                <li class="py-0.5">Administrator dapat menghapus kursus</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}/lessons/{lesson}/blocks</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}/lessons/{lesson}/blocks/{block}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}/lessons/{lesson}/blocks/{block}</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">20</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Manajemen Pengguna</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Administrator dapat melihat aktivitas terbaru pengguna</li>
                                <li class="py-0.5">Administrator dapat melihat daftar pengguna</li>
                                <li class="py-0.5">Administrator dapat menambah pengguna</li>
                                <li class="py-0.5">Administrator dapat melihat detail pengguna</li>
                                <li class="py-0.5">Administrator dapat mengubah pengguna</li>
                                <li class="py-0.5">Administrator dapat menghapus pengguna</li>
                                <li class="py-0.5">Administrator dapat melihat skema yang ditugaskan ke pengguna</li>
                                <li class="py-0.5">Administrator dapat melihat kursus terdaftar pengguna</li>
                                <li class="py-0.5">Administrator dapat mereset password pengguna</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/user/{user}/latest-activity</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/users</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/users</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/users/{user}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/users/{user}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/users/{user}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/users/{user}/assigned-schemes</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/users/{user}/enrolled-course</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/users/{user}/reset-password</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">21</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Penilaian</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Administrator dapat melihat antrian penilaian</li>
                                <li class="py-0.5">Administrator dapat memberikan feedback penilaian secara massal</li>
                                <li class="py-0.5">Administrator dapat merilis nilai penilaian secara massal</li>
                                <li class="py-0.5">Administrator dapat melihat detail penilaian</li>
                                <li class="py-0.5">Administrator dapat membuat/mengirim submisi (manualGrade)</li>
                                <li class="py-0.5">Administrator dapat mengubah submisi (overrideGrade)</li>
                                <li class="py-0.5">Administrator dapat mengubah submisi (saveDraftGrade)</li>
                                <li class="py-0.5">Administrator dapat melihat submisi (getDraftGrade)</li>
                                <li class="py-0.5">Administrator dapat mengubah submisi (releaseGrade)</li>
                                <li class="py-0.5">Administrator dapat mengubah submisi (returnToQueue)</li>
                                <li class="py-0.5">Administrator dapat melihat submisi (gradingStatus)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/grading</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/grading/bulk-feedback</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/grading/bulk-release</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/grading/{submission}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/submissions/{submission}/grades</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-cyan-100 text-cyan-700 font-medium">PATCH</span> <span class="endpoint-badge text-gray-600">api/v1/submissions/{submission}/grades</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/submissions/{submission}/grades/draft</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/submissions/{submission}/grades/draft</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-cyan-100 text-cyan-700 font-medium">PATCH</span> <span class="endpoint-badge text-gray-600">api/v1/submissions/{submission}/grades/release</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-cyan-100 text-cyan-700 font-medium">PATCH</span> <span class="endpoint-badge text-gray-600">api/v1/submissions/{submission}/grades/return-to-queue</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/submissions/{submission}/grades/status</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">22</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Konfigurasi Level</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Administrator dapat menambah konfigurasi level</li>
                                <li class="py-0.5">Administrator dapat mengubah konfigurasi level</li>
                                <li class="py-0.5">Administrator dapat menghapus konfigurasi level</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/level-configs</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/level-configs/{level_config}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/level-configs/{level_config}</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">23</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Manajemen Level</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Administrator dapat melihat statistik level</li>
                                <li class="py-0.5">Administrator dapat sinkronisasi level</li>
                                <li class="py-0.5">Administrator dapat mengubah level</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/levels/statistics</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/levels/sync</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/levels/{id}</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">24</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Master Data</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Administrator dapat melihat master data (students)</li>
                                <li class="py-0.5">Administrator dapat menambah master data</li>
                                <li class="py-0.5">Administrator dapat mengubah master data</li>
                                <li class="py-0.5">Administrator dapat menghapus master data</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/master-data/students</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/master-data/{type}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/master-data/{type}/{id}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/master-data/{type}/{id}</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">25</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Tempat Sampah</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Administrator dapat melihat master data (masterSourceTypes)</li>
                                <li class="py-0.5">Administrator dapat melihat daftar tempat sampah</li>
                                <li class="py-0.5">Administrator dapat mengubah tempat sampah (restoreAll)</li>
                                <li class="py-0.5">Administrator dapat menghapus permanen semua tempat sampah</li>
                                <li class="py-0.5">Administrator dapat menghapus permanen tempat sampah secara massal</li>
                                <li class="py-0.5">Administrator dapat memulihkan tempat sampah secara massal</li>
                                <li class="py-0.5">Administrator dapat melihat jenis sumber tempat sampah</li>
                                <li class="py-0.5">Administrator dapat memulihkan tempat sampah</li>
                                <li class="py-0.5">Administrator dapat menghapus permanen tempat sampah</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/master-data/trash-bin-source-types</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/trash-bins</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-cyan-100 text-cyan-700 font-medium">PATCH</span> <span class="endpoint-badge text-gray-600">api/v1/trash-bins</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/trash-bins</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/trash-bins/bulk</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-cyan-100 text-cyan-700 font-medium">PATCH</span> <span class="endpoint-badge text-gray-600">api/v1/trash-bins/bulk/restore</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/trash-bins/source-types</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-cyan-100 text-cyan-700 font-medium">PATCH</span> <span class="endpoint-badge text-gray-600">api/v1/trash-bins/{trashBinId}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/trash-bins/{trashBinId}</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">26</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Metrik Gamifikasi</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Administrator dapat melihat daftar metrik</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/metrics</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">27</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Manajemen Berita</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Administrator dapat menambah berita</li>
                                <li class="py-0.5">Administrator dapat mengubah berita</li>
                                <li class="py-0.5">Administrator dapat menghapus berita</li>
                                <li class="py-0.5">Administrator dapat mempublikasikan berita</li>
                                <li class="py-0.5">Administrator dapat membuat/mengirim berita (schedule)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/news</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/news/{news}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/news/{news}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/news/{news}/publish</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/news/{news}/schedule</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">28</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Operasi Sistem</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Administrator dapat melihat daftar operasi</li>
                                <li class="py-0.5">Administrator dapat menambah operasi</li>
                                <li class="py-0.5">Administrator dapat melihat detail operasi</li>
                                <li class="py-0.5">Administrator dapat mengubah operasi</li>
                                <li class="py-0.5">Administrator dapat menghapus operasi</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/operations</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/operations</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/operations/{operation}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/operations/{operation}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/operations/{operation}</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">29</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">TagController</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Administrator dapat menambah tags</li>
                                <li class="py-0.5">Administrator dapat mengubah tags</li>
                                <li class="py-0.5">Administrator dapat menghapus tags</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/tags</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/tags/{tag}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/tags/{tag}</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">30</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Operasi Massal Pengguna</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Administrator dapat mengaktifkan pengguna secara massal</li>
                                <li class="py-0.5">Administrator dapat menonaktifkan pengguna secara massal</li>
                                <li class="py-0.5">Administrator dapat menghapus pengguna</li>
                                <li class="py-0.5">Administrator dapat mengekspor data pengguna</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/users/bulk/activate</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/users/bulk/deactivate</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/users/bulk/delete</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/users/bulk/export</span></li>
                                </ul>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
        <section id="instruktur" class="mb-12">
            <h2 class="text-2xl font-bold mb-4 text-gray-900">Kebutuhan Fungsional — Instruktur</h2>
            <div class="overflow-x-auto rounded-lg shadow">
                <table class="w-full border-collapse bg-white">
                    <thead>
                        <tr class="bg-blue-600 text-white">
                            <th class="px-4 py-3 text-left text-sm font-semibold w-12">No</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold w-48">Fitur</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Detail</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold w-96">Endpoint</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">1</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Post/Pengumuman Notifikasi</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Instruktur dapat melihat daftar post/pengumuman</li>
                                <li class="py-0.5">Instruktur dapat melihat post/pengumuman (pinned)</li>
                                <li class="py-0.5">Instruktur dapat melihat detail post/pengumuman</li>
                                <li class="py-0.5">Instruktur dapat membuat/mengirim post/pengumuman (markAsViewed)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/posts</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/posts/pinned</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/posts/{uuid}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/posts/{uuid}/view</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">2</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Manajemen Pengumuman</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Instruktur dapat melihat daftar pengumuman</li>
                                <li class="py-0.5">Instruktur dapat menambah pengumuman</li>
                                <li class="py-0.5">Instruktur dapat melihat detail pengumuman</li>
                                <li class="py-0.5">Instruktur dapat mengubah pengumuman</li>
                                <li class="py-0.5">Instruktur dapat menghapus pengumuman</li>
                                <li class="py-0.5">Instruktur dapat mempublikasikan pengumuman</li>
                                <li class="py-0.5">Instruktur dapat membuat/mengirim pengumuman (markAsRead)</li>
                                <li class="py-0.5">Instruktur dapat membuat/mengirim pengumuman (schedule)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/announcements</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/announcements</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/announcements/{announcement}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/announcements/{announcement}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/announcements/{announcement}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/announcements/{announcement}/publish</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/announcements/{announcement}/read</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/announcements/{announcement}/schedule</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">3</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Tugas</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Instruktur dapat menambah tugas</li>
                                <li class="py-0.5">Instruktur dapat melihat detail tugas</li>
                                <li class="py-0.5">Instruktur dapat mengubah tugas</li>
                                <li class="py-0.5">Instruktur dapat menghapus tugas</li>
                                <li class="py-0.5">Instruktur dapat mengarsipkan tugas</li>
                                <li class="py-0.5">Instruktur dapat membuat/mengirim tugas (duplicate)</li>
                                <li class="py-0.5">Instruktur dapat melihat tugas (checkPrerequisites)</li>
                                <li class="py-0.5">Instruktur dapat mempublikasikan tugas</li>
                                <li class="py-0.5">Instruktur dapat membatalkan publikasi tugas</li>
                                <li class="py-0.5">Instruktur dapat melihat daftar kursus</li>
                                <li class="py-0.5">Instruktur dapat melihat daftar tugas belum selesai</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/assignments</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/assignments/{assignment}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/assignments/{assignment}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/assignments/{assignment}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/assignments/{assignment}/archived</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/assignments/{assignment}/duplicate</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/assignments/{assignment}/prerequisites/check</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/assignments/{assignment}/publish</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/assignments/{assignment}/unpublish</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/assignments</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/assignments/incomplete</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">4</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Submisi Tugas</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Instruktur dapat melihat daftar tugas</li>
                                <li class="py-0.5">Instruktur dapat menambah tugas</li>
                                <li class="py-0.5">Instruktur dapat melihat tugas (highestSubmission)</li>
                                <li class="py-0.5">Instruktur dapat melihat tugas (showForAssignment)</li>
                                <li class="py-0.5">Instruktur dapat mencari submisi</li>
                                <li class="py-0.5">Instruktur dapat mengubah submisi</li>
                                <li class="py-0.5">Instruktur dapat membuat/mengirim submisi (saveAnswer)</li>
                                <li class="py-0.5">Instruktur dapat membuat/mengirim submisi (grade)</li>
                                <li class="py-0.5">Instruktur dapat mengirim submisi untuk review</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/assignments/{assignment}/submissions</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/assignments/{assignment}/submissions</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/assignments/{assignment}/submissions/highest</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/assignments/{assignment}/submissions/{submission}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/submissions/search</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/submissions/{submission}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/submissions/{submission}/answers</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/submissions/{submission}/grades</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/submissions/{submission}/submit</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">5</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Otentikasi</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Instruktur dapat membuat/mengirim autentikasi (sendEmailVerification)</li>
                                <li class="py-0.5">Instruktur dapat melakukan logout</li>
                                <li class="py-0.5">Instruktur dapat membuat/mengirim autentikasi (setPassword)</li>
                                <li class="py-0.5">Instruktur dapat membuat/mengirim autentikasi (setUsername)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/auth/email/verify/send</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/auth/logout</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/auth/set-password</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/auth/set-username</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">6</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Reset Password</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Instruktur dapat membuat/mengirim autentikasi (changePassword)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/auth/password/change</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">7</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Manajemen Badge</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Instruktur dapat melihat daftar badge</li>
                                <li class="py-0.5">Instruktur dapat melihat badge (available)</li>
                                <li class="py-0.5">Instruktur dapat melihat detail badge</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/badges</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/badges/available</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/badges/{badge}</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">8</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Persetujuan Konten</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Instruktur dapat melihat daftar konten menunggu review</li>
                                <li class="py-0.5">Instruktur dapat menyetujui konten</li>
                                <li class="py-0.5">Instruktur dapat menolak konten</li>
                                <li class="py-0.5">Instruktur dapat mengirim konten untuk review</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/content/pending-review</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/content/{type}/{id}/approve</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/content/{type}/{id}/reject</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/content/{type}/{id}/submit</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">9</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Pencarian Konten</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Instruktur dapat mencari konten</li>
                                <li class="py-0.5">Instruktur dapat melihat pencarian (getSearchHistory)</li>
                                <li class="py-0.5">Instruktur dapat menghapus pencarian (clearSearchHistory)</li>
                                <li class="py-0.5">Instruktur dapat menghapus pencarian (deleteHistoryItem)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/content/search</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/search/history</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/search/history</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/search/history/{id}</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">10</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Statistik Konten</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Instruktur dapat melihat daftar konten</li>
                                <li class="py-0.5">Instruktur dapat melihat statistik pengumuman</li>
                                <li class="py-0.5">Instruktur dapat melihat konten paling banyak dilihat</li>
                                <li class="py-0.5">Instruktur dapat melihat statistik berita</li>
                                <li class="py-0.5">Instruktur dapat melihat konten trending</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/content/statistics</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/content/statistics/announcements/{announcement}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/content/statistics/most-viewed</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/content/statistics/news/{news}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/content/statistics/trending</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">11</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Manajemen Kursus</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Instruktur dapat menambah kursus</li>
                                <li class="py-0.5">Instruktur dapat generate slug kursus</li>
                                <li class="py-0.5">Instruktur dapat mengubah kursus</li>
                                <li class="py-0.5">Instruktur dapat menghapus kursus</li>
                                <li class="py-0.5">Instruktur dapat mengarsipkan kursus</li>
                                <li class="py-0.5">Instruktur dapat mengubah kunci enrollment</li>
                                <li class="py-0.5">Instruktur dapat menghapus kunci enrollment</li>
                                <li class="py-0.5">Instruktur dapat generate kunci enrollment</li>
                                <li class="py-0.5">Instruktur dapat mempublikasikan kursus</li>
                                <li class="py-0.5">Instruktur dapat mengubah kursus (unarchive)</li>
                                <li class="py-0.5">Instruktur dapat membatalkan publikasi kursus</li>
                                <li class="py-0.5">Instruktur dapat melihat my-courses (myEnrolledCourses)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/courses</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/courses/generate-slug</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/archive</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/enrollment-key</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/enrollment-key</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/enrollment-key/generate</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/publish</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/unarchive</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/unpublish</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/my-courses</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">12</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Pengumuman Kursus</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Instruktur dapat melihat daftar kursus</li>
                                <li class="py-0.5">Instruktur dapat menambah kursus</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/announcements</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/announcements</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">13</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Asesmen</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Instruktur dapat melihat daftar kursus</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/assessments</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">14</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Manajemen Enrollment</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Instruktur dapat membatalkan pendaftaran kursus</li>
                                <li class="py-0.5">Instruktur dapat melihat kursus (indexByCourse)</li>
                                <li class="py-0.5">Instruktur dapat melihat kursus (showByCourse)</li>
                                <li class="py-0.5">Instruktur dapat membuat/mengirim kursus (withdraw)</li>
                                <li class="py-0.5">Instruktur dapat melihat daftar enrollment</li>
                                <li class="py-0.5">Instruktur dapat menyetujui enrollment secara massal</li>
                                <li class="py-0.5">Instruktur dapat membuat enrollment manual</li>
                                <li class="py-0.5">Instruktur dapat menolak enrollment secara massal</li>
                                <li class="py-0.5">Instruktur dapat menghapus enrollment secara massal</li>
                                <li class="py-0.5">Instruktur dapat melihat detail enrollment</li>
                                <li class="py-0.5">Instruktur dapat menyetujui enrollment</li>
                                <li class="py-0.5">Instruktur dapat menolak enrollment</li>
                                <li class="py-0.5">Instruktur dapat menghapus enrollment</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/cancel</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/enrollments</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/enrollments/{enrollment}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/withdraw</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/enrollments</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/enrollments/approve/bulk</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/enrollments/create</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/enrollments/decline/bulk</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/enrollments/remove/bulk</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/enrollments/{enrollment}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/enrollments/{enrollment}/approve</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/enrollments/{enrollment}/decline</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/enrollments/{enrollment}/remove</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">15</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Laporan Enrollment</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Instruktur dapat melihat kursus (exportEnrollmentsCsv)</li>
                                <li class="py-0.5">Instruktur dapat melihat kursus (courseCompletionRate)</li>
                                <li class="py-0.5">Instruktur dapat melihat laporan (enrollmentFunnel)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/exports/enrollments-csv</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/reports/completion-rate</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/reports/enrollment-funnel</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">16</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Statistik Forum</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Instruktur dapat melihat kursus (userStats)</li>
                                <li class="py-0.5">Instruktur dapat melihat daftar kursus</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/my-statistics</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/statistics</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">17</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Manajemen Thread Forum</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Instruktur dapat melihat daftar kursus</li>
                                <li class="py-0.5">Instruktur dapat menambah kursus</li>
                                <li class="py-0.5">Instruktur dapat melihat detail kursus</li>
                                <li class="py-0.5">Instruktur dapat mengubah kursus</li>
                                <li class="py-0.5">Instruktur dapat menghapus kursus</li>
                                <li class="py-0.5">Instruktur dapat mengubah kursus (close)</li>
                                <li class="py-0.5">Instruktur dapat mengubah kursus (open)</li>
                                <li class="py-0.5">Instruktur dapat mengubah kursus (pin)</li>
                                <li class="py-0.5">Instruktur dapat mengubah kursus (resolve)</li>
                                <li class="py-0.5">Instruktur dapat mengubah kursus (unpin)</li>
                                <li class="py-0.5">Instruktur dapat mengubah kursus (unresolve)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads/{thread}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-cyan-100 text-cyan-700 font-medium">PATCH</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads/{thread}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads/{thread}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-cyan-100 text-cyan-700 font-medium">PATCH</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads/{thread}/close</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-cyan-100 text-cyan-700 font-medium">PATCH</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads/{thread}/open</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-cyan-100 text-cyan-700 font-medium">PATCH</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads/{thread}/pin</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-cyan-100 text-cyan-700 font-medium">PATCH</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads/{thread}/resolve</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-cyan-100 text-cyan-700 font-medium">PATCH</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads/{thread}/unpin</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-cyan-100 text-cyan-700 font-medium">PATCH</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads/{thread}/unresolve</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">18</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Reaksi Forum</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Instruktur dapat membuat/mengirim kursus (storeThreadReaction)</li>
                                <li class="py-0.5">Instruktur dapat menghapus kursus (destroyThreadReaction)</li>
                                <li class="py-0.5">Instruktur dapat membuat/mengirim kursus (storeReplyReaction)</li>
                                <li class="py-0.5">Instruktur dapat menghapus kursus (destroyReplyReaction)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads/{thread}/reactions</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads/{thread}/reactions/{reaction}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads/{thread}/replies/{reply}/reactions</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads/{thread}/replies/{reply}/reactions/{reaction}</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">19</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Balasan Forum</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Instruktur dapat melihat daftar kursus</li>
                                <li class="py-0.5">Instruktur dapat menambah kursus</li>
                                <li class="py-0.5">Instruktur dapat mengubah kursus</li>
                                <li class="py-0.5">Instruktur dapat menghapus kursus</li>
                                <li class="py-0.5">Instruktur dapat mengubah kursus (accept)</li>
                                <li class="py-0.5">Instruktur dapat melihat kursus (children)</li>
                                <li class="py-0.5">Instruktur dapat mengubah kursus (unaccept)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads/{thread}/replies</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads/{thread}/replies</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-cyan-100 text-cyan-700 font-medium">PATCH</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads/{thread}/replies/{reply}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads/{thread}/replies/{reply}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-cyan-100 text-cyan-700 font-medium">PATCH</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads/{thread}/replies/{reply}/accept</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads/{thread}/replies/{reply}/children</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-cyan-100 text-cyan-700 font-medium">PATCH</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads/{thread}/replies/{reply}/unaccept</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">20</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">ProgressController</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Instruktur dapat melihat detail kursus</li>
                                <li class="py-0.5">Instruktur dapat membuat/mengirim kursus (completeLesson)</li>
                                <li class="py-0.5">Instruktur dapat membuat/mengirim kursus (uncompleteLesson)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/progress</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}/lessons/{lesson}/complete</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}/lessons/{lesson}/uncomplete</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">21</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Kuis</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Instruktur dapat melihat daftar kursus</li>
                                <li class="py-0.5">Instruktur dapat menambah kuis</li>
                                <li class="py-0.5">Instruktur dapat melihat detail kuis</li>
                                <li class="py-0.5">Instruktur dapat mengubah kuis</li>
                                <li class="py-0.5">Instruktur dapat menghapus kuis</li>
                                <li class="py-0.5">Instruktur dapat mengarsipkan kuis</li>
                                <li class="py-0.5">Instruktur dapat mempublikasikan kuis</li>
                                <li class="py-0.5">Instruktur dapat melihat kuis (listQuestions)</li>
                                <li class="py-0.5">Instruktur dapat membuat/mengirim kuis (addQuestion)</li>
                                <li class="py-0.5">Instruktur dapat membuat/mengirim kuis (reorderQuestions)</li>
                                <li class="py-0.5">Instruktur dapat melihat kuis (showQuestion)</li>
                                <li class="py-0.5">Instruktur dapat mengubah kuis (updateQuestion)</li>
                                <li class="py-0.5">Instruktur dapat menghapus kuis (deleteQuestion)</li>
                                <li class="py-0.5">Instruktur dapat membatalkan publikasi kuis</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/quizzes</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/quizzes</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/quizzes/{quiz}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/quizzes/{quiz}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/quizzes/{quiz}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/quizzes/{quiz}/archived</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/quizzes/{quiz}/publish</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/quizzes/{quiz}/questions</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/quizzes/{quiz}/questions</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/quizzes/{quiz}/questions/reorder</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/quizzes/{quiz}/questions/{question}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/quizzes/{quiz}/questions/{question}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/quizzes/{quiz}/questions/{question}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/quizzes/{quiz}/unpublish</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">22</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Unit Kompetensi</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Instruktur dapat menambah kursus</li>
                                <li class="py-0.5">Instruktur dapat mengubah kursus (reorder)</li>
                                <li class="py-0.5">Instruktur dapat melihat detail kursus</li>
                                <li class="py-0.5">Instruktur dapat mengubah kursus</li>
                                <li class="py-0.5">Instruktur dapat menghapus kursus</li>
                                <li class="py-0.5">Instruktur dapat melihat kursus (getContentOrder)</li>
                                <li class="py-0.5">Instruktur dapat mengubah kursus (reorderContent)</li>
                                <li class="py-0.5">Instruktur dapat melihat kursus (contents)</li>
                                <li class="py-0.5">Instruktur dapat membuat/mengirim kursus (storeContent)</li>
                                <li class="py-0.5">Instruktur dapat mempublikasikan kursus</li>
                                <li class="py-0.5">Instruktur dapat membatalkan publikasi kursus</li>
                                <li class="py-0.5">Instruktur dapat melihat daftar semua unit kompetensi</li>
                                <li class="py-0.5">Instruktur dapat melihat detail unit kompetensi</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/reorder</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}/content-order</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}/content-order</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}/contents</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}/contents</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}/publish</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}/unpublish</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/units</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/units/{unit}</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">23</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Manajemen Materi</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Instruktur dapat melihat daftar kursus</li>
                                <li class="py-0.5">Instruktur dapat menambah kursus</li>
                                <li class="py-0.5">Instruktur dapat melihat detail kursus</li>
                                <li class="py-0.5">Instruktur dapat mengubah kursus</li>
                                <li class="py-0.5">Instruktur dapat menghapus kursus</li>
                                <li class="py-0.5">Instruktur dapat mempublikasikan kursus</li>
                                <li class="py-0.5">Instruktur dapat membatalkan publikasi kursus</li>
                                <li class="py-0.5">Instruktur dapat melihat daftar semua materi pelajaran</li>
                                <li class="py-0.5">Instruktur dapat melihat detail materi pelajaran</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}/lessons</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}/lessons</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}/lessons/{lesson}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}/lessons/{lesson}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}/lessons/{lesson}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}/lessons/{lesson}/publish</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}/lessons/{lesson}/unpublish</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/lessons</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/lessons/{lesson}</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">24</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">LessonBlockController</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Instruktur dapat melihat daftar kursus</li>
                                <li class="py-0.5">Instruktur dapat menambah kursus</li>
                                <li class="py-0.5">Instruktur dapat melihat detail kursus</li>
                                <li class="py-0.5">Instruktur dapat mengubah kursus</li>
                                <li class="py-0.5">Instruktur dapat menghapus kursus</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}/lessons/{lesson}/blocks</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}/lessons/{lesson}/blocks</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}/lessons/{lesson}/blocks/{block}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}/lessons/{lesson}/blocks/{block}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}/lessons/{lesson}/blocks/{block}</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">25</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Manajemen Pengguna</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Instruktur dapat melihat kursus (searchMentions)</li>
                                <li class="py-0.5">Instruktur dapat melihat daftar pengguna</li>
                                <li class="py-0.5">Instruktur dapat melihat detail pengguna</li>
                                <li class="py-0.5">Instruktur dapat melihat skema yang ditugaskan ke pengguna</li>
                                <li class="py-0.5">Instruktur dapat melihat kursus terdaftar pengguna</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/users/mentions</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/users</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/users/{user}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/users/{user}/assigned-schemes</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/users/{user}/enrolled-course</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">26</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Dashboard</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Instruktur dapat melihat daftar dashboard</li>
                                <li class="py-0.5">Instruktur dapat melihat dashboard (recentAchievements)</li>
                                <li class="py-0.5">Instruktur dapat melihat dashboard (recentLearning)</li>
                                <li class="py-0.5">Instruktur dapat melihat dashboard (recommendedCourses)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/dashboard</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/dashboard/recent-achievements</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/dashboard/recent-learning</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/dashboard/recommended-courses</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">27</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Dashboard Forum</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Instruktur dapat melihat thread forum sendiri</li>
                                <li class="py-0.5">Instruktur dapat melihat semua thread forum</li>
                                <li class="py-0.5">Instruktur dapat melihat thread forum trending</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/forums/my-threads</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/forums/threads</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/forums/threads/trending</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">28</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Penilaian</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Instruktur dapat melihat antrian penilaian</li>
                                <li class="py-0.5">Instruktur dapat memberikan feedback penilaian secara massal</li>
                                <li class="py-0.5">Instruktur dapat merilis nilai penilaian secara massal</li>
                                <li class="py-0.5">Instruktur dapat melihat detail penilaian</li>
                                <li class="py-0.5">Instruktur dapat membuat/mengirim submisi (manualGrade)</li>
                                <li class="py-0.5">Instruktur dapat mengubah submisi (overrideGrade)</li>
                                <li class="py-0.5">Instruktur dapat mengubah submisi (saveDraftGrade)</li>
                                <li class="py-0.5">Instruktur dapat melihat submisi (getDraftGrade)</li>
                                <li class="py-0.5">Instruktur dapat mengubah submisi (releaseGrade)</li>
                                <li class="py-0.5">Instruktur dapat mengubah submisi (returnToQueue)</li>
                                <li class="py-0.5">Instruktur dapat melihat submisi (gradingStatus)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/grading</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/grading/bulk-feedback</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/grading/bulk-release</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/grading/{submission}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/submissions/{submission}/grades</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-cyan-100 text-cyan-700 font-medium">PATCH</span> <span class="endpoint-badge text-gray-600">api/v1/submissions/{submission}/grades</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/submissions/{submission}/grades/draft</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/submissions/{submission}/grades/draft</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-cyan-100 text-cyan-700 font-medium">PATCH</span> <span class="endpoint-badge text-gray-600">api/v1/submissions/{submission}/grades/release</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-cyan-100 text-cyan-700 font-medium">PATCH</span> <span class="endpoint-badge text-gray-600">api/v1/submissions/{submission}/grades/return-to-queue</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/submissions/{submission}/grades/status</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">29</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Leaderboard</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Instruktur dapat melihat daftar leaderboard</li>
                                <li class="py-0.5">Instruktur dapat melihat riwayat poin pengguna</li>
                                <li class="py-0.5">Instruktur dapat melihat peringkat sendiri</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/leaderboards</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/leaderboards/{userId}/points-history</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/user/rank</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">30</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Penyelesaian Materi</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Instruktur dapat menandai materi pelajaran selesai</li>
                                <li class="py-0.5">Instruktur dapat menandai materi pelajaran belum selesai</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/lessons/{lesson}/complete</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/lessons/{lesson}/complete</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">31</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Manajemen Level</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Instruktur dapat melihat daftar level</li>
                                <li class="py-0.5">Instruktur dapat menghitung level</li>
                                <li class="py-0.5">Instruktur dapat melihat progres level</li>
                                <li class="py-0.5">Instruktur dapat melihat statistik XP harian</li>
                                <li class="py-0.5">Instruktur dapat melihat level pengguna</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/levels</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/levels/calculate</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/levels/progression</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/user/daily-xp-stats</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/user/level</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">32</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Master Data</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Instruktur dapat melihat master data (students)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/master-data/students</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">33</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Tempat Sampah</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Instruktur dapat melihat master data (masterSourceTypes)</li>
                                <li class="py-0.5">Instruktur dapat melihat daftar tempat sampah</li>
                                <li class="py-0.5">Instruktur dapat mengubah tempat sampah (restoreAll)</li>
                                <li class="py-0.5">Instruktur dapat menghapus permanen semua tempat sampah</li>
                                <li class="py-0.5">Instruktur dapat menghapus permanen tempat sampah secara massal</li>
                                <li class="py-0.5">Instruktur dapat memulihkan tempat sampah secara massal</li>
                                <li class="py-0.5">Instruktur dapat melihat jenis sumber tempat sampah</li>
                                <li class="py-0.5">Instruktur dapat memulihkan tempat sampah</li>
                                <li class="py-0.5">Instruktur dapat menghapus permanen tempat sampah</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/master-data/trash-bin-source-types</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/trash-bins</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-cyan-100 text-cyan-700 font-medium">PATCH</span> <span class="endpoint-badge text-gray-600">api/v1/trash-bins</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/trash-bins</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/trash-bins/bulk</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-cyan-100 text-cyan-700 font-medium">PATCH</span> <span class="endpoint-badge text-gray-600">api/v1/trash-bins/bulk/restore</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/trash-bins/source-types</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-cyan-100 text-cyan-700 font-medium">PATCH</span> <span class="endpoint-badge text-gray-600">api/v1/trash-bins/{trashBinId}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/trash-bins/{trashBinId}</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">34</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Manajemen Media</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Instruktur dapat membuat/mengirim media (upload)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/media/upload</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">35</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Manajemen Berita</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Instruktur dapat melihat daftar berita</li>
                                <li class="py-0.5">Instruktur dapat menambah berita</li>
                                <li class="py-0.5">Instruktur dapat melihat konten trending</li>
                                <li class="py-0.5">Instruktur dapat melihat detail berita</li>
                                <li class="py-0.5">Instruktur dapat mengubah berita</li>
                                <li class="py-0.5">Instruktur dapat menghapus berita</li>
                                <li class="py-0.5">Instruktur dapat mempublikasikan berita</li>
                                <li class="py-0.5">Instruktur dapat membuat/mengirim berita (schedule)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/news</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/news</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/news/trending</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/news/{news}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/news/{news}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/news/{news}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/news/{news}/publish</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/news/{news}/schedule</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">36</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Preferensi Notifikasi</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Instruktur dapat melihat daftar preferensi notifikasi</li>
                                <li class="py-0.5">Instruktur dapat mengubah preferensi notifikasi</li>
                                <li class="py-0.5">Instruktur dapat membuat/mengirim preferensi notifikasi (reset)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/notification-preferences</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/notification-preferences</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/notification-preferences/reset</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">37</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Notifikasi</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Instruktur dapat melihat daftar notifikasi</li>
                                <li class="py-0.5">Instruktur dapat menambah notifikasi</li>
                                <li class="py-0.5">Instruktur dapat melihat detail notifikasi</li>
                                <li class="py-0.5">Instruktur dapat mengubah notifikasi</li>
                                <li class="py-0.5">Instruktur dapat menghapus notifikasi</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/notifications</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/notifications</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/notifications/{notification}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/notifications/{notification}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/notifications/{notification}</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">38</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Manajemen Profil</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Instruktur dapat melihat daftar profil</li>
                                <li class="py-0.5">Instruktur dapat mengubah profil</li>
                                <li class="py-0.5">Instruktur dapat membuat/mengirim profil (deleteConfirm)</li>
                                <li class="py-0.5">Instruktur dapat membuat/mengirim profil (deleteRequest)</li>
                                <li class="py-0.5">Instruktur dapat memulihkan profil</li>
                                <li class="py-0.5">Instruktur dapat melihat daftar profil</li>
                                <li class="py-0.5">Instruktur dapat membuat/mengirim profil (uploadAvatar)</li>
                                <li class="py-0.5">Instruktur dapat menghapus profil (deleteAvatar)</li>
                                <li class="py-0.5">Instruktur dapat membuat/mengirim profil (requestEmailChange)</li>
                                <li class="py-0.5">Instruktur dapat membuat/mengirim profil (verifyEmailChange)</li>
                                <li class="py-0.5">Instruktur dapat mengubah profil</li>
                                <li class="py-0.5">Instruktur dapat melihat daftar profil</li>
                                <li class="py-0.5">Instruktur dapat mengubah profil</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/profile</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/profile</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/profile/account/delete/confirm</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/profile/account/delete/request</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/profile/account/restore</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/profile/activities</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/profile/avatar</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/profile/avatar</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/profile/email/change</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/profile/email/change/verify</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/profile/password</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/profile/privacy</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/profile/privacy</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">39</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Submisi Kuis</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Instruktur dapat melihat detail quiz-submissions</li>
                                <li class="py-0.5">Instruktur dapat membuat/mengirim quiz-submissions (saveAnswer)</li>
                                <li class="py-0.5">Instruktur dapat melihat quiz-submissions (listQuestions)</li>
                                <li class="py-0.5">Instruktur dapat melihat quiz-submissions (getQuestionAtOrder)</li>
                                <li class="py-0.5">Instruktur dapat mengirim quiz-submissions untuk review</li>
                                <li class="py-0.5">Instruktur dapat melihat daftar kuis</li>
                                <li class="py-0.5">Instruktur dapat melihat kuis (highestSubmission)</li>
                                <li class="py-0.5">Instruktur dapat membuat/mengirim kuis (start)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/quiz-submissions/{submission}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/quiz-submissions/{submission}/answers</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/quiz-submissions/{submission}/questions</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/quiz-submissions/{submission}/questions/{order}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/quiz-submissions/{submission}/submit</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/quizzes/{quiz}/submissions</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/quizzes/{quiz}/submissions/highest</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/quizzes/{quiz}/submissions/start</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">40</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">TagController</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Instruktur dapat menambah tags</li>
                                <li class="py-0.5">Instruktur dapat mengubah tags</li>
                                <li class="py-0.5">Instruktur dapat menghapus tags</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/tags</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/tags/{tag}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/tags/{tag}</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">41</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Gamifikasi Pengguna</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Instruktur dapat melihat badge pengguna</li>
                                <li class="py-0.5">Instruktur dapat melihat ringkasan pengguna</li>
                                <li class="py-0.5">Instruktur dapat melihat pengguna (unitLevels)</li>
                                <li class="py-0.5">Instruktur dapat melihat milestone pengguna</li>
                                <li class="py-0.5">Instruktur dapat melihat riwayat poin pengguna</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/user/badges</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/user/gamification-summary</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/user/levels/{slug}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/user/milestones</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/user/points-history</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">42</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Profil Publik</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Instruktur dapat melihat detail pengguna</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/users/{user}/profile</span></li>
                                </ul>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
        <section id="asesi" class="mb-12">
            <h2 class="text-2xl font-bold mb-4 text-gray-900">Kebutuhan Fungsional — Asesi (Student)</h2>
            <div class="overflow-x-auto rounded-lg shadow">
                <table class="w-full border-collapse bg-white">
                    <thead>
                        <tr class="bg-purple-600 text-white">
                            <th class="px-4 py-3 text-left text-sm font-semibold w-12">No</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold w-48">Fitur</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Detail</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold w-96">Endpoint</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">1</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Post/Pengumuman Notifikasi</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Asesi dapat melihat daftar post/pengumuman</li>
                                <li class="py-0.5">Asesi dapat melihat post/pengumuman (pinned)</li>
                                <li class="py-0.5">Asesi dapat melihat detail post/pengumuman</li>
                                <li class="py-0.5">Asesi dapat membuat/mengirim post/pengumuman (markAsViewed)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/posts</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/posts/pinned</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/posts/{uuid}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/posts/{uuid}/view</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">2</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Manajemen Pengumuman</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Asesi dapat melihat daftar pengumuman</li>
                                <li class="py-0.5">Asesi dapat melihat detail pengumuman</li>
                                <li class="py-0.5">Asesi dapat membuat/mengirim pengumuman (markAsRead)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/announcements</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/announcements/{announcement}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/announcements/{announcement}/read</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">3</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Tugas</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Asesi dapat melihat detail tugas</li>
                                <li class="py-0.5">Asesi dapat melihat tugas (checkPrerequisites)</li>
                                <li class="py-0.5">Asesi dapat melihat daftar kursus</li>
                                <li class="py-0.5">Asesi dapat melihat daftar tugas belum selesai</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/assignments/{assignment}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/assignments/{assignment}/prerequisites/check</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/assignments</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/assignments/incomplete</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">4</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Submisi Tugas</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Asesi dapat melihat daftar tugas</li>
                                <li class="py-0.5">Asesi dapat menambah tugas</li>
                                <li class="py-0.5">Asesi dapat melihat tugas (highestSubmission)</li>
                                <li class="py-0.5">Asesi dapat melihat tugas (showForAssignment)</li>
                                <li class="py-0.5">Asesi dapat mengubah submisi</li>
                                <li class="py-0.5">Asesi dapat membuat/mengirim submisi (saveAnswer)</li>
                                <li class="py-0.5">Asesi dapat mengirim submisi untuk review</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/assignments/{assignment}/submissions</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/assignments/{assignment}/submissions</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/assignments/{assignment}/submissions/highest</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/assignments/{assignment}/submissions/{submission}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/submissions/{submission}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/submissions/{submission}/answers</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/submissions/{submission}/submit</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">5</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Otentikasi</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Asesi dapat membuat/mengirim autentikasi (sendEmailVerification)</li>
                                <li class="py-0.5">Asesi dapat melakukan logout</li>
                                <li class="py-0.5">Asesi dapat membuat/mengirim autentikasi (setPassword)</li>
                                <li class="py-0.5">Asesi dapat membuat/mengirim autentikasi (setUsername)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/auth/email/verify/send</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/auth/logout</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/auth/set-password</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/auth/set-username</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">6</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Reset Password</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Asesi dapat membuat/mengirim autentikasi (changePassword)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/auth/password/change</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">7</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Manajemen Badge</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Asesi dapat melihat daftar badge</li>
                                <li class="py-0.5">Asesi dapat melihat badge (available)</li>
                                <li class="py-0.5">Asesi dapat melihat detail badge</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/badges</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/badges/available</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/badges/{badge}</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">8</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Persetujuan Konten</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Asesi dapat mengirim konten untuk review</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/content/{type}/{id}/submit</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">9</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Pencarian Konten</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Asesi dapat mencari konten</li>
                                <li class="py-0.5">Asesi dapat melihat pencarian (getSearchHistory)</li>
                                <li class="py-0.5">Asesi dapat menghapus pencarian (clearSearchHistory)</li>
                                <li class="py-0.5">Asesi dapat menghapus pencarian (deleteHistoryItem)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/content/search</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/search/history</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/search/history</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/search/history/{id}</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">10</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Statistik Konten</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Asesi dapat melihat daftar konten</li>
                                <li class="py-0.5">Asesi dapat melihat statistik pengumuman</li>
                                <li class="py-0.5">Asesi dapat melihat konten paling banyak dilihat</li>
                                <li class="py-0.5">Asesi dapat melihat statistik berita</li>
                                <li class="py-0.5">Asesi dapat melihat konten trending</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/content/statistics</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/content/statistics/announcements/{announcement}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/content/statistics/most-viewed</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/content/statistics/news/{news}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/content/statistics/trending</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">11</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Manajemen Kursus</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Asesi dapat melihat my-courses (myEnrolledCourses)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/my-courses</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">12</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Pengumuman Kursus</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Asesi dapat melihat daftar kursus</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/announcements</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">13</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Manajemen Enrollment</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Asesi dapat membatalkan pendaftaran kursus</li>
                                <li class="py-0.5">Asesi dapat menambah kursus</li>
                                <li class="py-0.5">Asesi dapat melihat status kursus</li>
                                <li class="py-0.5">Asesi dapat melihat kursus (indexByCourse)</li>
                                <li class="py-0.5">Asesi dapat melihat kursus (showByCourse)</li>
                                <li class="py-0.5">Asesi dapat membuat/mengirim kursus (withdraw)</li>
                                <li class="py-0.5">Asesi dapat melihat daftar enrollment</li>
                                <li class="py-0.5">Asesi dapat melihat detail enrollment</li>
                                <li class="py-0.5">Asesi dapat menyetujui enrollment</li>
                                <li class="py-0.5">Asesi dapat menolak enrollment</li>
                                <li class="py-0.5">Asesi dapat menghapus enrollment</li>
                                <li class="py-0.5">Asesi dapat melihat enrollment (listInvitations)</li>
                                <li class="py-0.5">Asesi dapat melihat enrollment (showInvitation)</li>
                                <li class="py-0.5">Asesi dapat membuat/mengirim enrollment (acceptInvitation)</li>
                                <li class="py-0.5">Asesi dapat membuat/mengirim enrollment (declineInvitation)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/cancel</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/enroll</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/enrollment-status</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/enrollments</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/enrollments/{enrollment}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/withdraw</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/enrollments</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/enrollments/{enrollment}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/enrollments/{enrollment}/approve</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/enrollments/{enrollment}/decline</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/enrollments/{enrollment}/remove</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/me/enrollments/invitations</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/me/enrollments/invitations/{enrollment}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/me/enrollments/invitations/{enrollment}/accept</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/me/enrollments/invitations/{enrollment}/decline</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">14</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Statistik Forum</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Asesi dapat melihat kursus (userStats)</li>
                                <li class="py-0.5">Asesi dapat melihat daftar kursus</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/my-statistics</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/statistics</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">15</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Manajemen Thread Forum</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Asesi dapat melihat daftar kursus</li>
                                <li class="py-0.5">Asesi dapat menambah kursus</li>
                                <li class="py-0.5">Asesi dapat melihat detail kursus</li>
                                <li class="py-0.5">Asesi dapat mengubah kursus</li>
                                <li class="py-0.5">Asesi dapat menghapus kursus</li>
                                <li class="py-0.5">Asesi dapat mengubah kursus (close)</li>
                                <li class="py-0.5">Asesi dapat mengubah kursus (open)</li>
                                <li class="py-0.5">Asesi dapat mengubah kursus (pin)</li>
                                <li class="py-0.5">Asesi dapat mengubah kursus (resolve)</li>
                                <li class="py-0.5">Asesi dapat mengubah kursus (unpin)</li>
                                <li class="py-0.5">Asesi dapat mengubah kursus (unresolve)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads/{thread}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-cyan-100 text-cyan-700 font-medium">PATCH</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads/{thread}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads/{thread}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-cyan-100 text-cyan-700 font-medium">PATCH</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads/{thread}/close</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-cyan-100 text-cyan-700 font-medium">PATCH</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads/{thread}/open</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-cyan-100 text-cyan-700 font-medium">PATCH</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads/{thread}/pin</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-cyan-100 text-cyan-700 font-medium">PATCH</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads/{thread}/resolve</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-cyan-100 text-cyan-700 font-medium">PATCH</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads/{thread}/unpin</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-cyan-100 text-cyan-700 font-medium">PATCH</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads/{thread}/unresolve</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">16</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Reaksi Forum</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Asesi dapat membuat/mengirim kursus (storeThreadReaction)</li>
                                <li class="py-0.5">Asesi dapat menghapus kursus (destroyThreadReaction)</li>
                                <li class="py-0.5">Asesi dapat membuat/mengirim kursus (storeReplyReaction)</li>
                                <li class="py-0.5">Asesi dapat menghapus kursus (destroyReplyReaction)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads/{thread}/reactions</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads/{thread}/reactions/{reaction}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads/{thread}/replies/{reply}/reactions</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads/{thread}/replies/{reply}/reactions/{reaction}</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">17</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Balasan Forum</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Asesi dapat melihat daftar kursus</li>
                                <li class="py-0.5">Asesi dapat menambah kursus</li>
                                <li class="py-0.5">Asesi dapat mengubah kursus</li>
                                <li class="py-0.5">Asesi dapat menghapus kursus</li>
                                <li class="py-0.5">Asesi dapat mengubah kursus (accept)</li>
                                <li class="py-0.5">Asesi dapat melihat kursus (children)</li>
                                <li class="py-0.5">Asesi dapat mengubah kursus (unaccept)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads/{thread}/replies</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads/{thread}/replies</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-cyan-100 text-cyan-700 font-medium">PATCH</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads/{thread}/replies/{reply}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads/{thread}/replies/{reply}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-cyan-100 text-cyan-700 font-medium">PATCH</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads/{thread}/replies/{reply}/accept</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads/{thread}/replies/{reply}/children</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-cyan-100 text-cyan-700 font-medium">PATCH</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/forum/threads/{thread}/replies/{reply}/unaccept</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">18</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">ProgressController</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Asesi dapat melihat detail kursus</li>
                                <li class="py-0.5">Asesi dapat membuat/mengirim kursus (completeLesson)</li>
                                <li class="py-0.5">Asesi dapat membuat/mengirim kursus (uncompleteLesson)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/progress</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}/lessons/{lesson}/complete</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}/lessons/{lesson}/uncomplete</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">19</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Kuis</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Asesi dapat melihat daftar kursus</li>
                                <li class="py-0.5">Asesi dapat melihat detail kuis</li>
                                <li class="py-0.5">Asesi dapat melihat kuis (listQuestions)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/quizzes</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/quizzes/{quiz}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/quizzes/{quiz}/questions</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">20</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Unit Kompetensi</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Asesi dapat melihat detail kursus</li>
                                <li class="py-0.5">Asesi dapat melihat kursus (contents)</li>
                                <li class="py-0.5">Asesi dapat melihat daftar semua unit kompetensi</li>
                                <li class="py-0.5">Asesi dapat melihat detail unit kompetensi</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}/contents</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/units</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/units/{unit}</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">21</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Manajemen Materi</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Asesi dapat melihat daftar kursus</li>
                                <li class="py-0.5">Asesi dapat melihat detail kursus</li>
                                <li class="py-0.5">Asesi dapat melihat daftar semua materi pelajaran</li>
                                <li class="py-0.5">Asesi dapat melihat detail materi pelajaran</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}/lessons</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}/lessons/{lesson}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/lessons</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/lessons/{lesson}</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">22</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">LessonBlockController</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Asesi dapat melihat daftar kursus</li>
                                <li class="py-0.5">Asesi dapat melihat detail kursus</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}/lessons/{lesson}/blocks</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/units/{unit}/lessons/{lesson}/blocks/{block}</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">23</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Manajemen Pengguna</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Asesi dapat melihat kursus (searchMentions)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/courses/{course}/users/mentions</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">24</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Dashboard</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Asesi dapat melihat daftar dashboard</li>
                                <li class="py-0.5">Asesi dapat melihat dashboard (recentAchievements)</li>
                                <li class="py-0.5">Asesi dapat melihat dashboard (recentLearning)</li>
                                <li class="py-0.5">Asesi dapat melihat dashboard (recommendedCourses)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/dashboard</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/dashboard/recent-achievements</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/dashboard/recent-learning</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/dashboard/recommended-courses</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">25</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Dashboard Forum</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Asesi dapat melihat thread forum sendiri</li>
                                <li class="py-0.5">Asesi dapat melihat semua thread forum</li>
                                <li class="py-0.5">Asesi dapat melihat thread forum trending</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/forums/my-threads</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/forums/threads</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/forums/threads/trending</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">26</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Leaderboard</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Asesi dapat melihat daftar leaderboard</li>
                                <li class="py-0.5">Asesi dapat melihat riwayat poin pengguna</li>
                                <li class="py-0.5">Asesi dapat melihat peringkat sendiri</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/leaderboards</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/leaderboards/{userId}/points-history</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/user/rank</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">27</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Penyelesaian Materi</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Asesi dapat menandai materi pelajaran selesai</li>
                                <li class="py-0.5">Asesi dapat menandai materi pelajaran belum selesai</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/lessons/{lesson}/complete</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/lessons/{lesson}/complete</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">28</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Manajemen Level</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Asesi dapat melihat daftar level</li>
                                <li class="py-0.5">Asesi dapat menghitung level</li>
                                <li class="py-0.5">Asesi dapat melihat progres level</li>
                                <li class="py-0.5">Asesi dapat melihat statistik XP harian</li>
                                <li class="py-0.5">Asesi dapat melihat level pengguna</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/levels</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/levels/calculate</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/levels/progression</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/user/daily-xp-stats</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/user/level</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">29</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Manajemen Media</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Asesi dapat membuat/mengirim media (upload)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/media/upload</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">30</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Manajemen Berita</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Asesi dapat melihat daftar berita</li>
                                <li class="py-0.5">Asesi dapat melihat konten trending</li>
                                <li class="py-0.5">Asesi dapat melihat detail berita</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/news</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/news/trending</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/news/{news}</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">31</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Preferensi Notifikasi</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Asesi dapat melihat daftar preferensi notifikasi</li>
                                <li class="py-0.5">Asesi dapat mengubah preferensi notifikasi</li>
                                <li class="py-0.5">Asesi dapat membuat/mengirim preferensi notifikasi (reset)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/notification-preferences</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/notification-preferences</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/notification-preferences/reset</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">32</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Notifikasi</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Asesi dapat melihat daftar notifikasi</li>
                                <li class="py-0.5">Asesi dapat menambah notifikasi</li>
                                <li class="py-0.5">Asesi dapat melihat detail notifikasi</li>
                                <li class="py-0.5">Asesi dapat mengubah notifikasi</li>
                                <li class="py-0.5">Asesi dapat menghapus notifikasi</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/notifications</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/notifications</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/notifications/{notification}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/notifications/{notification}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/notifications/{notification}</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">33</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Manajemen Profil</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Asesi dapat melihat daftar profil</li>
                                <li class="py-0.5">Asesi dapat mengubah profil</li>
                                <li class="py-0.5">Asesi dapat membuat/mengirim profil (deleteConfirm)</li>
                                <li class="py-0.5">Asesi dapat membuat/mengirim profil (deleteRequest)</li>
                                <li class="py-0.5">Asesi dapat memulihkan profil</li>
                                <li class="py-0.5">Asesi dapat melihat daftar profil</li>
                                <li class="py-0.5">Asesi dapat membuat/mengirim profil (uploadAvatar)</li>
                                <li class="py-0.5">Asesi dapat menghapus profil (deleteAvatar)</li>
                                <li class="py-0.5">Asesi dapat membuat/mengirim profil (requestEmailChange)</li>
                                <li class="py-0.5">Asesi dapat membuat/mengirim profil (verifyEmailChange)</li>
                                <li class="py-0.5">Asesi dapat mengubah profil</li>
                                <li class="py-0.5">Asesi dapat melihat daftar profil</li>
                                <li class="py-0.5">Asesi dapat mengubah profil</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/profile</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/profile</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/profile/account/delete/confirm</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/profile/account/delete/request</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/profile/account/restore</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/profile/activities</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/profile/avatar</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">DELETE</span> <span class="endpoint-badge text-gray-600">api/v1/profile/avatar</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/profile/email/change</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/profile/email/change/verify</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/profile/password</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/profile/privacy</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-medium">PUT</span> <span class="endpoint-badge text-gray-600">api/v1/profile/privacy</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">34</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Submisi Kuis</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Asesi dapat melihat detail quiz-submissions</li>
                                <li class="py-0.5">Asesi dapat membuat/mengirim quiz-submissions (saveAnswer)</li>
                                <li class="py-0.5">Asesi dapat melihat quiz-submissions (listQuestions)</li>
                                <li class="py-0.5">Asesi dapat melihat quiz-submissions (getQuestionAtOrder)</li>
                                <li class="py-0.5">Asesi dapat mengirim quiz-submissions untuk review</li>
                                <li class="py-0.5">Asesi dapat melihat daftar kuis</li>
                                <li class="py-0.5">Asesi dapat melihat kuis (highestSubmission)</li>
                                <li class="py-0.5">Asesi dapat membuat/mengirim kuis (start)</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/quiz-submissions/{submission}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/quiz-submissions/{submission}/answers</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/quiz-submissions/{submission}/questions</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/quiz-submissions/{submission}/questions/{order}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/quiz-submissions/{submission}/submit</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/quizzes/{quiz}/submissions</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/quizzes/{quiz}/submissions/highest</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">POST</span> <span class="endpoint-badge text-gray-600">api/v1/quizzes/{quiz}/submissions/start</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-white hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">35</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Gamifikasi Pengguna</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Asesi dapat melihat badge pengguna</li>
                                <li class="py-0.5">Asesi dapat melihat ringkasan pengguna</li>
                                <li class="py-0.5">Asesi dapat melihat pengguna (unitLevels)</li>
                                <li class="py-0.5">Asesi dapat melihat milestone pengguna</li>
                                <li class="py-0.5">Asesi dapat melihat riwayat poin pengguna</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/user/badges</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/user/gamification-summary</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/user/levels/{slug}</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/user/milestones</span></li>
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/user/points-history</span></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 hover:bg-gray-100 transition-colors align-top">
                            <td class="px-4 py-3 text-sm font-medium text-gray-500">36</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">Profil Publik</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="list-disc list-inside space-y-0.5 text-gray-700">
                                <li class="py-0.5">Asesi dapat melihat detail pengguna</li>
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-0.5">
                                <li class="py-0.5"><span class="endpoint-badge inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-medium">GET</span> <span class="endpoint-badge text-gray-600">api/v1/users/{user}/profile</span></li>
                                </ul>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
        <footer class="text-center text-xs text-gray-400 mt-8 pb-8">
            <p>Dokumen ini di-generate otomatis dari route definitions Laravel.</p>
        </footer>
    </div>
</body>
</html>