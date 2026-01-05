<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SystemInstall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:system-install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '系统安装';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('---系统开始初始化---');
        $this->call('migrate:fresh');
        $this->call('migrate');
        $this->call('db:seed');
        $this->call('storage:link');
        $this->call('shield:super-admin');
        $this->call('shield:generate', [
            '--all' => true,
            '--panel' => 'admin'
        ]);
        $this->info('---系统初始化完成---');
    }
}
