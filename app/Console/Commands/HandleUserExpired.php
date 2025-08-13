<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class HandleUserExpired extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:handle-user-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '处理用户会员到期';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        User::query()
            ->whereNotNull('expired_at')
            ->where('expired_at', '<', now())
            ->update([
                'user_level_id' => 0,
                'expired_at' => null,
            ]);
    }
}
