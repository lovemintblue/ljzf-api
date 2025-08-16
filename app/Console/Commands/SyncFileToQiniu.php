<?php

namespace App\Console\Commands;

use App\Models\Carousel;
use App\Models\Community;
use App\Models\Facility;
use App\Models\House;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SyncFileToQiniu extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-file-to-qiniu';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '同步文件到七牛';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('同步小区图片开始');
        $communities = Community::query()->whereNotNull('image')->get();
        foreach ($communities as $community) {
            $filename = $community->image;
            Storage::disk('qiniu')->put($filename, file_get_contents(Storage::url($filename)));
            foreach ($community->album as $album) {
                $filename = $album;
                Storage::disk('qiniu')->put($filename, file_get_contents(Storage::url($filename)));
            }
        }
        $this->info('同步小区图片完成');

        $this->info('--同步用户头像开始--');
        $users = User::query()->whereNotNull('avatar')->get();
        foreach ($users as $user) {
            $filename = $user->avatar;
            Storage::disk('qiniu')->put($filename, file_get_contents(Storage::url($filename)));
        }
        $this->info('--同步用户头像完成--');

        $this->info('--同步房源图片开始--');
        $houses = House::query()->whereNotNull('cover_image')->get();
        foreach ($houses as $house) {
            $filename = $house->cover_image;
            Storage::disk('qiniu')->put($filename, file_get_contents(Storage::url($filename)));
            $filename = $house->video;
            Storage::disk('qiniu')->put($filename, file_get_contents(Storage::url($filename)));
            foreach ($house->album as $album) {
                $filename = $album;
                Storage::disk('qiniu')->put($filename, file_get_contents(Storage::url($filename)));
            }
        }
        $this->info('--同步房源图片完成--');

        $this->info('--同步轮播图开始--');
        $carousels = Carousel::query()->whereNotNull('image')->get();
        foreach ($carousels as $carousel) {
            $filename = $carousel->image;
            Storage::disk('qiniu')->put($filename, file_get_contents(Storage::url($filename)));
        }
        $this->info('--同步轮播图完成--');

        $this->info('--同步基础设施开始--');
        $facilities = Facility::query()->whereNotNull('icon')->get();
        foreach ($facilities as $facility) {
            $filename = $facility->icon;
            Storage::disk('qiniu')->put($filename, file_get_contents(Storage::url($filename)));
        }
        $this->info('--同步基础设施完成--');
    }
}
