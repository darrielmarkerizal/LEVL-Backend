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
 * @tags Master Data
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
     * Get all master data types
     *
     * Returns list of available master data endpoints
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
     * Get user statuses
     *
     * Returns list of user status options
     */
    public function userStatuses(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(UserStatus::class),
            'Daftar status pengguna'
        );
    }

    /**
     * Get roles
     *
     * Returns list of available roles
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
     * Get course statuses
     *
     * Returns list of course status options
     */
    public function courseStatuses(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(CourseStatus::class),
            'Daftar status kursus'
        );
    }

    /**
     * Get course types
     *
     * Returns list of course type options
     */
    public function courseTypes(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(CourseType::class),
            'Daftar tipe kursus'
        );
    }

    /**
     * Get enrollment types
     *
     * Returns list of enrollment type options
     */
    public function enrollmentTypes(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(EnrollmentType::class),
            'Daftar tipe pendaftaran'
        );
    }

    /**
     * Get level tags
     *
     * Returns list of difficulty level options
     */
    public function levelTags(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(LevelTag::class),
            'Daftar level kesulitan'
        );
    }

    /**
     * Get progression modes
     *
     * Returns list of progression mode options
     */
    public function progressionModes(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(ProgressionMode::class),
            'Daftar mode progres'
        );
    }

    /**
     * Get content types (lesson)
     *
     * Returns list of lesson content type options
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
     * Get enrollment statuses
     *
     * Returns list of enrollment status options
     */
    public function enrollmentStatuses(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(EnrollmentStatus::class),
            'Daftar status pendaftaran'
        );
    }

    /**
     * Get progress statuses
     *
     * Returns list of progress status options
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
     * Get assignment statuses
     *
     * Returns list of assignment status options
     */
    public function assignmentStatuses(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(AssignmentStatus::class),
            'Daftar status tugas'
        );
    }

    /**
     * Get submission statuses
     *
     * Returns list of submission status options
     */
    public function submissionStatuses(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(SubmissionStatus::class),
            'Daftar status pengumpulan'
        );
    }

    /**
     * Get submission types
     *
     * Returns list of submission type options
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
     * Get content statuses (news/announcement)
     *
     * Returns list of content status options
     */
    public function contentStatuses(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(ContentStatus::class),
            'Daftar status konten'
        );
    }

    /**
     * Get priorities
     *
     * Returns list of priority options
     */
    public function priorities(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(Priority::class),
            'Daftar prioritas'
        );
    }

    /**
     * Get target types
     *
     * Returns list of target type options
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
     * Get challenge types
     *
     * Returns list of challenge type options
     */
    public function challengeTypes(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(ChallengeType::class),
            'Daftar tipe tantangan'
        );
    }

    /**
     * Get challenge assignment statuses
     *
     * Returns list of challenge assignment status options
     */
    public function challengeAssignmentStatuses(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(ChallengeAssignmentStatus::class),
            'Daftar status tantangan user'
        );
    }

    /**
     * Get challenge criteria types
     *
     * Returns list of challenge criteria type options
     */
    public function challengeCriteriaTypes(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(ChallengeCriteriaType::class),
            'Daftar tipe kriteria tantangan'
        );
    }

    /**
     * Get badge types
     *
     * Returns list of badge type options
     */
    public function badgeTypes(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(BadgeType::class),
            'Daftar tipe badge'
        );
    }

    /**
     * Get point source types
     *
     * Returns list of point source type options
     */
    public function pointSourceTypes(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(PointSourceType::class),
            'Daftar sumber poin'
        );
    }

    /**
     * Get point reasons
     *
     * Returns list of point reason options
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
     * Get notification types
     *
     * Returns list of notification type options
     */
    public function notificationTypes(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(NotificationType::class),
            'Daftar tipe notifikasi'
        );
    }

    /**
     * Get notification channels
     *
     * Returns list of notification channel options
     */
    public function notificationChannels(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(NotificationChannel::class),
            'Daftar channel notifikasi'
        );
    }

    /**
     * Get notification frequencies
     *
     * Returns list of notification frequency options
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
     * Get grade statuses
     *
     * Returns list of grade status options
     */
    public function gradeStatuses(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(GradeStatus::class),
            'Daftar status nilai'
        );
    }

    /**
     * Get grade source types
     *
     * Returns list of grade source type options
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
     * Get category statuses
     *
     * Returns list of category status options
     */
    public function categoryStatuses(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(CategoryStatus::class),
            'Daftar status kategori'
        );
    }

    /**
     * Get setting types
     *
     * Returns list of setting type options
     */
    public function settingTypes(): JsonResponse
    {
        return $this->success(
            $this->transformEnum(SettingType::class),
            'Daftar tipe pengaturan'
        );
    }
}
