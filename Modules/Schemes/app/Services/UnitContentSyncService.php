<?php

declare(strict_types=1);

namespace Modules\Schemes\Services;

use Illuminate\Support\Facades\DB;
use Modules\Schemes\Models\UnitContent;

class UnitContentSyncService
{
    public function register(string $type, int $contentId, int $unitId, ?int $order = null): UnitContent
    {
        return DB::transaction(function () use ($type, $contentId, $unitId, $order) {
            if ($order !== null) {
                UnitContent::where('unit_id', $unitId)
                    ->where('order', '>=', $order)
                    ->lockForUpdate()
                    ->increment('order');
            } else {
                $order = $this->getNextOrder($unitId);
            }

            $uc = UnitContent::create([
                'unit_id' => $unitId,
                'contentable_type' => $type,
                'contentable_id' => $contentId,
                'order' => $order,
            ]);

            $this->syncOrderToModel($type, $contentId, $order);

            return $uc;
        });
    }

    public function reorder(int $unitId, array $contentOrder): array
    {
        return DB::transaction(function () use ($unitId, $contentOrder) {
            $items = UnitContent::where('unit_id', $unitId)
                ->lockForUpdate()
                ->get()
                ->keyBy(fn ($item) => $item->contentable_type . '-' . $item->contentable_id);

            $usedKeys = [];

            foreach ($contentOrder as $index => $item) {
                $key = $item['type'] . '-' . $item['id'];

                if (!isset($items[$key])) {
                    continue;
                }

                $newOrder = $index + 1;

                $items[$key]->update(['order' => $newOrder]);

                $this->syncOrderToModel($item['type'], (int) $item['id'], $newOrder);

                $usedKeys[] = $key;
            }

            $nextOrder = count($usedKeys);

            foreach ($items as $key => $item) {
                if (in_array($key, $usedKeys, true)) {
                    continue;
                }

                $nextOrder++;

                $item->update(['order' => $nextOrder]);

                $this->syncOrderToModel(
                    $item->contentable_type,
                    (int) $item->contentable_id,
                    $nextOrder
                );
            }

            return UnitContent::where('unit_id', $unitId)
                ->orderBy('order')
                ->get()
                ->map(fn (UnitContent $uc) => [
                    'type' => $uc->contentable_type,
                    'id' => $uc->contentable_id,
                    'order' => $uc->order,
                ])
                ->toArray();
        });
    }

    public function unregister(string $type, int $contentId): void
    {
        $uc = UnitContent::where('contentable_type', $type)
            ->where('contentable_id', $contentId)
            ->first();

        if ($uc) {
            $unitId = $uc->unit_id;
            $uc->delete();
            $this->reindexUnit($unitId);
        }
    }

    public function getNextOrder(int $unitId): int
    {
        $maxOrder = UnitContent::where('unit_id', $unitId)->max('order') ?? 0;

        return max(0, $maxOrder) + 1;
    }

    public function syncModelsFromUnitContents(int $unitId): void
    {
        $items = UnitContent::where('unit_id', $unitId)->get();

        foreach ($items as $item) {
            $this->syncOrderToModel($item->contentable_type, $item->contentable_id, $item->order);
        }
    }

    public function reindexUnit(int $unitId): void
    {
        DB::transaction(function () use ($unitId) {
            $items = UnitContent::where('unit_id', $unitId)
                ->orderBy('order')
                ->lockForUpdate()
                ->get();

            foreach ($items->values() as $index => $item) {
                $newOrder = $index + 1;

                $item->update(['order' => $newOrder]);

                $this->syncOrderToModel(
                    $item->contentable_type,
                    (int) $item->contentable_id,
                    $newOrder
                );
            }
        });
    }

    private function syncOrderToModel(string $type, int $id, int $order): void
    {
        match ($type) {
            'lesson' => \Modules\Schemes\Models\Lesson::withoutGlobalScopes()->where('id', $id)->update(['order' => $order]),
            'assignment' => \Modules\Learning\Models\Assignment::withoutGlobalScopes()->where('id', $id)->update(['order' => $order]),
            'quiz' => \Modules\Learning\Models\Quiz::withoutGlobalScopes()->where('id', $id)->update(['order' => $order]),
            default => null,
        };
    }
}