<?php

namespace Modules\Common\Http\Controllers;

use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Common\Http\Requests\CategoryStoreRequest;
use Modules\Common\Http\Requests\CategoryUpdateRequest;
use Modules\Common\Services\CategoryService;

/**
 * @tags Data Master
 */
class CategoriesController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly CategoryService $service) {}

    /**
     * @summary Daftar Kategori
     */
    public function index(Request $request)
    {
        $perPage = max(1, (int) $request->get('per_page', 15));
        $paginator = $this->service->paginate($perPage);

        return $this->paginateResponse($paginator);
    }

    /**
     * @summary Buat Kategori Baru
     */
    public function store(CategoryStoreRequest $request)
    {
        $category = $this->service->create($request->validated());

        return $this->created(['category' => $category], 'Kategori dibuat');
    }

    /**
     * @summary Detail Kategori
     */
    public function show(int $category)
    {
        $model = $this->service->find($category);
        if (! $model) {
            return $this->error('Kategori tidak ditemukan', 404);
        }

        return $this->success(['category' => $model]);
    }

    /**
     * @summary Perbarui Kategori
     */
    public function update(CategoryUpdateRequest $request, int $category)
    {
        $updated = $this->service->update($category, $request->validated());
        if (! $updated) {
            return $this->error('Kategori tidak ditemukan', 404);
        }

        return $this->success(['category' => $updated], 'Kategori diperbarui');
    }

    /**
     * @summary Hapus Kategori
     */
    public function destroy(int $category)
    {
        $deleted = $this->service->delete($category);
        if (! $deleted) {
            return $this->error('Kategori tidak ditemukan', 404);
        }

        return $this->success([], 'Kategori dihapus');
    }
}
