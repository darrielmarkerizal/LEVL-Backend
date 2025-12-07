<?php

namespace Modules\Schemes\Http\Controllers;

use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Schemes\Http\Requests\TagRequest;
use Modules\Schemes\Models\Tag;
use Modules\Schemes\Services\TagService;

class TagController extends Controller
{
    use ApiResponse;

    public function __construct(private TagService $service) {}

    public function index(Request $request)
    {
        $perPage = (int) ($request->query('per_page', 0));

        $result = $this->service->list($perPage);

        if ($result instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
            return $this->paginateResponse($result, 'Daftar tag berhasil diambil.');
        }

        return $this->success([
            'items' => $result,
        ], 'Daftar tag berhasil diambil.');
    }

    public function store(TagRequest $request)
    {
        $validated = $request->validated();

        if (! empty($validated['names'])) {
            $tags = $this->service->createMany($validated['names']);

            return $this->created(['tags' => $tags], 'Tag berhasil dibuat.');
        }

        $tag = $this->service->create($validated);

        return $this->created(['tag' => $tag], 'Tag berhasil dibuat.');
    }

    public function show(Tag $tag)
    {
        return $this->success(['tag' => $tag]);
    }

    public function update(TagRequest $request, Tag $tag)
    {
        $validated = $request->validated();
        unset($validated['names']);

        $updated = $this->service->update($tag->id, $validated);

        if (! $updated) {
            return $this->error('Tag tidak ditemukan.', 404);
        }

        return $this->success(['tag' => $updated], 'Tag berhasil diperbarui.');
    }

    public function destroy(Tag $tag)
    {
        $deleted = $this->service->delete($tag->id);

        if (! $deleted) {
            return $this->error('Tag tidak ditemukan.', 404);
        }

        return $this->success([], 'Tag berhasil dihapus.');
    }
}
