<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ReportCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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

            // Multi-section screenshot evidence
            'evidence_post' => ['nullable', 'array', 'max:10'],
            'evidence_post.*' => ['file', 'mimes:jpeg,jpg,png,webp', 'max:10240'],

            'evidence_comment' => ['nullable', 'array', 'max:10'],
            'evidence_comment.*' => ['file', 'mimes:jpeg,jpg,png,webp', 'max:10240'],

            'evidence_profile' => ['nullable', 'array', 'max:10'],
            'evidence_profile.*' => ['file', 'mimes:jpeg,jpg,png,webp', 'max:10240'],

            'evidence_other' => ['nullable', 'array', 'max:10'],
            'evidence_other.*' => ['file', 'mimes:jpeg,jpg,png,webp', 'max:10240'],

            // Backward compatibility
            'evidence' => ['nullable', 'array', 'max:10'],
            'evidence.*' => ['file', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $hasFiles = ! empty($this->file('evidence_comment'))
                || ! empty($this->file('evidence_post'))
                || ! empty($this->file('evidence_profile'))
                || ! empty($this->file('evidence_other'))
                || ! empty($this->file('evidence'));

            if (! $hasFiles) {
                $validator->errors()->add(
                    'evidence',
                    'অন্তত একটি প্রমাণভিত্তিক স্ক্রিনশট আপলোড করতে হবে (মন্তব্য, মূল পোস্ট বা প্রোফাইল)।'
                );
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'submitted_profile_url.required' => 'মন্তব্যকারীর ফেসবুক প্রোফাইল লিঙ্ক আবশ্যক।',
            'evidence.required' => 'অন্তত একটি প্রমাণভিত্তিক স্ক্রিনশট আপলোড করতে হবে।',
            'evidence_post.*.max' => 'প্রতিটি পোস্ট স্ক্রিনশটের আকার সর্বোচ্চ ১০ মেগাবাইট হতে পারে।',
            'evidence_post.*.mimes' => 'শুধুমাত্র JPG, PNG বা WebP ফরম্যাটের ছবি গ্রহণযোগ্য।',
            'evidence_comment.*.max' => 'প্রতিটি মন্তব্য স্ক্রিনশটের আকার সর্বোচ্চ ১০ মেগাবাইট হতে পারে।',
            'evidence_comment.*.mimes' => 'শুধুমাত্র JPG, PNG বা WebP ফরম্যাটের ছবি গ্রহণযোগ্য।',
            'evidence_profile.*.max' => 'প্রতিটি প্রোফাইল স্ক্রিনশটের আকার সর্বোচ্চ ১০ মেগাবাইট হতে পারে।',
            'evidence_profile.*.mimes' => 'শুধুমাত্র JPG, PNG বা WebP ফরম্যাটের ছবি গ্রহণযোগ্য।',
            'evidence_other.*.max' => 'প্রতিটি অন্যান্য স্ক্রিনশটের আকার সর্বোচ্চ ১০ মেগাবাইট হতে পারে।',
            'evidence_other.*.mimes' => 'শুধুমাত্র JPG, PNG বা WebP ফরম্যাটের ছবি গ্রহণযোগ্য।',
        ];
    }
}
