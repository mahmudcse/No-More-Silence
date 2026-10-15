<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm;');

            DB::statement('CREATE INDEX IF NOT EXISTS facebook_profiles_display_name_trgm_idx ON facebook_profiles USING gin (display_name_normalized gin_trgm_ops);');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS facebook_profiles_display_name_trgm_idx;');
        }
    }
};
