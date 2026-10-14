<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('tracking_code_hash')->nullable()->unique();
            $table->foreignId('reporter_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('facebook_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('incident_id')->nullable()->constrained()->nullOnDelete();
            $table->string('submitted_profile_url')->nullable();
            $table->string('submitted_comment_url')->nullable();
            $table->text('comment_text')->nullable();
            $table->text('comment_text_translation')->nullable();
            $table->timestamp('comment_posted_at')->nullable();
            $table->string('category')->nullable();
            $table->unsignedTinyInteger('severity')->nullable();
            $table->string('workflow_status')->default('submitted');
            $table->string('visibility')->default('hidden');
            $table->string('platform')->default('facebook');
            $table->string('submitted_via')->default('web');
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('duplicate_of_id')->nullable()->constrained('reports')->nullOnDelete();
            $table->text('rejected_reason')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('submitter_ip_hash')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
