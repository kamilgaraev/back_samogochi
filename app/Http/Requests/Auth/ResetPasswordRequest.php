<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
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
            'email' => 'required|email|max:255',
            'token' => 'required|string',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/'
            ],
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
            'email.required' => 'Email обязателен для заполнения',
            'email.email' => 'Введите корректный email адрес',
            'email.max' => 'Email не должен превышать 255 символов',
            
            'token.required' => 'Токен сброса пароля обязателен',
            'token.string' => 'Некорректный токен сброса пароля',
            
            'password.required' => 'Новый пароль обязателен для заполнения',
            'password.min' => 'Пароль должен содержать минимум 8 символов',
            'password.confirmed' => 'Подтверждение пароля не совпадает',
            'password.regex' => 'Пароль должен содержать: минимум 1 строчную букву, 1 заглавную букву, 1 цифру и 1 специальный символ (@$!%*?&)',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'email' => 'email',
            'token' => 'токен',
            'password' => 'пароль',
            'password_confirmation' => 'подтверждение пароля',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Ошибки валидации',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}
