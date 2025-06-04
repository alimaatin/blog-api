<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
            /**
             * Either email or username needed
             * @example alimatin1010@gmail.com
             */
            "email" => "required_without:username|string|email",
            /**
             * Either username or email needed
             * @example alimatin
             */
            "username" => "required_without:email|string",
            /**
             * @example alimatin
             */
            "password" => "required|string",
            /**
             * @example true
             */
            "remember" => "nullable|boolean",
        ];
    }
}
