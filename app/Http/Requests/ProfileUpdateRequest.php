<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'owner_name' => ['required', 'string', 'max:100'],
            'shop_name' => ['required', 'string', 'max:150'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:120',
                Rule::unique(User::class)->ignore($this->user()),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'owner_name.required' => 'Vui lòng nhập họ và tên.',
            'owner_name.max' => 'Họ và tên không được vượt quá 100 ký tự.',
            'shop_name.required' => 'Vui lòng nhập tên cửa hàng.',
            'shop_name.max' => 'Tên cửa hàng không được vượt quá 150 ký tự.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Địa chỉ email không hợp lệ.',
            'email.lowercase' => 'Vui lòng nhập email bằng chữ thường.',
            'email.max' => 'Email không được vượt quá 120 ký tự.',
            'email.unique' => 'Email này đã được sử dụng bởi tài khoản khác.',
        ];
    }
}
