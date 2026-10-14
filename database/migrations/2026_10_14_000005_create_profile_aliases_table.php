<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_aliases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facebook_profile_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('value');
            $table->string('value_normalized')->nullable()->index();
            $table->timestamp('seen_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_aliases');
    }
};
