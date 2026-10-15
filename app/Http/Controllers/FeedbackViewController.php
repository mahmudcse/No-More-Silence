<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\FeedbackType;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FeedbackViewController extends Controller
{
    public function index(Request $request): Response
    {
        $types = collect(FeedbackType::cases())->map(fn (FeedbackType $type): array => [
            'value' => $type->value,
            'label_en' => $type->labelEn(),
            'label_bn' => $type->labelBn(),
        ]);

        return Inertia::render('Feedback/Index', [
            'types' => $types,
            'initialPageUrl' => $request->query('url', ''),
            'initialType' => $request->query('type', 'bug_report'),
        ]);
    }
}
