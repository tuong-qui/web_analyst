<?php

namespace App\Http\Requests;

use App\Services\CustomerSegments;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerSegmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dataset_id' => ['nullable', 'integer', 'min:1'],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'segment' => ['nullable', Rule::in(array_keys(CustomerSegments::GROUPS))],
            'search' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', Rule::in(['monetary', 'frequency', 'recency'])],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
