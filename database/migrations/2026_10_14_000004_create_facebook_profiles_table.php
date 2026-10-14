<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facebook_profiles', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('fb_numeric_id')->nullable()->unique();
            $table->string('canonical_url')->unique();
            $table->string('current_display_name')->nullable();
            $table->string('display_name_normalized')->nullable()->index();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->string('district_source')->default('unknown');
            $table->string('profile_status')->default('unknown');
            $table->string('platform')->default('facebook');
            $table->timestamp('last_checked_at')->nullable();
            $table->unsignedInteger('public_reports_count')->default(0);
            $table->timestamp('first_recorded_at')->nullable();
            $table->timestamp('last_recorded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facebook_profiles');
    }
};
