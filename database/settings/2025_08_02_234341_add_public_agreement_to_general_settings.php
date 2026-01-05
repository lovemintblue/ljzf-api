<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration {
    public function up(): void
    {
        $this->migrator->add('general.public_agreement', '');
    }

    public function down(): void
    {
        $this->migrator->delete('general.public_agreement');
    }
};
