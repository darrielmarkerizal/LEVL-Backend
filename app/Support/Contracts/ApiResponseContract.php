<?php

namespace App\Support\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

interface ApiResponseContract
{
    /**
     * Generic success response
     */
    public function success(
        mixed $data = null,
        string $message = 'Berhasil',
        int $status = 200,
        ?array $meta = null
    ): JsonResponse;

    /**
     * 201 Created response
     */
    public function created(
        mixed $data = null,
        string $message = 'Berhasil dibuat',
        ?array $meta = null
    ): JsonResponse;

    /**
     * Generic error response
     */
    public function error(
        string $message = 'Terjadi kesalahan',
        int $status = 400,
        ?array $errors = null,
        mixed $data = null,
        ?array $meta = null
    ): JsonResponse;

    /**
     * Paginated response
     */
    public function paginateResponse(
        LengthAwarePaginator $paginator,
        string $message = 'Berhasil',
        int $status = 200
    ): JsonResponse;

    /**
     * Validation error (422)
     */
    public function validationError(
        array $errors,
        string $message = 'Validasi data gagal'
    ): JsonResponse;

    /**
     * Resource not found (404)
     */
    public function notFound(
        string $message = 'Resource tidak ditemukan'
    ): JsonResponse;

    /**
     * Unauthorized (401)
     */
    public function unauthorized(
        string $message = 'Tidak terotorisasi'
    ): JsonResponse;

    /**
     * Forbidden (403)
     */
    public function forbidden(
        string $message = 'Akses ditolak'
    ): JsonResponse;

    /**
     * 204 No Content
     */
    public function noContent(): JsonResponse;
}
