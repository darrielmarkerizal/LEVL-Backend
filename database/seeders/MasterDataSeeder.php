<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Common\Models\MasterDataItem;

class MasterDataSeeder extends Seeder
{
    
    public function run(): void
    {
        $this->seedCategories();
        $this->seedDifficultyLevels();
        $this->seedContentTypes();
    }

    private function seedCategories(): void
    {
        $categories = [
            ['value' => 'information-technology', 'label' => 'Teknologi Informasi'],
            ['value' => 'project-management', 'label' => 'Manajemen Proyek'],
            ['value' => 'marketing', 'label' => 'Pemasaran'],
            ['value' => 'finance', 'label' => 'Keuangan'],
            ['value' => 'human-resources', 'label' => 'Sumber Daya Manusia'],
            ['value' => 'english-language', 'label' => 'Bahasa Inggris'],
            ['value' => 'design', 'label' => 'Desain'],
        ];

        foreach ($categories as $index => $item) {
            $existing = MasterDataItem::where('type', 'categories')
                ->where('value', $item['value'])
                ->first();

            if (!$existing) {
                DB::table('master_data')->insert([
                    'type' => 'categories',
                    'value' => $item['value'],
                    'label' => $item['label'],
                    'is_active' => DB::raw('true'),
                    'sort_order' => $index + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function seedDifficultyLevels(): void
    {
        $difficultyLevels = [
            ['value' => 'beginner', 'label' => 'Beginner', 'label_id' => 'Pemula'],
            ['value' => 'intermediate', 'label' => 'Intermediate', 'label_id' => 'Menengah'],
            ['value' => 'advanced', 'label' => 'Advanced', 'label_id' => 'Lanjutan'],
        ];

        foreach ($difficultyLevels as $index => $item) {
            $existing = MasterDataItem::where('type', 'difficulty-levels')
                ->where('value', $item['value'])
                ->first();

            if (!$existing) {
                DB::table('master_data')->insert([
                    'type' => 'difficulty-levels',
                    'value' => $item['value'],
                    'label' => $item['label_id'],
                    'is_active' => DB::raw('true'),
                    'sort_order' => $index + 1,
                    'metadata' => json_encode(['label_en' => $item['label']]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function seedContentTypes(): void
    {
        $contentTypes = [
            ['value' => 'article', 'label' => 'Article'],
            ['value' => 'video', 'label' => 'Video'],
            ['value' => 'quiz', 'label' => 'Quiz'],
        ];

        foreach ($contentTypes as $index => $item) {
            $existing = MasterDataItem::where('type', 'content-types')
                ->where('value', $item['value'])
                ->first();

            if (!$existing) {
                DB::table('master_data')->insert([
                    'type' => 'content-types',
                    'value' => $item['value'],
                    'label' => $item['label'],
                    'is_active' => DB::raw('true'),
                    'sort_order' => $index + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private static function getRandomColor(): string
    {
        $colors = ['red', 'blue', 'green', 'yellow', 'purple', 'orange'];

        return $colors[array_rand($colors)];
    }
}
