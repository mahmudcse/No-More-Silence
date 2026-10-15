<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ReportCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'submitted_profile_url' => ['required', 'string', 'max:500'],
            'submitted_comment_url' => ['nullable', 'string', 'max:500'],
            'comment_text' => ['nullable', 'string', 'max:5000'],
            'category' => ['nullable', Rule::enum(ReportCategory::class)],
            'incident_id' => ['nullable', 'integer', 'exists:incidents,id'],
            'new_incident_title' => ['nullable', 'string', 'max:255'],
            'new_incident_district_id' => ['nullable', 'integer', 'exists:districts,id'],
            'reporter_email' => ['nullable', 'email', 'max:255'],
            'turnstile_token' => ['nullable', 'string'],
            'evidence' => ['required', 'array', 'min:1', 'max:5'],
            'evidence.*' => ['file', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'submitted_profile_url.required' => 'মন্তব্যকারীর ফেসবুক প্রোফাইল লিঙ্ক আবশ্যক।',
            'evidence.required' => 'অন্তত একটি প্রমাণভিত্তিক স্ক্রিনশট আপলোড করতে হবে।',
            'evidence.max' => 'সর্বোচ্চ ৫টি স্ক্রিনশট আপলোড করা যাবে।',
            'evidence.*.max' => 'প্রতিটি ফাইলের আকার সর্বোচ্চ ১০ মেগাবাইট হতে পারে।',
            'evidence.*.mimes' => 'শুধুমাত্র JPG, PNG বা WebP ফরম্যাটের ছবি গ্রহণযোগ্য।',
        ];
    }
}
