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
                /**
                 * @example alimatin
                 */
                "username" => "required|string|max:255|unique:users",
                /**
                 * @example alimatin
                 */
                "displayName" => "required|string|max:255",
                /**
                 * @example alimatin@gmail.com
                 */
                "email" => "required|string|email|max:255|unique:users",
                /**
                 * @example alimatin
                 */
                "password" => "required|string|min:8",
                                /**
                 * @example alimatin
                 */
                "password_confirmation" => "required|string|min:8",
        ];
    }
}
