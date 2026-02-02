<?php

namespace Modules\Gamification\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class PointResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'points' => $this->points,
            'source_type' => $this->source_type?->value,
            'source_type_label' => $this->source_type?->label(),
            'reason' => $this->reason?->value,
            'reason_label' => $this->reason?->label(),
            'description' => $this->description,
            'context' => $this->resolveContext(),
            'created_at' => $this->created_at,
        ];
    }

    private function resolveContext(): array
    {
        $context = [];
        $sourceId = $this->source_id;
        $sourceType = $this->source_type?->value;

        if (!$sourceId || !$sourceType) {
            return $context;
        }

        switch ($sourceType) {
            case 'course':
                $course = \Modules\Schemes\Models\Course::find($sourceId);
                if ($course) {
                    $context['course'] = ['id' => $course->id, 'title' => $course->title];
                }
                break;
            case 'unit':
                $unit = \Modules\Schemes\Models\Unit::with('course')->find($sourceId);
                if ($unit) {
                    $context['unit'] = ['id' => $unit->id, 'title' => $unit->title];
                    $context['course'] = ['id' => $unit->course_id, 'title' => $unit->course?->title];
                }
                break;
            case 'lesson':
                $lesson = \Modules\Schemes\Models\Lesson::with('unit.course')->find($sourceId);
                if ($lesson) {
                    $context['lesson'] = ['id' => $lesson->id, 'title' => $lesson->title];
                    $context['unit'] = ['id' => $lesson->unit_id, 'title' => $lesson->unit?->title];
                    $context['course'] = ['id' => $lesson->unit?->course_id, 'title' => $lesson->unit?->course?->title];
                }
                break;
            // Handle assignment if needed, usually mapped to lesson or unit
            case 'assignment':
                 $assignment = \Modules\Learning\Models\Assignment::with(['assignable', 'lesson.unit.course'])->find($sourceId);
                 if ($assignment) {
                     $context['assignment'] = ['id' => $assignment->id, 'title' => $assignment->title];
                     
                     // Resolve parent structure
                     if ($assignment->assignable_type === \Modules\Schemes\Models\Course::class) {
                         $course = $assignment->assignable;
                         if ($course) {
                             $context['course'] = ['id' => $course->id, 'title' => $course->title];
                         }
                     } elseif ($assignment->assignable_type === \Modules\Schemes\Models\Unit::class) {
                         $unit = $assignment->assignable;
                         if ($unit) {
                             $context['unit'] = ['id' => $unit->id, 'title' => $unit->title];
                             $context['course'] = ['id' => $unit->course_id, 'title' => $unit->course?->title];
                         }
                     } elseif ($assignment->assignable_type === \Modules\Schemes\Models\Lesson::class) {
                         $lesson = $assignment->assignable;
                         if ($lesson) {
                             $context['lesson'] = ['id' => $lesson->id, 'title' => $lesson->title];
                             $context['unit'] = ['id' => $lesson->unit_id, 'title' => $lesson->unit?->title];
                             $context['course'] = ['id' => $lesson->unit?->course_id, 'title' => $lesson->unit?->course?->title];
                         }
                     } elseif ($assignment->lesson_id) {
                         $lesson = $assignment->lesson;
                         if ($lesson) {
                             $context['lesson'] = ['id' => $lesson->id, 'title' => $lesson->title];
                             $context['unit'] = ['id' => $lesson->unit_id, 'title' => $lesson->unit?->title];
                             $context['course'] = ['id' => $lesson->unit?->course_id, 'title' => $lesson->unit?->course?->title];
                         }
                     }
                 }
                break;
        }

        return $context;
    }
}
