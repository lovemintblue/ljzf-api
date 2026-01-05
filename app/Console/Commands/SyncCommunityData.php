<?php

namespace App\Console\Commands;

use App\Models\Community;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SyncCommunityData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-community-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '同步小区数据';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Community::query()->truncate();
        $key = 'a94bbfa854c00af7a92f7f33d04e907f';
        $api = 'https://restapi.amap.com/v3/place/text';
        $page = 1;


        do {
            $response = Http::get($api, [
                'key' => $key,
                'keywords' => '',
                'types' => '120300',
                'city' => '360702',
                'page' => $page
            ]);

            $result = $response->json();

            if (count($result['pois']) === 0) {
                break;
            }

            foreach ($result['pois'] as $poi) {
//                dd($poi);
                $community = new Community();
                $community->name = $poi['name'];
                $community->province = $poi['pname'];
                $community->city = $poi['cityname'];
                $community->district = $poi['adname'];
                $community->address = is_array($poi['address']) ? '' : $poi['address'];
                $community->save();
            }
            $page++;
            // 延时3秒
            sleep(3);
        } while (true);

        dd($response->json());
    }
}
