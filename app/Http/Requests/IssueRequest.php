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
        return [
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'enum:' . IssueType::class],
            'priority' => ['required', 'enum:' . IssuePriority::class],
            'merchant_name' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:65535'],
        ];
    }
}
