<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Reporter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReporterViewController extends Controller
{
    public function dashboard(Request $request): Response
    {
        $reporter = null;
        if (session()->has('reporter_id')) {
            $reporter = Reporter::query()->find(session('reporter_id'));
        }

        return Inertia::render('Reporter/Dashboard', [
            'reporter' => $reporter ? [
                'id' => $reporter->id,
                'email' => $reporter->email,
                'reputation' => $reporter->reputation,
            ] : null,
        ]);
    }
}
