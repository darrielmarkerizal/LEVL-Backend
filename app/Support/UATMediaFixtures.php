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

        
        $mp4 = $dir.'/sample-video.mp4';
        if (! File::exists($mp4)) {
            File::put($mp4, self::minimalMp4());
        }

        
        $xls = $dir.'/sample-data.xls';
        if (! File::exists($xls)) {
            File::put($xls, "Name,Value\nSample,Data\n");
        }

        
        $doc = $dir.'/sample-document.doc';
        if (! File::exists($doc)) {
            File::put($doc, self::minimalDoc());
        }
    }

    public static function paths(): array
    {
        self::ensureFilesExist();
        $base = self::basePath();

        return [
            'video' => $base.'/sample-video.mp4',
            'image' => $base.'/sample-1x1.png',
            'excel' => $base.'/sample-data.xls',
            'doc' => $base.'/sample-document.doc',
            'pdf' => $base.'/sample-minimal.pdf',
        ];
    }

    private static function minimalPdf(): string
    {
        return "%PDF-1.1\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n3 0 obj<</Type/Page/MediaBox[0 0 200 200]>>endobj\ntrailer<</Root 1 0 R>>\n%%EOF";
    }

    private static function minimalMp4(): string
    {
        
        return hex2bin('000000186674797069736f6d0000020069736f6d69736f32617663310000000c6d6461740000');
    }

    private static function minimalDoc(): string
    {
        
        return "{\\rtf1\\ansi\\deff0 {\\fonttbl {\\f0 Times New Roman;}}\n\\f0\\fs24 LEVL Sample Document\n}";
    }
}
