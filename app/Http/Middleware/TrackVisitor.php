<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\VisitTrackerService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitor
{
    public function __construct(private readonly VisitTrackerService $tracker) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track successful GET responses
        if ($request->isMethod('GET') && $response->isSuccessful()) {
            $this->tracker->recordVisit($request);
        }

        return $response;
    }
}
