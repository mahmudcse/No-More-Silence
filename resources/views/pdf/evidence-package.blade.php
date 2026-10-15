<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <title>Evidence Package: {{ $profile->current_display_name }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #1e293b;
            margin: 0;
            padding: 20px;
        }
        .header {
            border-bottom: 2px solid #be123c;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            color: #881337;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .subtitle {
            font-size: 10px;
            color: #64748b;
            margin-top: 4px;
        }
        .badge {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            display: inline-block;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #0f172a;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
            margin-top: 15px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        table.meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table.meta-table td {
            padding: 5px 8px;
            border: 1px solid #e2e8f0;
        }
        table.meta-table td.label {
            font-weight: bold;
            background-color: #f8fafc;
            width: 30%;
            color: #334155;
        }
        .report-box {
            border: 1px solid #cbd5e1;
            background-color: #ffffff;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 12px;
            page-break-inside: avoid;
        }
        .report-header {
            border-bottom: 1px dashed #e2e8f0;
            padding-bottom: 6px;
            margin-bottom: 6px;
        }
        .comment-text {
            background-color: #f8fafc;
            border-left: 3px solid #be123c;
            padding: 8px 10px;
            font-style: italic;
            margin: 6px 0;
            font-size: 11px;
        }
        .hash-code {
            font-family: monospace;
            font-size: 9px;
            color: #475569;
            background-color: #f1f5f9;
            padding: 2px 4px;
            border-radius: 2px;
            word-break: break-all;
        }
        .footer {
            margin-top: 30px;
            border-top: 1px solid #cbd5e1;
            padding-top: 10px;
            font-size: 9px;
            color: #64748b;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">NO MORE SILENCE: OFFICIAL EVIDENCE DOSSIER</div>
        <div class="subtitle">
            Document Reference: NMS-EV-{{ substr($profile->uuid, 0, 8) }} | Generated: {{ now()->toDateTimeString() }} UTC
        </div>
        <div class="subtitle">
            Purpose: Official evidence package for Law Enforcement (Police Cyber Crime Unit), BTRC, and Legal Proceedings.
        </div>
    </div>

    <div class="section-title">1. Documented Subject Profile Information</div>
    <table class="meta-table">
        <tr>
            <td class="label">Display Name</td>
            <td><strong>{{ $profile->current_display_name ?? 'Facebook User' }}</strong></td>
        </tr>
        <tr>
            <td class="label">Permanent Canonical URL</td>
            <td><span class="hash-code">{{ $profile->canonical_url }}</span></td>
        </tr>
        <tr>
            <td class="label">Numeric Facebook ID</td>
            <td>{{ $profile->fb_numeric_id ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Self-Declared District</td>
            <td>{{ $profile->district?->name_en ?? 'Unknown' }}</td>
        </tr>
        <tr>
            <td class="label">Total Verified Public Records</td>
            <td><strong>{{ $reports->count() }}</strong> verified public comments</td>
        </tr>
        <tr>
            <td class="label">Record Period</td>
            <td>{{ $profile->first_recorded_at?->toDateString() }} to {{ $profile->last_recorded_at?->toDateString() }}</td>
        </tr>
    </table>

    <div class="section-title">2. Evidentiary Records & Checksums</div>

    @forelse($reports as $index => $report)
        <div class="report-box">
            <div class="report-header">
                <strong>Record #{{ $index + 1 }}:</strong>
                UUID: <span class="hash-code">{{ $report->uuid }}</span> |
                Category: <strong>{{ $report->category?->labelEn() ?? 'Record' }}</strong> |
                Verified Date: {{ $report->verified_at?->toDateTimeString() }}
            </div>

            @if($report->incident)
                <div style="font-size: 10px; color: #475569; margin-bottom: 4px;">
                    <strong>Context / Linked Incident:</strong> {{ $report->incident->title_en }} ({{ $report->incident->district?->name_en ?? 'National' }})
                </div>
            @endif

            <div class="comment-text">
                "{{ $report->comment_text }}"
            </div>

            @if($report->comment_text_translation)
                <div style="font-size: 10px; color: #64748b; margin-bottom: 6px;">
                    <em>Translation:</em> {{ $report->comment_text_translation }}
                </div>
            @endif

            <div style="font-size: 9px; color: #64748b;">
                @php
                    $media = $report->getFirstMedia('original') ?? $report->getFirstMedia('redacted');
                @endphp
                @if($media)
                    <div><strong>SHA-256 Integrity Hash:</strong> <span class="hash-code">{{ $media->getCustomProperty('sha256') ?? 'N/A' }}</span></div>
                    <div><strong>Perceptual Hash (pHash):</strong> <span class="hash-code">{{ $media->getCustomProperty('phash') ?? 'N/A' }}</span></div>
                @endif
                <div><strong>Source Comment URL:</strong> {{ $report->submitted_comment_url ?? 'Preserved via Screenshot Corroboration' }}</div>
            </div>
        </div>
    @empty
        <p>No verified public records found for this profile.</p>
    @endforelse

    <div class="footer">
        <div><strong>Legal Notice:</strong> This document contains verified records of public statements made on social networks. Victim identities have been redacted in strict compliance with Section 14 of the Nari o Shishu Nirjatan Daman Ain 2000.</div>
        <div>Certified by No More Silence Evidence Verification Board under 8-Point Verification Standard.</div>
    </div>
</body>
</html>
