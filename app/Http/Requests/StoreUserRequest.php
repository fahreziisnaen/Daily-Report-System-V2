<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Admin authorization is checked in the controller
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 
                'string', 
                'email', 
                'max:255',
                'unique:users,email'
            ],
            'homebase' => ['required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:255'],
            'department_id' => ['required', 'exists:departments,id'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'exists:roles,name'],
        ];
    }

    /**
     * Get custom validation error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'Email sudah digunakan oleh user lain.',
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'name.required' => 'Nama harus diisi.',
            'homebase.required' => 'Homebase harus diisi.',
            'position.required' => 'Jabatan harus diisi.',
            'department_id.required' => 'Department harus diisi.',
            'department_id.exists' => 'Department tidak valid.',
            'password.required' => 'Password harus diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'role.required' => 'Role harus diisi.',
            'role.exists' => 'Role tidak valid.',
        ];
    }
} 