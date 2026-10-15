<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('type')->default('bug_report');
            $table->string('title');
            $table->text('description');
            $table->string('page_url')->nullable();
            $table->text('contact_email')->nullable();
            $table->json('device_info')->nullable();
            $table->string('status')->default('open');
            $table->text('admin_notes')->nullable();
            $table->string('ip_hash')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
