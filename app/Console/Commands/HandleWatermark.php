<?php

namespace App\Console\Commands;

use App\Models\House;
use App\Services\HouseService;
use App\Services\WatermarkService;
use Faker\Provider\Image;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class HandleWatermark extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:handle-watermark';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '处理水印';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $houses = House::query()
            ->whereNotNull('video')
            ->whereNull('watermark_video')
            ->where('is_show',1)
            ->limit(1)
            ->get();
        foreach ($houses as $house) {
            (new HouseService())::handleWatermark($house);
            $this->info($house->title.':处理完成');
            Log::info($house->title.':处理完成');
        }
    }
}
