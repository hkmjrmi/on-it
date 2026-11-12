<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Onpay\Core\Http\FormRequest;

class SavedFilterRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('saved_filters', 'name')
                    ->where('user_id', $this->user()?->id)
                    ->ignore($this->route('savedFilter')),
            ],
        ];

        if ($this->conditions->isStoreAction) {
            $rules['params'] = ['required', 'array'];
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
            'name.unique' => __('You already have a saved filter with this name.'),
        ];
    }
}
