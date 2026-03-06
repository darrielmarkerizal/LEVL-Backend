<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Form Documentation - LEVL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">API Form Documentation</h1>
                        <p class="mt-1 text-sm text-gray-600">Panduan lengkap untuk UI/UX Developer</p>
                    </div>
                    <div class="text-sm text-gray-500">
                        <span class="font-medium">Versi:</span> 1.1<br>
                        <span class="font-medium">Update:</span> 6 Maret 2026
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Introduction -->
            <div class="bg-blue-50 border-l-4 border-blue-500 p-6 mb-8 rounded-r-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-500 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-blue-900">Tentang Dokumentasi Ini</h3>
                        <p class="mt-2 text-blue-800">
                            Dokumentasi ini berisi spesifikasi lengkap untuk semua form pembuatan konten pembelajaran 
                            dari sisi Management (Superadmin, Admin, Instructor). Setiap endpoint dilengkapi dengan 
                            field specification, validation rules, dan contoh request/response.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Documentation Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12">
                <!-- Schemes Module -->
                <a href="/form/schemes" class="group">
                    <div class="bg-white rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 overflow-hidden border border-gray-200">
                        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="text-2xl font-bold text-white">Schemes Module</h2>
                                    <p class="text-indigo-100 mt-1">Course Structure & Content</p>
                                </div>
                                <i class="fas fa-book text-white text-4xl opacity-75"></i>
                            </div>
                        </div>
                        <div class="p-6">
                            <p class="text-gray-600 mb-4">
                                Dokumentasi lengkap untuk membuat dan mengelola struktur kursus, unit, lesson, dan lesson blocks.
                            </p>
                            <div class="space-y-2">
                                <div class="flex items-center text-sm text-gray-700">
                                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                    <span>Course (Kursus)</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-700">
                                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                    <span>Unit</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-700">
                                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                    <span>Lesson (Pelajaran)</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-700">
                                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                    <span>Lesson Block (Content Elements)</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-700">
                                    <i class="fas fa-star text-yellow-500 mr-2"></i>
                                    <span class="font-medium">Unified Content Creation</span>
                                </div>
                            </div>
                            <div class="mt-6 flex items-center text-indigo-600 font-medium group-hover:text-indigo-700">
                                <span>Lihat Dokumentasi</span>
                                <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- Learning Module -->
                <a href="/form/learning" class="group">
                    <div class="bg-white rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 overflow-hidden border border-gray-200">
                        <div class="bg-gradient-to-r from-emerald-500 to-teal-600 p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="text-2xl font-bold text-white">Learning Module</h2>
                                    <p class="text-emerald-100 mt-1">Assessments & Evaluations</p>
                                </div>
                                <i class="fas fa-graduation-cap text-white text-4xl opacity-75"></i>
                            </div>
                        </div>
                        <div class="p-6">
                            <p class="text-gray-600 mb-4">
                                Dokumentasi lengkap untuk membuat dan mengelola assignment, quiz, dan pertanyaan kuis.
                            </p>
                            <div class="space-y-2">
                                <div class="flex items-center text-sm text-gray-700">
                                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                    <span>Assignment (Tugas)</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-700">
                                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                    <span>Quiz (Kuis)</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-700">
                                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                    <span>Quiz Questions</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-700">
                                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                    <span>Question Types (MC, Checkbox, T/F, Essay)</span>
                                </div>
                            </div>
                            <div class="mt-6 flex items-center text-emerald-600 font-medium group-hover:text-emerald-700">
                                <span>Lihat Dokumentasi</span>
                                <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Quick Links -->
            <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
                <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-link text-gray-600 mr-2"></i>
                    Quick Links
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="#general-info" class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors">
                        <i class="fas fa-info-circle text-blue-500 mr-3"></i>
                        <span class="text-gray-700">General Information</span>
                    </a>
                    <a href="#authorization" class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors">
                        <i class="fas fa-lock text-red-500 mr-3"></i>
                        <span class="text-gray-700">Authorization</span>
                    </a>
                    <a href="#response-format" class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors">
                        <i class="fas fa-code text-green-500 mr-3"></i>
                        <span class="text-gray-700">Response Format</span>
                    </a>
                </div>
            </div>

            <!-- General Information -->
            <div id="general-info" class="mt-12 bg-white rounded-lg shadow-md p-8 border border-gray-200">
                <h3 class="text-2xl font-bold text-gray-900 mb-6">Informasi Umum</h3>
                
                <div class="space-y-6">
                    <!-- Authorization -->
                    <div id="authorization">
                        <h4 class="text-lg font-semibold text-gray-800 mb-3">Authorization</h4>
                        <p class="text-gray-600 mb-2">Semua endpoint memerlukan:</p>
                        <ul class="list-disc list-inside space-y-1 text-gray-700 ml-4">
                            <li>Authentication: <code class="bg-gray-100 px-2 py-1 rounded text-sm">Bearer {token}</code> di header</li>
                            <li>Role: Superadmin, Admin, atau Instructor</li>
                            <li>Permission: Sesuai dengan resource yang diakses</li>
                        </ul>
                    </div>

                    <!-- Response Format -->
                    <div id="response-format">
                        <h4 class="text-lg font-semibold text-gray-800 mb-3">Response Format</h4>
                        <p class="text-gray-600 mb-3">Semua endpoint menggunakan format response standar:</p>
                        <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
                            <pre class="text-green-400 text-sm"><code>{
  "success": true,
  "message": "Resource created successfully",
  "data": { ... },
  "meta": { ... },
  "errors": null
}</code></pre>
                        </div>
                    </div>

                    <!-- Status Codes -->
                    <div>
                        <h4 class="text-lg font-semibold text-gray-800 mb-3">HTTP Status Codes</h4>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            <div class="flex items-center p-3 bg-green-50 rounded-lg">
                                <span class="font-mono font-bold text-green-700 mr-2">200</span>
                                <span class="text-sm text-gray-700">Success (GET, PUT)</span>
                            </div>
                            <div class="flex items-center p-3 bg-green-50 rounded-lg">
                                <span class="font-mono font-bold text-green-700 mr-2">201</span>
                                <span class="text-sm text-gray-700">Created (POST)</span>
                            </div>
                            <div class="flex items-center p-3 bg-red-50 rounded-lg">
                                <span class="font-mono font-bold text-red-700 mr-2">400</span>
                                <span class="text-sm text-gray-700">Bad Request</span>
                            </div>
                            <div class="flex items-center p-3 bg-red-50 rounded-lg">
                                <span class="font-mono font-bold text-red-700 mr-2">401</span>
                                <span class="text-sm text-gray-700">Unauthorized</span>
                            </div>
                            <div class="flex items-center p-3 bg-red-50 rounded-lg">
                                <span class="font-mono font-bold text-red-700 mr-2">403</span>
                                <span class="text-sm text-gray-700">Forbidden</span>
                            </div>
                            <div class="flex items-center p-3 bg-red-50 rounded-lg">
                                <span class="font-mono font-bold text-red-700 mr-2">422</span>
                                <span class="text-sm text-gray-700">Validation Error</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex items-center justify-between">
                    <p class="text-gray-600 text-sm">
                        © 2026 LEVL Backend Team. All rights reserved.
                    </p>
                    <div class="flex items-center space-x-4">
                        <a href="https://github.com" class="text-gray-500 hover:text-gray-700">
                            <i class="fab fa-github text-xl"></i>
                        </a>
                        <a href="mailto:backend@levl.com" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-envelope text-xl"></i>
                        </a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>
