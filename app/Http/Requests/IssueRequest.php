<?php

namespace App\Http\Requests;

use App\Enums\IssuePriority;
use App\Enums\IssueType;
use Onpay\Core\Http\FormRequest;

class IssueRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:65535'],
        ];

        if ($this->conditions->isStoreAction) {
            $rules += [
                'type' => ['required', 'enum:' . IssueType::class],
                'priority' => ['required', 'enum:' . IssuePriority::class],
                'merchant_name' => ['nullable', 'string', 'max:255'],
                'attachments' => ['nullable', 'array', 'max:5'],
                'attachments.*' => ['file', 'mimes:jpg,jpeg,png,gif,webp,pdf', 'max:5120'],
            ];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'attachments.max' => __('You may upload up to :max files.'),
            'attachments.*.mimes' => __('Only images and PDF files are allowed.'),
            'attachments.*.max' => __('Each file may not be larger than 5 MB.'),
        ];
    }
}
