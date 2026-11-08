<?php

namespace App\Http\Requests;

class ProductRecommendationRequest extends AssociationRuleRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'items' => ['sometimes', 'array', 'max:50'],
            'items.*' => ['required', 'string', 'max:100', 'distinct'],
        ]);
    }
}
