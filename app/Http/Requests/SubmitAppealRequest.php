<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitAppealRequest extends FormRequest
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
            'facebook_profile_url' => ['required', 'string', 'max:500'],
            'report_uuid' => ['nullable', 'string', 'exists:reports,uuid'],
            'contact' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'ownership_proof' => ['nullable', 'string', 'max:5000'],
            'proof_file' => ['nullable', 'file', 'mimes:jpeg,jpg,png,pdf,webp', 'max:10240'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'facebook_profile_url.required' => 'ফেসবুক প্রোফাইল লিঙ্ক আবশ্যক।',
            'contact.required' => 'যোগাযোগের জন্য ইমেইল বা ফোন নম্বর প্রদান আবশ্যক।',
            'message.required' => 'আপিলের কারণ ও বিস্তারিত ব্যাখ্যা প্রদান করুন।',
            'message.min' => 'ব্যাখ্যা অন্তত ১০ অক্ষরের হতে হবে।',
        ];
    }
}
