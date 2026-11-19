<?php

namespace App\Http\Requests;

use App\Enums\IssuePriority;
use App\Enums\IssueProgress;
use App\Enums\IssueStatus;
use App\Enums\UserRole;
use App\Models\User;
use Onpay\Core\Http\FormRequest;

class IssueBulkRequest extends FormRequest
{
    /**
     * The bulk actions that can be applied.
     *
     * @var array<int, string>
     */
    public const array ACTIONS = ['status', 'progress', 'priority', 'assignee', 'archive'];

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
            'ids' => ['required', 'array', 'min:1', 'max:20'],
            'ids.*' => ['integer', 'distinct'],
            'action' => ['required', 'in:' . implode(',', self::ACTIONS)],
            'status' => ['required_if:action,status', 'nullable', 'enum:' . IssueStatus::class],
            'progress' => ['required_if:action,progress', 'nullable', 'enum:' . IssueProgress::class],
            'priority' => ['required_if:action,priority', 'nullable', 'enum:' . IssuePriority::class],
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
            'ids.max' => __('You may update up to :max issues at a time.'),
            'assigned_to.in' => __('The assignee must be a Programmer.'),
        ];
    }
}
