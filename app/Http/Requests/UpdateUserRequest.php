<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization is handled in the controller using policies
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $user = $this->route('user');
        
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'homebase' => ['required', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'max:1024'],
            'signature' => ['nullable', 'image', 'max:1024'],
        ];

        $authUser = auth()->user();
        
        // Add additional validation for admins
        if ($authUser->hasRole(['Super Admin', 'Admin Divisi', 'Vice President'])) {
            $rules['position'] = ['required', 'string', 'max:255'];
            
            // Super Admin can change department
            if ($authUser->hasRole('Super Admin')) {
                $rules['department_id'] = ['required', 'exists:departments,id'];
            }
            
            // Add email validation if needed
            if ($this->routeIs('admin.users.update')) {
                $rules['email'] = [
                    'required', 
                    'string', 
                    'email', 
                    'max:255', 
                    Rule::unique('users')->ignore($user->id)
                ];
            }
        }

        return $rules;
    }

    /**
     * Get custom validation error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama harus diisi.',
            'name.max' => 'Nama tidak boleh lebih dari :max karakter.',
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan oleh user lain.',
            'email.max' => 'Email tidak boleh lebih dari :max karakter.',
            'homebase.required' => 'Homebase harus diisi.',
            'position.required' => 'Jabatan harus diisi.',
            'department_id.required' => 'Department harus diisi.',
            'department_id.exists' => 'Department tidak valid.',
            'avatar.image' => 'File harus berupa gambar.',
            'avatar.max' => 'Ukuran avatar tidak boleh lebih dari 1MB.',
            'signature.image' => 'File harus berupa gambar.',
            'signature.max' => 'Ukuran tanda tangan tidak boleh lebih dari 1MB.',
        ];
    }
} 