<?php

namespace App\Http\Controllers;

use App\Services\UploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class FileTestController extends Controller
{
    public function __construct(
        protected UploadService $uploadService
    ) {}

    /**
     * Get storage configuration info
     */
    public function config(): JsonResponse
    {
        $diskName = config('filesystems.default');
        $diskConfig = config("filesystems.disks.{$diskName}");
        
        return response()->json([
            'default_disk' => $diskName,
            'disk_config' => [
                'driver' => $diskConfig['driver'] ?? null,
                'bucket' => $diskConfig['bucket'] ?? null,
                'region' => $diskConfig['region'] ?? null,
                'endpoint' => $diskConfig['endpoint'] ?? null,
                'url' => $diskConfig['url'] ?? null,
                'has_key' => !empty($diskConfig['key']),
                'has_secret' => !empty($diskConfig['secret']),
            ],
            'env_vars' => [
                'FILESYSTEM_DISK' => env('FILESYSTEM_DISK'),
                'DO_USE_CDN' => env('DO_USE_CDN'),
                'IMAGE_QUALITY' => env('IMAGE_QUALITY', 80),
            ],
        ]);
    }

    /**
     * Test file upload
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB
            'directory' => 'string|nullable',
        ]);

        try {
            $file = $request->file('file');
            $directory = $request->input('directory', 'test/uploads');
            
            Log::info('Test upload started', [
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
                'directory' => $directory,
            ]);

            // Upload file
            $path = $this->uploadService->storePublic($file, $directory);
            
            // Get URL
            $url = $this->uploadService->getPublicUrl($path);
            
            // Check if file exists
            $exists = $this->uploadService->exists($path);
            
            // Try to get file info from storage
            $disk = Storage::disk(config('filesystems.default'));
            $fileSize = $exists ? $disk->size($path) : null;
            $lastModified = $exists ? $disk->lastModified($path) : null;
            
            Log::info('Test upload completed', [
                'path' => $path,
                'url' => $url,
                'exists' => $exists,
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'path' => $path,
                    'url' => $url,
                    'exists' => $exists,
                    'size' => $fileSize,
                    'last_modified' => $lastModified ? date('Y-m-d H:i:s', $lastModified) : null,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Test upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ], 500);
        }
    }

    /**
     * List files in a directory
     */
    public function list(Request $request): JsonResponse
    {
        $directory = $request->input('directory', 'test/uploads');
        $disk = Storage::disk(config('filesystems.default'));
        
        try {
            $files = $disk->allFiles($directory);
            
            $fileList = collect($files)->map(function ($path) use ($disk) {
                return [
                    'path' => $path,
                    'url' => $this->uploadService->getPublicUrl($path),
                    'size' => $disk->size($path),
                    'last_modified' => date('Y-m-d H:i:s', $disk->lastModified($path)),
                ];
            })->values();

            return response()->json([
                'success' => true,
                'data' => [
                    'directory' => $directory,
                    'count' => $fileList->count(),
                    'files' => $fileList,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if file exists and is accessible
     */
    public function check(Request $request): JsonResponse
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        $path = $request->input('path');
        $disk = Storage::disk(config('filesystems.default'));
        
        try {
            $exists = $disk->exists($path);
            $url = $this->uploadService->getPublicUrl($path);
            
            $response = [
                'success' => true,
                'data' => [
                    'path' => $path,
                    'exists' => $exists,
                    'url' => $url,
                ],
            ];

            if ($exists) {
                $response['data']['size'] = $disk->size($path);
                $response['data']['last_modified'] = date('Y-m-d H:i:s', $disk->lastModified($path));
                
                // Try to check if URL is accessible
                $response['data']['url_check'] = $this->checkUrlAccessibility($url);
            }

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a file
     */
    public function delete(Request $request): JsonResponse
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        $path = $request->input('path');
        
        try {
            $existed = $this->uploadService->exists($path);
            $this->uploadService->deletePublic($path);
            $stillExists = $this->uploadService->exists($path);

            return response()->json([
                'success' => true,
                'data' => [
                    'path' => $path,
                    'existed_before' => $existed,
                    'deleted' => $existed && !$stillExists,
                    'still_exists' => $stillExists,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test direct S3 operations
     */
    public function testS3Operations(): JsonResponse
    {
        try {
            $disk = Storage::disk('do');
            $testPath = 'test/s3-test-' . time() . '.txt';
            $testContent = 'Test S3 operations at ' . now()->toDateTimeString();
            
            Log::info('Testing S3 operations', ['path' => $testPath]);

            // Test 1: Put file with explicit options
            $put1 = $disk->put($testPath, $testContent, [
                'visibility' => 'public',
                'ACL' => 'public-read',
                'ContentType' => 'text/plain',
            ]);
            
            // Test 2: Check if exists
            $exists = $disk->exists($testPath);
            
            // Test 3: Get URL
            $url = $disk->url($testPath);
            
            // Test 4: Get file info
            $size = $exists ? $disk->size($testPath) : null;
            $lastModified = $exists ? $disk->lastModified($testPath) : null;
            
            // Test 5: Check URL accessibility
            $urlCheck = $this->checkUrlAccessibility($url);
            
            // Cleanup
            $disk->delete($testPath);

            return response()->json([
                'success' => true,
                'tests' => [
                    'put_with_options' => $put1,
                    'file_exists' => $exists,
                    'url_generated' => $url,
                    'size' => $size,
                    'last_modified' => $lastModified ? date('Y-m-d H:i:s', $lastModified) : null,
                    'url_accessible' => $urlCheck,
                    'cleaned_up' => !$disk->exists($testPath),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('S3 operations test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ], 500);
        }
    }

    /**
     * Check if URL is accessible via HTTP
     */
    protected function checkUrlAccessibility(?string $url): array
    {
        if (!$url) {
            return [
                'accessible' => false,
                'reason' => 'No URL provided',
            ];
        }

        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            return [
                'accessible' => $httpCode === 200,
                'http_code' => $httpCode,
                'error' => $error ?: null,
            ];
        } catch (\Exception $e) {
            return [
                'accessible' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
