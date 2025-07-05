<?php
/**
 * 开发用 - 生成用户Token
 */

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class GenerateToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '快速为用户生成 token';

    /**
     * @return void
     */
    public function handle(): void
    {
        $userId = $this->ask('输入用户 id');
        $user = User::query()->find($userId);
        if (!$user) {
            $this->error('token生成失败');
        } else {
            $token = $user->createToken($user->id)->plainTextToken;
            $this->info($token);
        }
    }
}
