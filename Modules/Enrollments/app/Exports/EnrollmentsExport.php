<?php

namespace Modules\Enrollments\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class EnrollmentsExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected Collection $enrollments;

    public function __construct(Collection $enrollments)
    {
        $this->enrollments = $enrollments;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return $this->enrollments;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Student Name',
            'Email',
            'Status',
            'Progress %',
            'Enrolled At',
            'Completed At',
        ];
    }

    /**
     * @param mixed $enrollment
     * @return array
     */
    public function map($enrollment): array
    {
        $progress = $enrollment->courseProgress;

        return [
            $enrollment->id,
            $enrollment->user?->name ?? 'N/A',
            $enrollment->user?->email ?? 'N/A',
            $enrollment->status,
            $progress?->progress_percent ?? 0,
            $enrollment->enrolled_at?->toDateTimeString(),
            $enrollment->completed_at?->toDateTimeString() ?? 'N/A',
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Enrollments';
    }
}
