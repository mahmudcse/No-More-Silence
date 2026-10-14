<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stats_snapshots', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('received')->default(0);
            $table->unsignedInteger('verified')->default(0);
            $table->unsignedInteger('public')->default(0);
            $table->unsignedInteger('profiles')->default(0);
            $table->timestamps();

            $table->unique(['date', 'district_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stats_snapshots');
    }
};
