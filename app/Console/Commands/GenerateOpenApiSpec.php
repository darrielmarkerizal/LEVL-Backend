<?php

namespace App\Console\Commands;

use App\Services\OpenApiGeneratorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateOpenApiSpec extends Command
{
    protected $signature = 'openapi:generate {--output=storage/api-docs/openapi.json}';

    protected $description = 'Generate OpenAPI specification from Laravel routes organized by features';

    public function handle(OpenApiGeneratorService $generator): int
    {
        $this->info('Generating OpenAPI specification...');

        try {
            $spec = $generator->generate();

            $outputPath = $this->option('output');
            $directory = dirname($outputPath);

            if (! File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            File::put($outputPath, json_encode($spec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

            $this->info("OpenAPI specification generated successfully at: {$outputPath}");
            $this->info('Total paths: ' . count($spec['paths']));
            $this->info('Total tags: ' . count($spec['tags']));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to generate OpenAPI specification: ' . $e->getMessage());
            $this->error($e->getTraceAsString());

            return Command::FAILURE;
        }
    }
}

