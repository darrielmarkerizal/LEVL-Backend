<?php

namespace Modules\Common\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Modules\Auth\Enums\UserStatus;
use Modules\Common\Enums\CategoryStatus;
use Modules\Common\Enums\SettingType;
use Modules\Content\Enums\ContentStatus;
use Modules\Content\Enums\Priority;
use Modules\Content\Enums\TargetType;
use Modules\Enrollments\Enums\EnrollmentStatus;
use Modules\Enrollments\Enums\ProgressStatus;
use Modules\Gamification\Enums\BadgeType;
use Modules\Gamification\Enums\ChallengeAssignmentStatus;
use Modules\Gamification\Enums\ChallengeCriteriaType;
use Modules\Gamification\Enums\ChallengeType;
use Modules\Gamification\Enums\PointReason;
use Modules\Gamification\Enums\PointSourceType;
use Modules\Grading\Enums\GradeStatus;
use Modules\Grading\Enums\SourceType;
use Modules\Learning\Enums\AssignmentStatus;
use Modules\Learning\Enums\SubmissionStatus;
use Modules\Learning\Enums\SubmissionType;
use Modules\Notifications\Enums\NotificationChannel;
use Modules\Notifications\Enums\NotificationFrequency;
use Modules\Notifications\Enums\NotificationType;
use Modules\Schemes\Enums\ContentType;
use Modules\Schemes\Enums\CourseStatus;
use Modules\Schemes\Enums\CourseType;
use Modules\Schemes\Enums\EnrollmentType;
use Modules\Schemes\Enums\LevelTag;
use Modules\Schemes\Enums\ProgressionMode;
use Spatie\Permission\Models\Role;

/**
 * @tags Data Master
 */
class MasterDataController extends Controller
{
    use ApiResponse;

    /**
     * Transform enum cases to value-label array
     */
    private function transformEnum(string $enumClass): array
    {
        return array_map(
            fn ($case) => [
                'value' => $case->value,
                'label' => $case->label(),
            ],
            $enumClass::cases()
        );
    }

    /**
     * @summary Daftar Tipe Master Data
     */
    public function index(): JsonResponse
    {
        $types = [
            // Database-backed master data (CRUD)
            ['key' => 'categories', 'label' => 'Kategori', 'type' => 'crud'],
            ['key' => 'tags', 'label' => 'Tags', 'type' => 'crud'],
            // Enum-based master data (Read-only)
            ['key' => 'user-status', 'label' => 'Status Pengguna', 'type' => 'enum'],
            ['key' => 'roles', 'label' => 'Peran', 'type' => 'enum'],
            ['key' => 'course-status', 'label' => 'Status Kursus', 'type' => 'enum'],
            ['key' => 'course-types', 'label' => 'Tipe Kursus', 'type' => 'enum'],
            ['key' => 'enrollment-types', 'label' => 'Tipe Pendaftaran', 'type' => 'enum'],
            ['key' => 'level-tags', 'label' => 'Level Kesulitan', 'type' => 'enum'],
            ['key' => 'progression-modes', 'label' => 'Mode Progres', 'type' => 'enum'],
            ['key' => 'content-types', 'label' => 'Tipe Konten', 'type' => 'enum'],
            ['key' => 'enrollment-status', 'label' => 'Status Pendaftaran', 'type' => 'enum'],
            ['key' => 'progress-status', 'label' => 'Status Progres', 'type' => 'enum'],
            ['key' => 'assignment-status', 'label' => 'Status Tugas', 'type' => 'enum'],
            ['key' => 'submission-status', 'label' => 'Status Pengumpulan', 'type' => 'enum'],
            ['key' => 'submission-types', 'label' => 'Tipe Pengumpulan', 'type' => 'enum'],
            ['key' => 'content-status', 'label' => 'Status Konten', 'type' => 'enum'],
            ['key' => 'priorities', 'label' => 'Prioritas', 'type' => 'enum'],
            ['key' => 'target-types', 'label' => 'Tipe Target', 'type' => 'enum'],
            ['key' => 'challenge-types', 'label' => 'Tipe Tantangan', 'type' => 'enum'],
            ['key' => 'challenge-assignment-status', 'label' => 'Status Tantangan User', 'type' => 'enum'],
            ['key' => 'challenge-criteria-types', 'label' => 'Tipe Kriteria Tantangan', 'type' => 'enum'],
            ['key' => 'badge-types', 'label' => 'Tipe Badge', 'type' => 'enum'],
            ['key' => 'point-source-types', 'label' => 'Sumber Poin', 'type' => 'enum'],
            ['key' => 'point-reasons', 'label' => 'Alasan Poin', 'type' => 'enum'],
            ['key' => 'notification-types', 'label' => 'Tipe Notifikasi', 'type' => 'enum'],
            ['key' => 'notification-channels', 'label' => 'Channel Notifikasi', 'type' => 'enum'],
            ['key' => 'notification-frequencies', 'label' => 'Frekuensi Notifikasi', 'type' => 'enum'],
            ['key' => 'grade-status', 'label' => 'Status Nilai', 'type' => 'enum'],
            ['key' => 'grade-source-types', 'label' => 'Sumber Nilai', 'type' => 'enum'],
            ['key' => 'category-status', 'label' => 'Status Kategori', 'type' => 'enum'],
            ['key' => 'setting-types', 'label' => 'Tipe Pengaturan', 'type' => 'enum'],
        ];

        return $this->success($types, 'Daftar tipe master data');
    }

    // ==================== AUTH ====================

    /**
     * @summary Daftar Status Pengguna
     */
    public function userStatuses(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(UserStatus::class),
            'Daftar status pengguna'
        );
    }

    /**
     * @summary Daftar Peran
     */
    public function roles(): JsonResponse
    {
        $roles = Role::all()->map(fn ($role) => [
            'value' => $role->name,
            'label' => __('enums.roles.'.strtolower($role->name)),
        ])->toArray();

        return $this->success($roles, 'Daftar peran');
    }

    // ==================== SCHEMES ====================

    /**
     * @summary Daftar Status Kursus
     */
    public function courseStatuses(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(CourseStatus::class),
            'Daftar status kursus'
        );
    }

    /**
     * @summary Daftar Tipe Kursus
     */
    public function courseTypes(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(CourseType::class),
            'Daftar tipe kursus'
        );
    }

    /**
     * @summary Daftar Tipe Pendaftaran
     */
    public function enrollmentTypes(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(EnrollmentType::class),
            'Daftar tipe pendaftaran'
        );
    }

    /**
     * @summary Daftar Level Kesulitan
     */
    public function levelTags(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(LevelTag::class),
            'Daftar level kesulitan'
        );
    }

    /**
     * @summary Daftar Mode Progres
     */
    public function progressionModes(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(ProgressionMode::class),
            'Daftar mode progres'
        );
    }

    /**
     * @summary Daftar Tipe Konten Lesson
     */
    public function contentTypes(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(ContentType::class),
            'Daftar tipe konten'
        );
    }

    // ==================== ENROLLMENTS ====================

    /**
     * @summary Daftar Status Pendaftaran
     */
    public function enrollmentStatuses(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(EnrollmentStatus::class),
            'Daftar status pendaftaran'
        );
    }

    /**
     * @summary Daftar Status Progres
     */
    public function progressStatuses(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(ProgressStatus::class),
            'Daftar status progres'
        );
    }

    // ==================== LEARNING ====================

    /**
     * @summary Daftar Status Tugas
     */
    public function assignmentStatuses(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(AssignmentStatus::class),
            'Daftar status tugas'
        );
    }

    /**
     * @summary Daftar Status Pengumpulan
     */
    public function submissionStatuses(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(SubmissionStatus::class),
            'Daftar status pengumpulan'
        );
    }

    /**
     * @summary Daftar Tipe Pengumpulan
     */
    public function submissionTypes(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(SubmissionType::class),
            'Daftar tipe pengumpulan'
        );
    }

    // ==================== CONTENT ====================

    /**
     * @summary Daftar Status Konten
     */
    public function contentStatuses(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(ContentStatus::class),
            'Daftar status konten'
        );
    }

    /**
     * @summary Daftar Prioritas
     */
    public function priorities(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(Priority::class),
            'Daftar prioritas'
        );
    }

    /**
     * @summary Daftar Tipe Target
     */
    public function targetTypes(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(TargetType::class),
            'Daftar tipe target'
        );
    }

    // ==================== GAMIFICATION ====================

    /**
     * @summary Daftar Tipe Tantangan
     */
    public function challengeTypes(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(ChallengeType::class),
            'Daftar tipe tantangan'
        );
    }

    /**
     * @summary Daftar Status Tantangan User
     */
    public function challengeAssignmentStatuses(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(ChallengeAssignmentStatus::class),
            'Daftar status tantangan user'
        );
    }

    /**
     * @summary Daftar Kriteria Tantangan
     */
    public function challengeCriteriaTypes(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(ChallengeCriteriaType::class),
            'Daftar tipe kriteria tantangan'
        );
    }

    /**
     * @summary Daftar Tipe Badge
     */
    public function badgeTypes(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(BadgeType::class),
            'Daftar tipe badge'
        );
    }

    /**
     * @summary Daftar Sumber Poin
     */
    public function pointSourceTypes(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(PointSourceType::class),
            'Daftar sumber poin'
        );
    }

    /**
     * @summary Daftar Alasan Poin
     */
    public function pointReasons(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(PointReason::class),
            'Daftar alasan poin'
        );
    }

    // ==================== NOTIFICATIONS ====================

    /**
     * @summary Daftar Tipe Notifikasi
     */
    public function notificationTypes(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(NotificationType::class),
            'Daftar tipe notifikasi'
        );
    }

    /**
     * @summary Daftar Channel Notifikasi
     */
    public function notificationChannels(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(NotificationChannel::class),
            'Daftar channel notifikasi'
        );
    }

    /**
     * @summary Daftar Frekuensi Notifikasi
     */
    public function notificationFrequencies(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(NotificationFrequency::class),
            'Daftar frekuensi notifikasi'
        );
    }

    // ==================== GRADING ====================

    /**
     * @summary Daftar Status Nilai
     */
    public function gradeStatuses(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(GradeStatus::class),
            'Daftar status nilai'
        );
    }

    /**
     * @summary Daftar Sumber Nilai
     */
    public function gradeSourceTypes(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(SourceType::class),
            'Daftar sumber nilai'
        );
    }

    // ==================== COMMON ====================

    /**
     * @summary Daftar Status Kategori
     */
    public function categoryStatuses(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(CategoryStatus::class),
            'Daftar status kategori'
        );
    }

    /**
     * @summary Daftar Tipe Pengaturan
     */
    public function settingTypes(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(SettingType::class),
            'Daftar tipe pengaturan'
        );
    }
}
