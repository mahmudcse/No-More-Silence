<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\HasPublicUuid;
use App\Enums\FeedbackStatus;
use App\Enums\FeedbackType;
use Database\Factories\FeedbackFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property FeedbackType $type
 * @property FeedbackStatus $status
 * @property array<string, mixed>|null $device_info
 */
#[Fillable(['type', 'title', 'description', 'page_url', 'contact_email', 'device_info', 'status', 'admin_notes', 'ip_hash'])]
class Feedback extends Model
{
    /** @use HasFactory<FeedbackFactory> */
    use HasFactory;

    use HasPublicUuid;

    protected $table = 'feedback';

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'type' => FeedbackType::class,
            'status' => FeedbackStatus::class,
            'contact_email' => 'encrypted',
            'device_info' => 'array',
        ];
    }
}
