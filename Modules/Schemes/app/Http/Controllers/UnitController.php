<?php

namespace Modules\Schemes\Http\Controllers;

use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Schemes\Http\Requests\ReorderUnitsRequest;
use Modules\Schemes\Http\Requests\UnitRequest;
use Modules\Schemes\Services\UnitService;

class UnitController extends Controller
{
    use ApiResponse;

    public function __construct(private UnitService $service) {}

    public function index(Request $request, int $course)
    {
        $params = $request->all();
        $paginator = $this->service->listByCourse($course, $params);

        return $this->paginateResponse($paginator);
    }

    public function store(UnitRequest $request, int $course)
    {
        $data = $request->validated();
        $unit = $this->service->create($course, $data);

        return $this->created(['unit' => $unit], 'Unit berhasil dibuat.');
    }

    public function show(int $course, int $unit)
    {
        $found = $this->service->show($course, $unit);
        if (! $found) {
            return $this->error('Unit tidak ditemukan.', 404);
        }

        return $this->success(['unit' => $found]);
    }

    public function update(UnitRequest $request, int $course, int $unit)
    {
        $data = $request->validated();
        $updated = $this->service->update($course, $unit, $data);
        if (! $updated) {
            return $this->error('Unit tidak ditemukan.', 404);
        }

        return $this->success(['unit' => $updated], 'Unit berhasil diperbarui.');
    }

    public function destroy(int $course, int $unit)
    {
        $ok = $this->service->delete($course, $unit);
        if (! $ok) {
            return $this->error('Unit tidak ditemukan.', 404);
        }

        return $this->success([], 'Unit berhasil dihapus.');
    }

    public function reorder(ReorderUnitsRequest $request, int $course)
    {
        $data = $request->validated();

        $unitIds = $data['units'];
        $unitsInCourse = $this->service->getRepository()->getAllByCourse($course);
        $validUnitIds = $unitsInCourse->pluck('id')->toArray();
        $invalidIds = array_diff($unitIds, $validUnitIds);

        if (! empty($invalidIds)) {
            return $this->error('Beberapa unit tidak ditemukan di course ini.', 422);
        }

        $unitOrders = [];
        foreach ($unitIds as $index => $unitId) {
            $unitOrders[$unitId] = $index + 1;
        }
        $this->service->reorder($course, $unitOrders);

        return $this->success([], 'Urutan unit berhasil diperbarui.');
    }
}
