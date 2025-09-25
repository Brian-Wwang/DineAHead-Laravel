<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Models\Location;

class ImportLocationJson extends Command
{
    protected $signature = 'import:locations';
    protected $description = 'Import Cambodian provinces and districts into locations table';

    public function handle()
    {
        $jsonPath = storage_path('app/locations.json');

        if (!File::exists($jsonPath)) {
            $this->error("locations.json not found in storage/app/");
            return 1;
        }

        $raw = File::get($jsonPath);
        $data = json_decode($raw, true);

        if (!isset($data['provinces'])) {
            $this->error("Invalid JSON structure");
            return 1;
        }

        $provinces = $data['provinces'];

        // ✅ 优先导入 Phnom Penh
        usort($provinces, function ($a, $b) {
            return ($a['name'] === 'Phnom Penh') ? -1 : 1;
        });

        $this->info("🚀 开始导入 locations（省 + 市）...");

        DB::transaction(function () use ($provinces) {
            Location::truncate();

            foreach ($provinces as $province) {
                $provinceCode = (string) $province['id']; // 强制转 int

                $this->info("📍 导入省份：{$province['name']} (Code: {$provinceCode})");

                $provinceModel = Location::create([
                    'code'      => $provinceCode,
                    'name'      => $province['name'],
                    'type'      => 'province',
                    'parent_id' => null,
                ]);

                foreach ($province['districts'] as $district) {
                    $districtCode = (string) $district['id']; // 强制转 int

                    $this->info("　　└ 市场：{$district['name']} (Code: {$districtCode})");

                    Location::create([
                        'code'      => $districtCode,
                        'name'      => $district['name'],
                        'type'      => 'district',
                        'parent_id' => $provinceModel->id,
                    ]);
                }
            }
        });

        $this->info("✅ 导入成功！");
        return 0;
    }
}
