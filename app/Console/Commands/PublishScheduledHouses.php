<?php

namespace App\Console\Commands;

use App\Models\House;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PublishScheduledHouses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'houses:publish-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '发布到期的预约上架房源';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('开始检查预约上架房源...');

        // 查找所有到期的预约上架房源
        $houses = House::where('scheduled_publish_at', '<=', now())
            ->whereNotNull('scheduled_publish_at')
            ->where('is_show', 0)
            ->get();

        $count = 0;
        foreach ($houses as $house) {
            try {
                // 上架房源
                $house->is_show = 1;
                $house->scheduled_publish_at = null;
                $house->save();

                $count++;
                $this->info("房源 #{$house->id} ({$house->title}) 已自动上架");
                
                Log::info("预约上架房源成功", [
                    'house_id' => $house->id,
                    'title' => $house->title,
                    'scheduled_at' => $house->scheduled_publish_at
                ]);
            } catch (\Exception $e) {
                $this->error("房源 #{$house->id} 上架失败: " . $e->getMessage());
                Log::error("预约上架房源失败", [
                    'house_id' => $house->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->info("共上架 {$count} 套房源");
        
        return Command::SUCCESS;
    }
}

