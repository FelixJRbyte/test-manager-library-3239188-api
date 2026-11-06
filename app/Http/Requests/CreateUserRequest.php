<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:120'],
            'password' => ['required', 'string', 'min:6', 'max:200'],
            'role' => ['required', 'string', 'in:librarian'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'username.required' => 'El nombre de usuario es obligatorio.',
            'username.string' => 'El nombre de usuario debe ser texto.',
            'username.max' => 'El nombre de usuario no puede superar 120 caracteres.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.string' => 'La contraseña debe ser texto.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'password.max' => 'La contraseña no puede superar 200 caracteres.',
            'role.required' => 'El rol es obligatorio.',
            'role.in' => 'Por la API solo se permite crear usuarios con el rol «librarian».',
        ];
    }
}
