<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\File;

final class UATMediaFixtures
{
    public static function basePath(): string
    {
        return database_path('fixtures/uat');
    }

    public static function ensureFilesExist(): void
    {
        $dir = self::basePath();
        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $pdf = $dir.'/sample-minimal.pdf';
        if (! File::exists($pdf)) {
            File::put($pdf, self::minimalPdf());
        }

        $png = $dir.'/sample-1x1.png';
        if (! File::exists($png)) {
            File::put($png, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='));
        }

        $txt = $dir.'/sample-readme.txt';
        if (! File::exists($txt)) {
            File::put($txt, "LEVL UAT fixture placeholder\n");
        }
    }

    public static function paths(): array
    {
        self::ensureFilesExist();
        $base = self::basePath();

        return [
            'video' => $base.'/sample-minimal.pdf',
            'image' => $base.'/sample-1x1.png',
            'excel' => $base.'/sample-readme.txt',
            'doc' => $base.'/sample-readme.txt',
            'pdf' => $base.'/sample-minimal.pdf',
        ];
    }

    private static function minimalPdf(): string
    {
        return "%PDF-1.1\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n3 0 obj<</Type/Page/MediaBox[0 0 200 200]>>endobj\ntrailer<</Root 1 0 R>>\n%%EOF";
    }
}
