<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration {
    public function up(): void
    {
        $this->migrator->add('general.user_level_benefit', '');
        $this->migrator->add('general.user_level_rule', '');
        $this->migrator->add('general.user_level_other', '');
        $this->migrator->add('general.join_us', '');
        $this->migrator->add('general.company_intro', '');
        $this->migrator->add('general.privacy_policy', '');
    }

    public function down(): void
    {
        $this->migrator->delete('general.user_level_benefit');
        $this->migrator->delete('general.user_level_rule');
        $this->migrator->delete('general.user_level_other');
        $this->migrator->delete('general.join_us');
        $this->migrator->delete('general.company_intro');
        $this->migrator->delete('general.privacy_policy');
    }
};
