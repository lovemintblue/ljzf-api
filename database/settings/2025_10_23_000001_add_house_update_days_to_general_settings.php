<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.house_update_days', 30);
    }

    public function down(): void
    {
        $this->migrator->delete('general.house_update_days');
    }
};

