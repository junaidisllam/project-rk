<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreAddressRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
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
            'phone' => ['required', 'string', 'max:20'],
            'alt_phone' => ['nullable', 'string', 'max:20'],
            'division_id' => ['required', 'integer'],
            'division_name' => ['required', 'string', 'max:255'],
            'district_id' => ['required', 'integer'],
            'district_name' => ['required', 'string', 'max:255'],
            'upazila_id' => ['required', 'integer'],
            'upazila_name' => ['required', 'string', 'max:255'],
            'union_id' => ['nullable', 'integer'],
            'union_name' => ['nullable', 'string', 'max:255'],
            'address_details' => ['required', 'string', 'max:500'],
            'is_default' => ['boolean'],
        ];
    }
}