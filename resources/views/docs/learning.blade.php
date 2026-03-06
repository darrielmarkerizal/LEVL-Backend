<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learning Module - API Documentation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .code-block { font-family: 'Courier New', monospace; }
        .collapsible-content { max-height: 0; overflow: hidden; transition: max-height 0.3s ease; }
        .collapsible-content.active { max-height: 5000px; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <header class="bg-gradient-to-r from-emerald-500 to-teal-600 shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <a href="/form" class="text-white hover:text-emerald-100 text-sm mb-2 inline-flex items-center">
                            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar
                        </a>
                        <h1 class="text-3xl font-bold text-white">Learning Module</h1>
                        <p class="mt-1 text-emerald-100">Assessments & Evaluations Management</p>
                    </div>
                    <i class="fas fa-graduation-cap text-white text-5xl opacity-75"></i>
                </div>
            </div>
        </header>

        <nav class="bg-white shadow-sm sticky top-0 z-10 border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex space-x-8 overflow-x-auto py-4">
                    <a href="#assignment" class="text-gray-700 hover:text-emerald-600 whitespace-nowrap font-medium">Assignment</a>
                    <a href="#quiz" class="text-gray-700 hover:text-emerald-600 whitespace-nowrap font-medium">Quiz</a>
                    <a href="#questions" class="text-gray-700 hover:text-emerald-600 whitespace-nowrap font-medium">Quiz Questions</a>
                </div>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <!-- Quiz Section -->
            <section id="quiz" class="mb-12">
                <div class="bg-white rounded-lg shadow-md p-8 border border-gray-200">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">2. Quiz (Kuis)</h2>
                        <span class="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-sm font-medium">Auto-graded</span>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Endpoint</h3>
                        <div class="bg-gray-900 rounded-lg p-4">
                            <code class="text-green-400">POST /api/v1/quizzes</code><br>
                            <code class="text-yellow-400">PUT /api/v1/quizzes/{quiz_id}</code>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Field Spesifikasi</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Field</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipe</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Required</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Validasi</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-mono text-gray-900">unit_slug</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">string</td>
                                        <td class="px-4 py-3"><span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs">Ya</span></td>
                                        <td class="px-4 py-3 text-sm text-gray-600">exists:units,slug</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">Slug unit</td>
                                    </tr>
                                    <tr class="bg-gray-50">
                                        <td class="px-4 py-3 text-sm font-mono text-gray-900">title</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">string</td>
                                        <td class="px-4 py-3"><span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs">Ya</span></td>
                                        <td class="px-4 py-3 text-sm text-gray-600">max:255</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">Judul quiz</td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-mono text-gray-900">passing_grade</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">decimal</td>
                                        <td class="px-4 py-3"><span class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs">Tidak</span></td>
                                        <td class="px-4 py-3 text-sm text-gray-600">min:0, max:100</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">Default: 60 (%)</td>
                                    </tr>
                                    <tr class="bg-gray-50">
                                        <td class="px-4 py-3 text-sm font-mono text-gray-900">auto_grading</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">boolean</td>
                                        <td class="px-4 py-3"><span class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs">Tidak</span></td>
                                        <td class="px-4 py-3 text-sm text-gray-600">true/false</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">Default: true</td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-mono text-gray-900">randomization_type</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">enum</td>
                                        <td class="px-4 py-3"><span class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs">Tidak</span></td>
                                        <td class="px-4 py-3 text-sm text-gray-600">static, random_order, bank</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">Default: static</td>
                                    </tr>
                                    <tr class="bg-gray-50">
                                        <td class="px-4 py-3 text-sm font-mono text-gray-900">review_mode</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">enum</td>
                                        <td class="px-4 py-3"><span class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs">Tidak</span></td>
                                        <td class="px-4 py-3 text-sm text-gray-600">immediate, after_deadline, never</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">Default: immediate</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Randomization Type</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-list-ol text-blue-500 text-xl mr-3"></i>
                                    <h4 class="font-semibold text-gray-800">static</h4>
                                </div>
                                <p class="text-sm text-gray-600">Soal selalu urutan sama</p>
                            </div>
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-random text-purple-500 text-xl mr-3"></i>
                                    <h4 class="font-semibold text-gray-800">random_order</h4>
                                </div>
                                <p class="text-sm text-gray-600">Soal diacak urutannya</p>
                            </div>
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-database text-green-500 text-xl mr-3"></i>
                                    <h4 class="font-semibold text-gray-800">bank</h4>
                                </div>
                                <p class="text-sm text-gray-600">Subset random dari bank soal</p>
                            </div>
                        </div>
                    </div>

                    <button onclick="toggleCollapsible('quiz-example')" class="w-full bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-medium py-3 px-4 rounded-lg flex items-center justify-between transition-colors">
                        <span>Lihat Contoh Request</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div id="quiz-example" class="collapsible-content mt-4">
                        <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
                            <pre class="text-green-400 text-sm"><code>{
  "unit_slug": "pengenalan-html",
  "title": "Quiz HTML Dasar",
  "description": "Kuis untuk menguji pemahaman HTML dasar",
  "passing_grade": 80,
  "auto_grading": true,
  "time_limit_minutes": 30,
  "randomization_type": "random_order",
  "review_mode": "immediate",
  "order": 2
}</code></pre>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Quiz Questions Section -->
            <section id="questions" class="mb-12">
                <div class="bg-white rounded-lg shadow-md p-8 border border-gray-200">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">3. Quiz Questions (Pertanyaan Kuis)</h2>
                        <span class="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-sm font-medium">4 Types</span>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Endpoint</h3>
                        <div class="bg-gray-900 rounded-lg p-4">
                            <code class="text-green-400">POST /api/v1/quizzes/{quiz_id}/questions</code><br>
                            <code class="text-yellow-400">PUT /api/v1/quizzes/{quiz_id}/questions/{question_id}</code>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div class="border-2 border-blue-200 rounded-lg p-4 bg-blue-50">
                            <div class="flex items-center mb-3">
                                <i class="fas fa-check-circle text-blue-500 text-2xl mr-3"></i>
                                <h4 class="font-semibold text-gray-800">Multiple Choice</h4>
                            </div>
                            <p class="text-sm text-gray-600 mb-2">Pilihan ganda (1 jawaban benar)</p>
                            <div class="bg-white p-2 rounded text-xs">
                                <code>type: multiple_choice</code><br>
                                <code>options: REQUIRED</code><br>
                                <code>answer_key: [0] (1 index)</code>
                            </div>
                        </div>

                        <div class="border-2 border-purple-200 rounded-lg p-4 bg-purple-50">
                            <div class="flex items-center mb-3">
                                <i class="fas fa-check-square text-purple-500 text-2xl mr-3"></i>
                                <h4 class="font-semibold text-gray-800">Checkbox</h4>
                            </div>
                            <p class="text-sm text-gray-600 mb-2">Pilihan ganda (>1 jawaban benar)</p>
                            <div class="bg-white p-2 rounded text-xs">
                                <code>type: checkbox</code><br>
                                <code>options: REQUIRED</code><br>
                                <code>answer_key: [0,1,2] (multiple)</code>
                            </div>
                        </div>

                        <div class="border-2 border-green-200 rounded-lg p-4 bg-green-50">
                            <div class="flex items-center mb-3">
                                <i class="fas fa-toggle-on text-green-500 text-2xl mr-3"></i>
                                <h4 class="font-semibold text-gray-800">True/False</h4>
                            </div>
                            <p class="text-sm text-gray-600 mb-2">Benar/Salah</p>
                            <div class="bg-white p-2 rounded text-xs">
                                <code>type: true_false</code><br>
                                <code>options: 2 pilihan (Benar/Salah)</code><br>
                                <code>answer_key: [0] atau [1]</code>
                            </div>
                        </div>

                        <div class="border-2 border-orange-200 rounded-lg p-4 bg-orange-50">
                            <div class="flex items-center mb-3">
                                <i class="fas fa-edit text-orange-500 text-2xl mr-3"></i>
                                <h4 class="font-semibold text-gray-800">Essay</h4>
                            </div>
                            <p class="text-sm text-gray-600 mb-2">Jawaban panjang (manual grading)</p>
                            <div class="bg-white p-2 rounded text-xs">
                                <code>type: essay</code><br>
                                <code>options: TIDAK diperlukan</code><br>
                                <code>answer_key: TIDAK diperlukan</code>
                            </div>
                        </div>
                    </div>

                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
                        <div class="flex">
                            <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                            <div>
                                <p class="text-sm text-blue-800">
                                    <strong>Catatan:</strong> Field <code class="bg-blue-100 px-1 rounded">answer_key</code> 
                                    berisi array of index (dimulai dari 0). Untuk true_false, gunakan index 0 untuk True/Benar, index 1 untuk False/Salah.
                                </p>
                            </div>
                        </div>
                    </div>

                    <button onclick="toggleCollapsible('questions-example')" class="w-full bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-medium py-3 px-4 rounded-lg flex items-center justify-between transition-colors">
                        <span>Lihat Contoh Request</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div id="questions-example" class="collapsible-content mt-4">
                        <div class="space-y-4">
                            <div>
                                <h5 class="font-medium text-gray-700 mb-2">Multiple Choice:</h5>
                                <div class="bg-gray-900 rounded-lg p-4">
                                    <pre class="text-green-400 text-sm"><code>{
  "type": "multiple_choice",
  "content": "Apa kepanjangan dari HTML?",
  "weight": 1.0,
  "max_score": 10,
  "order": 1,
  "options": [
    {"text": "Hyper Text Markup Language"},
    {"text": "High Tech Modern Language"},
    {"text": "Home Tool Markup Language"}
  ],
  "answer_key": [0]
}</code></pre>
                                </div>
                            </div>
                            <div>
                                <h5 class="font-medium text-gray-700 mb-2">Checkbox:</h5>
                                <div class="bg-gray-900 rounded-lg p-4">
                                    <pre class="text-green-400 text-sm"><code>{
  "type": "checkbox",
  "content": "Pilih tag HTML yang valid:",
  "weight": 1.5,
  "max_score": 15,
  "options": [
    {"text": "&lt;div&gt;"},
    {"text": "&lt;span&gt;"},
    {"text": "&lt;section&gt;"},
    {"text": "&lt;paragraph&gt;"}
  ],
  "answer_key": [0, 1, 2]
}</code></pre>
                                </div>
                            </div>
                            <div>
                                <h5 class="font-medium text-gray-700 mb-2">True/False:</h5>
                                <div class="bg-gray-900 rounded-lg p-4">
                                    <pre class="text-green-400 text-sm"><code>{
  "type": "true_false",
  "content": "HTML adalah bahasa pemrograman",
  "weight": 0.5,
  "max_score": 5,
  "options": [
    {"text": "Benar"},
    {"text": "Salah"}
  ],
  "answer_key": [1]
}</code></pre>
                                </div>
                            </div>
                            <div>
                                <h5 class="font-medium text-gray-700 mb-2">Essay:</h5>
                                <div class="bg-gray-900 rounded-lg p-4">
                                    <pre class="text-green-400 text-sm"><code>{
  "type": "essay",
  "content": "Jelaskan perbedaan antara tag &lt;div&gt; dan &lt;span&gt;!",
  "weight": 2.0,
  "max_score": 20,
  "order": 4
}</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer class="bg-white border-t border-gray-200 mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <p class="text-center text-gray-600 text-sm">© 2026 LEVL Backend Team</p>
            </div>
        </footer>
    </div>

    <script>
        function toggleCollapsible(id) {
            const element = document.getElementById(id);
            element.classList.toggle('active');
        }

        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    </script>
</body>
</html>
