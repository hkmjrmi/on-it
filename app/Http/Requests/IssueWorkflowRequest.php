<?php

namespace App\Http\Requests;

use App\Enums\IssuePriority;
use App\Enums\IssueProgress;
use App\Enums\IssueStatus;
use App\Enums\UserRole;
use App\Models\User;
use Onpay\Core\Http\FormRequest;

class IssueWorkflowRequest extends FormRequest
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
        $programmerIds = User::query()->where('role', UserRole::Programmer)->pluck('id');

        return [
            'status' => ['required', 'enum:' . IssueStatus::class],
            'progress' => ['required', 'enum:' . IssueProgress::class],
            'priority' => ['required', 'enum:' . IssuePriority::class],
            'assigned_to' => ['nullable', 'integer', 'in:' . $programmerIds->join(',')],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'assigned_to.in' => __('The assignee must be a Programmer.'),
        ];
    }
}
