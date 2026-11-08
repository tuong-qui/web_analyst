<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssociationRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dataset_id' => ['nullable', 'integer', 'min:1'],
            'support' => ['sometimes', 'required', 'numeric', 'min:0.01', 'max:100'],
            'confidence' => ['sometimes', 'required', 'numeric', 'min:0', 'max:100'],
            'lift' => ['sometimes', 'required', 'numeric', 'min:0', 'max:1000'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            '*.required' => 'Vui lòng nhập đầy đủ ngưỡng phân tích.',
            '*.numeric' => 'Ngưỡng phân tích phải là số.',
            'support.min' => 'Support phải từ 0,01% đến 100%.',
            'support.max' => 'Support phải từ 0,01% đến 100%.',
            'confidence.min' => 'Confidence phải từ 0% đến 100%.',
            'confidence.max' => 'Confidence phải từ 0% đến 100%.',
            'lift.min' => 'Lift phải từ 0 đến 1000.',
            'lift.max' => 'Lift phải từ 0 đến 1000.',
        ];
    }
}
