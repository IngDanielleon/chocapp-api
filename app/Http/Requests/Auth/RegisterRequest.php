<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'           => 'required|string|min:3|max:100',
            'email'          => 'required|email|unique:users,email',
            'password'       => 'required|string|min:8|confirmed',
            'id_type'        => 'required|in:CC,CE,PPT,PASAPORTE',
            'id_number'      => 'required|string|unique:users,id_number',
            'phone_number'   => 'required|string|max:20',
            'terms_accepted' => 'required|accepted',
            'profile_pic'    => 'nullable|image|mimes:jpeg,png,webp|max:5120',
        ];
    }

    public function attributes(): array
    {
        return [
            'name'           => 'nombre completo',
            'email'          => 'correo electrónico',
            'password'       => 'contraseña',
            'id_type'        => 'tipo de documento',
            'id_number'      => 'número de documento',
            'phone_number'   => 'número de teléfono',
            'terms_accepted' => 'términos y condiciones',
        ];
    }
}
