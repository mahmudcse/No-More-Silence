<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\IncidentStatus;
use App\Enums\Platform;
use App\Enums\ReportCategory;
use App\Enums\SubmittedVia;
use App\Enums\Visibility;
use App\Enums\WorkflowStatus;
use App\Models\District;
use App\Models\FacebookProfile;
use App\Models\Incident;
use App\Models\Report;
use App\Services\StatsService;
use Illuminate\Database\Seeder;

class SoftLaunchSeeder extends Seeder
{
    public function run(): void
    {
        $dhaka = District::query()->where('slug', 'dhaka')->first();
        $chattogram = District::query()->where('slug', 'chattogram')->first();
        $sylhet = District::query()->where('slug', 'sylhet')->first();

        // 1. Create Verified Incidents
        $incident1 = Incident::firstOrCreate(
            ['slug' => 'dhaka-university-protest-2026'],
            [
                'title_bn' => 'ঢাকা বিশ্ববিদ্যালয় নারী ও শিক্ষার্থী নিরাপত্তা আন্দোলন',
                'title_en' => 'Dhaka University Student Safety & Anti-Harassment Movement',
                'summary' => 'ক্যাম্পাসে নারী শিক্ষার্থীদের বিরুদ্ধে সংঘটিত নিপীড়ন ও সাইবার বুলিং বিরোধী আন্দোলন।',
                'district_id' => $dhaka?->id,
                'occurred_on' => now()->subDays(15),
                'status' => IncidentStatus::Public,
            ]
        );

        $incident2 = Incident::firstOrCreate(
            ['slug' => 'sylhet-cyber-threat-case-2026'],
            [
                'title_bn' => 'সিলেট অনলাইন যৌন হয়রানি ও হুমকি ঘটনা',
                'title_en' => 'Sylhet Online Cyber Threat & Harassment Incident',
                'summary' => 'সামাজিক যোগাযোগ মাধ্যমে সংঘবদ্ধ ট্রলিং ও ধর্ষণের হুমকি প্রদানের ঘটনা।',
                'district_id' => $sylhet?->id,
                'occurred_on' => now()->subDays(30),
                'status' => IncidentStatus::Public,
            ]
        );

        // 2. Create Subject Profiles
        $profile1 = FacebookProfile::firstOrCreate(
            ['canonical_url' => 'https://www.facebook.com/profile.php?id=100099001122334'],
            [
                'current_display_name' => 'Monirul Islam',
                'fb_numeric_id' => '100099001122334',
                'district_id' => $dhaka?->id,
                'public_reports_count' => 1,
                'first_recorded_at' => now()->subDays(14),
                'last_recorded_at' => now()->subDays(14),
            ]
        );

        $profile2 = FacebookProfile::firstOrCreate(
            ['canonical_url' => 'https://www.facebook.com/profile.php?id=100088112233445'],
            [
                'current_display_name' => 'Rakib Hossain',
                'fb_numeric_id' => '100088112233445',
                'district_id' => $chattogram?->id,
                'public_reports_count' => 1,
                'first_recorded_at' => now()->subDays(28),
                'last_recorded_at' => now()->subDays(28),
            ]
        );

        // 3. Create Verified Public Reports
        Report::firstOrCreate(
            ['uuid' => '00000000-0000-4000-8000-000000000001'],
            [
                'tracking_code_hash' => hash('sha256', 'DEMO-TRACK-001'),
                'facebook_profile_id' => $profile1->id,
                'incident_id' => $incident1->id,
                'submitted_profile_url' => $profile1->canonical_url,
                'submitted_comment_url' => 'https://www.facebook.com/posts/101/comments/201',
                'comment_text' => 'মেয়েরা রাতের বেলা ক্যাম্পাসে বের হলে এমন ঘটনাই ঘটবে, দোষ তো ওদের নিজেদের পোশাকেরই।',
                'comment_text_translation' => 'If women go out at night on campus, this will happen, it is their own clothes to blame.',
                'category' => ReportCategory::VictimBlaming,
                'workflow_status' => WorkflowStatus::Verified,
                'visibility' => Visibility::Public,
                'platform' => Platform::Facebook,
                'submitted_via' => SubmittedVia::Web,
                'verified_at' => now()->subDays(12),
                'published_at' => now()->subDays(12),
            ]
        );

        Report::firstOrCreate(
            ['uuid' => '00000000-0000-4000-8000-000000000002'],
            [
                'tracking_code_hash' => hash('sha256', 'DEMO-TRACK-002'),
                'facebook_profile_id' => $profile2->id,
                'incident_id' => $incident2->id,
                'submitted_profile_url' => $profile2->canonical_url,
                'submitted_comment_url' => 'https://www.facebook.com/posts/102/comments/202',
                'comment_text' => 'অনলাইনে যারা এত কথা বলে তাদের রাস্তায় পেলে ঠিক শিক্ষা দেওয়া হবে।',
                'comment_text_translation' => 'Those who talk so much online will be taught a lesson on the streets.',
                'category' => ReportCategory::SexualThreat,
                'workflow_status' => WorkflowStatus::Verified,
                'visibility' => Visibility::Featured,
                'platform' => Platform::Facebook,
                'submitted_via' => SubmittedVia::Web,
                'verified_at' => now()->subDays(25),
                'published_at' => now()->subDays(25),
            ]
        );

        // 4. Invalidate and refresh statistics cache
        app(StatsService::class)->clearCache();
    }
}
