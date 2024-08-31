<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'name'=>['string','min:3'],
            'email'=>['required','email:rfc,dns','unique:users,email'],
            'phone_number'=>['required','string','regex:/^(\+20|0)(1[0125]\d{8}|2[1-9]\d{8})$/','unique:users,phone_number'],
            'password'=>['required','confirmed','min:8','regex:/^(?=.*[A-Z])(?=.*[a-z])(?=.*[\W_])(?=.*\d).+$/']
        ];
    }

    public function messages()
    {
        return [
            'password.required' => 'The password field is required.',
            'password.confirmed' => 'The password confirmation does not match.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.regex' => 'The password must contain at least one uppercase letter, one lowercase letter, one special character, and one digit.',
            'phone_number.regex' => 'The phone number must be a valid Egyptian phone number.',
            'phone_number.unique' => 'The phone number has already been taken.',
            'email.required' => 'The email field is required.',
            'email.email' => 'The email address must be a valid email address.',
            'email.unique' => 'The email address has already been taken.',
        ];
    }
}
