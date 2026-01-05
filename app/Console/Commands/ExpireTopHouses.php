<?php

namespace App\Console\Commands;

use App\Models\House;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ExpireTopHouses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'houses:expire-top';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '检查并关闭已到期的推广房源';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('开始检查已到期的推广房源...');

        $today = Carbon::today();

        // 查找所有已置顶且到期日期小于今天的房源
        $expiredHouses = House::where('is_top', true)
            ->whereNotNull('top_expires_at')
            ->where('top_expires_at', '<', $today)
            ->get();

        if ($expiredHouses->isEmpty()) {
            $this->info('没有需要处理的到期推广房源。');
            return Command::SUCCESS;
        }

        $count = 0;
        foreach ($expiredHouses as $house) {
            $house->is_top = false;
            $house->top_at = null;
            // 保留 top_expires_at 以便查看历史记录
            $house->save();
            
            $this->info("房源 #{$house->id} (NO: {$house->no}) 推广已到期，已自动关闭。到期日期: {$house->top_expires_at}");
            $count++;
        }

        $this->info("共处理了 {$count} 个到期推广房源。");

        return Command::SUCCESS;
    }
}

