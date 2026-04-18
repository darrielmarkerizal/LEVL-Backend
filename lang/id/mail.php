<?php

return [
    // Auth Emails
    'credentials' => [
        'subject' => 'Kredensial Akun Anda',
        'title' => 'Akun Anda Telah Dibuat',
        'greeting' => 'Halo :name,',
        'body' => 'Akun Anda telah dibuat oleh administrator. Gunakan kredensial berikut untuk masuk, lalu segera ubah password Anda setelah login.',
        'email_label' => 'Email',
        'password_label' => 'Password Sementara',
        'button' => 'Masuk Sekarang',
        'footer' => 'Jika Anda merasa tidak terkait dengan pembuatan akun ini, abaikan email ini.',
    ],

    'verify' => [
        'subject' => 'Verifikasi Email Anda',
        'title' => 'Verifikasi Email Anda',
        'greeting' => 'Halo :name,',
        'body' => 'Terima kasih telah mendaftar. Untuk menyelesaikan pendaftaran, silakan verifikasi alamat email Anda dengan mengklik tombol di bawah ini:',
        'button' => 'Verifikasi Email Saya',
        'info' => '<strong>Penting:</strong> Link verifikasi ini berlaku selama :minutes menit dan hanya dapat digunakan satu kali.',
        'footer' => 'Jika Anda tidak membuat akun ini, Anda dapat mengabaikan email ini dengan aman.',
    ],

    'reset' => [
        'subject' => 'Reset Kata Sandi Anda',
        'title' => 'Reset Kata Sandi Anda',
        'greeting' => 'Halo :name,',
        'body' => 'Kami menerima permintaan untuk mereset kata sandi akun Anda. Untuk melanjutkan, klik tombol di bawah ini:',
        'button' => 'Atur Ulang Kata Sandi',
        'info' => '<strong>Penting:</strong> Link reset berlaku selama :minutes menit dan hanya dapat digunakan satu kali.',
        'footer' => 'Jika Anda tidak meminta reset kata sandi, Anda dapat mengabaikan email ini dengan aman.',
    ],

    'change_email' => [
        'subject' => 'Verifikasi Perubahan Email',
        'title' => 'Verifikasi Perubahan Email',
        'greeting' => 'Halo :name,',
        'body' => 'Kami menerima permintaan untuk <strong>mengubah email akun</strong> Anda menjadi <strong>:new_email</strong>.',
        'body_confirm' => 'Jika ini benar, silakan konfirmasi perubahan dengan mengklik tombol di bawah ini:',
        'button' => 'Verifikasi Perubahan Email',
        'info' => '<strong>Penting:</strong> Link ini berlaku selama :minutes menit dan hanya dapat digunakan satu kali.',
        'footer' => 'Jika Anda tidak meminta perubahan ini, abaikan email ini.',
    ],

    'delete_account' => [
        'subject' => 'Konfirmasi Penghapusan Akun',
        'title' => 'Konfirmasi Penghapusan Akun',
        'greeting' => 'Halo :name,',
        'body_1' => 'Kami menerima permintaan untuk menghapus akun Anda secara permanen.',
        'body_2' => 'Tindakan ini <strong>tidak dapat dibatalkan</strong>. Semua data Anda akan dihapus secara permanen dari sistem kami.',
        'button' => 'Hapus Akun Saya Permanen',
        'info' => '<strong>Penting:</strong> Link konfirmasi ini berlaku selama :minutes menit dan hanya dapat digunakan satu kali.',
        'warning' => 'Jika Anda tidak meminta penghapusan akun, segera amankan akun Anda karena seseorang mungkin memiliki akses ke password Anda.',
        'footer' => 'Email ini dikirimkan secara otomatis oleh sistem keamanan.',
    ],

    'users_export' => [
        'subject' => 'Export Data Pengguna',
        'title' => 'Export Data Pengguna',
        'greeting' => 'Halo,',
        'body' => 'Export data pengguna Anda telah selesai dan terlampir dalam email ini.',
        'file_label' => 'File',
        'footer' => 'Email ini dikirim secara otomatis. Mohon tidak membalas email ini.',
    ],

    // Enrollment Emails
    'enrollment_active' => [
        'subject' => 'Enrollment Berhasil',
        'title' => 'Selamat! Enrollment Berhasil',
        'greeting' => 'Halo :name,',
        'body' => 'Anda telah berhasil terdaftar pada course berikut:',
        'info' => '<strong>Status:</strong> Anda sekarang terdaftar aktif dan dapat mulai mengakses materi course.',
        'button' => 'Akses Course',
        'footer' => 'Selamat belajar!',
    ],

    'enrollment_pending' => [
        'subject' => 'Permintaan Enrollment Dikirim',
        'title' => 'Permintaan Enrollment Dikirim',
        'greeting' => 'Halo :name,',
        'body' => 'Terima kasih atas minat Anda untuk mengikuti course berikut:',
        'info' => '<strong>Status:</strong> Permintaan enrollment Anda sedang menunggu persetujuan dari admin atau instructor course. Anda akan menerima notifikasi email setelah permintaan Anda disetujui atau ditolak.',
        'confirmation' => 'Kami akan mengirimkan email konfirmasi segera setelah permintaan Anda diproses.',
        'footer' => 'Terima kasih atas kesabaran Anda.',
    ],

    'enrollment_manual_active' => [
        'subject' => 'Anda Telah Terdaftar di Course',
        'title' => 'Konfirmasi Enrollment Course',
        'greeting' => 'Halo :name,',
        'body' => 'Administrator Anda telah mendaftarkan Anda pada course berikut:',
        'info' => '<strong>Status:</strong> Anda telah terdaftar dan dapat langsung mengakses materi course.',
        'button' => 'Akses Course',
        'footer' => 'Jika Anda memiliki pertanyaan, silakan hubungi administrator course Anda.',
    ],

    'enrollment_manual_pending' => [
        'subject' => 'Notifikasi Enrollment',
        'title' => 'Enrollment Dikirim untuk Ditinjau',
        'greeting' => 'Halo :name,',
        'body' => 'Administrator Anda telah mengirimkan enrollment Anda untuk course berikut:',
        'info' => '<strong>Status:</strong> Enrollment Anda sedang menunggu tinjauan dan persetujuan dari administrator atau instructor course. Anda akan menerima notifikasi setelah enrollment Anda disetujui.',
        'confirmation' => 'Anda dapat mengakses materi course setelah enrollment Anda disetujui.',
        'footer' => 'Terima kasih atas kesabaran Anda.',
    ],

    'enrollment_approved' => [
        'subject' => 'Permintaan Enrollment Disetujui',
        'title' => 'Permintaan Enrollment Disetujui',
        'greeting' => 'Halo :name,',
        'body' => 'Kabar baik! Permintaan enrollment Anda untuk course berikut telah disetujui:',
        'info' => '<strong>Status:</strong> Anda sekarang terdaftar aktif dan dapat mulai mengakses materi course.',
        'button' => 'Akses Course',
        'footer' => 'Selamat belajar!',
    ],

    'enrollment_declined' => [
        'subject' => 'Permintaan Enrollment Ditolak',
        'title' => 'Permintaan Enrollment Ditolak',
        'greeting' => 'Halo :name,',
        'body' => 'Kami memberitahu bahwa permintaan enrollment Anda untuk course berikut telah ditolak:',
        'info' => '<strong>Status:</strong> Permintaan enrollment Anda telah ditolak oleh admin atau instructor course.',
        'contact' => 'Jika Anda memiliki pertanyaan mengenai keputusan ini, silakan hubungi admin atau instructor course terkait.',
        'footer' => 'Terima kasih atas pengertian Anda.',
    ],

    'admin_enrollment_notification' => [
        'subject' => 'Notifikasi Enrollment Baru',
        'title' => 'Notifikasi Enrollment Baru',
        'greeting' => 'Halo :name,',
        'body' => 'Ada enrollment baru pada course yang Anda kelola:',
        'course_label' => 'Course',
        'student_label' => 'Peserta',
        'status_label' => 'Status',
        'status_pending' => 'Menunggu Persetujuan',
        'status_active' => 'Terdaftar Aktif',
        'body_pending' => 'Silakan tinjau dan setujui atau tolak permintaan enrollment ini melalui dashboard admin.',
        'body_active' => 'Peserta telah terdaftar aktif pada course ini.',
        'button_manage' => 'Kelola Enrollments',
        'button_view' => 'Lihat Enrollments',
        'footer' => 'Email ini dikirim secara otomatis dari sistem :app_name.',
    ],

    // Learning Emails
    'assignment_published' => [
        'subject' => 'Assignment Baru Tersedia',
        'subject_with_details' => 'Assignment baru: :assignment — :course',
        'title' => 'Assignment Baru Tersedia',
        'greeting' => 'Halo, <strong>:name</strong>!',
        'body' => 'Assignment baru telah dipublikasikan untuk course yang Anda ikuti:',
        'course_label' => 'Course',
        'available_from_label' => 'Tersedia dari',
        'deadline_label' => 'Deadline',
        'max_score_label' => 'Maksimal Score',
        'submit_text' => 'Silakan akses assignment ini dan kirimkan submission Anda sebelum deadline.',
        'button_assignment' => 'Lihat Assignment',
        'button_course' => 'Lihat Course',
        'footer' => 'Email ini dikirim secara otomatis. Mohon tidak membalas email ini.',
    ],

    // Schemes Emails
    'course_completed' => [
        'subject' => 'Selamat! Course Selesai',
        'title' => 'Selamat!',
        'subtitle' => 'Anda telah menyelesaikan course!',
        'greeting' => 'Halo, <strong>:name</strong>!',
        'body' => 'Kami ingin mengucapkan selamat karena Anda telah berhasil menyelesaikan course berikut:',
        'course_code_label' => 'Kode Course',
        'completed_date_label' => 'Tanggal Selesai',
        'progress_label' => 'Progress',
        'stat_progress' => 'Progress',
        'stat_completed' => 'Selesai',
        'thanks' => 'Terima kasih telah menyelesaikan course ini. Kami harap Anda mendapatkan pengetahuan dan keterampilan yang bermanfaat.',
        'button' => 'Lihat Course',
        'footer' => 'Email ini dikirim secara otomatis. Mohon tidak membalas email ini.',
    ],

    // Notifications Emails
    'post_published' => [
        'subject' => 'Pengumuman Baru Dipublikasikan',
        'title' => 'Pengumuman Baru Dipublikasikan',
        'greeting' => 'Halo :name,',
        'body' => 'Pengumuman baru telah dipublikasikan yang mungkin menarik bagi Anda:',
        'post_title_label' => 'Judul',
        'category_label' => 'Kategori',
        'excerpt_label' => 'Pratinjau',
        'button' => 'Baca Pengumuman Lengkap',
        'info' => 'Pengumuman ini dipublikasikan khusus untuk peran Anda. Klik tombol di atas untuk membaca konten lengkap.',
        'footer' => 'Email ini dikirim secara otomatis. Mohon tidak membalas email ini.',
    ],

    // Common
    'common' => [
        'fallback_url_text' => 'Jika tombol di atas tidak berfungsi, salin dan tempel URL berikut ke browser Anda:',
        'thanks' => 'Terima kasih',
        'regards' => 'Salam',
        'team' => 'Tim :app_name',
    ],
];
