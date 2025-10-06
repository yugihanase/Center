<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TechnicianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ถ้ามี policy/middleware คุมสิทธิ์อยู่แล้ว ใช้ true ได้
    }

    public function rules(): array
    {
        // รองรับทั้ง route model binding และ id ธรรมดา
        $id = $this->route('technician')?->id ?? $this->input('id');

        return [
            'employee_code' => [
                'required','alpha_dash','max:50',
                Rule::unique('technicians','employee_code')->ignore($id),
            ],
            'name'          => ['required','string','max:255'],
            'phone'         => ['nullable','string','max:30'],
            'email'         => ['nullable','email','max:255'],
            'role'          => ['required', Rule::in(['technician','lead'])],
            'department'    => ['nullable','string','max:255'],
            'is_active'     => ['sometimes','boolean'],
            'user_id'       => ['nullable','exists:users,id'],
            'notes'         => ['nullable','string','max:2000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'employee_code' => 'รหัสพนักงาน',
            'name'          => 'ชื่อ',
            'role'          => 'บทบาท',
        ];
    }
}
