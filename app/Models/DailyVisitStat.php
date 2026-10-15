<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\DailyVisitStatFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyVisitStat extends Model
{
    /** @use HasFactory<DailyVisitStatFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'date',
        'pageviews',
        'unique_visitors',
        'top_pages',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'pageviews' => 'integer',
            'unique_visitors' => 'integer',
            'top_pages' => 'array',
        ];
    }
}
