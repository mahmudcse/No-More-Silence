<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ReportCategory;
use App\Models\District;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SubmissionViewController extends Controller
{
    public function create(Request $request): Response
    {
        $districts = District::query()
            ->select(['id', 'name_bn', 'name_en'])
            ->orderBy('name_en')
            ->get();

        $categories = collect(ReportCategory::cases())->map(fn (ReportCategory $cat): array => [
            'value' => $cat->value,
            'label_en' => $cat->labelEn(),
            'label_bn' => $cat->labelBn(),
        ]);

        $sharedData = null;
        if ($request->filled('share_id')) {
            $shareKey = 'share_target:'.(string) $request->query('share_id');
            /** @var array<string, mixed>|null $cachedShare */
            $cachedShare = session()->get($shareKey);
            $sharedData = $cachedShare;
        }

        return Inertia::render('Submissions/Create', [
            'districts' => $districts,
            'categories' => $categories,
            'turnstileSiteKey' => config('services.turnstile.site_key'),
            'sharedData' => $sharedData,
        ]);
    }

    public function shareTarget(Request $request): RedirectResponse
    {
        $title = (string) $request->input('title', '');
        $text = (string) $request->input('text', '');
        $url = (string) $request->input('url', '');

        // Extract potential Facebook URL from url or text
        $combinedText = $url.' '.$text;
        $extractedUrl = null;
        if (preg_match('/https?:\/\/(?:[a-zA-Z0-9-]+\.)?facebook\.com\/[^\s]+/i', $combinedText, $matches)) {
            $extractedUrl = $matches[0];
        } elseif ($url !== '') {
            $extractedUrl = $url;
        }

        $shareId = Str::random(16);
        $savedFiles = [];

        /** @var array<UploadedFile>|UploadedFile|null $evidenceFiles */
        $evidenceFiles = $request->file('evidence', []);
        if (! is_array($evidenceFiles)) {
            $evidenceFiles = $evidenceFiles ? [$evidenceFiles] : [];
        }

        foreach ($evidenceFiles as $file) {
            if ($file->isValid()) {
                $ext = $file->getClientOriginalExtension() ?: 'jpg';
                $filename = Str::random(20).'.'.$ext;
                $path = "temp-shares/{$shareId}/{$filename}";
                Storage::disk('local')->putFileAs("temp-shares/{$shareId}", $file, $filename);

                $savedFiles[] = [
                    'filename' => $filename,
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime' => $file->getClientMimeType(),
                    'url' => route('submissions.shared-file', ['shareId' => $shareId, 'filename' => $filename]),
                ];
            }
        }

        session()->put("share_target:{$shareId}", [
            'share_id' => $shareId,
            'title' => $title,
            'text' => $text,
            'extracted_url' => $extractedUrl,
            'files' => $savedFiles,
        ]);

        return redirect()->route('submit', ['share_id' => $shareId]);
    }

    public function sharedFile(string $shareId, string $filename): BinaryFileResponse
    {
        // Sanitize path to prevent directory traversal
        $safeShareId = preg_replace('/[^a-zA-Z0-9_-]/', '', $shareId);
        $safeFilename = basename($filename);

        $path = Storage::disk('local')->path("temp-shares/{$safeShareId}/{$safeFilename}");

        if (! file_exists($path)) {
            abort(404, 'Shared file not found or expired.');
        }

        return response()->file($path);
    }

    public function track(Request $request, ?string $code = null): Response
    {
        return Inertia::render('Submissions/Track', [
            'initialCode' => $code ?? $request->query('code', ''),
        ]);
    }
}
