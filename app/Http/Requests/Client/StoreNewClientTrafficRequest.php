<?php

namespace App\Http\Requests\Client;

use App\Enums\UserRoleEnum;
use Illuminate\Foundation\Http\FormRequest;

class StoreNewClientTrafficRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (auth()->user()->role === UserRoleEnum::ADMIN->value) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'inbound' => 'required|int|min:1',
            'username' => 'required|string|min:3|max:255',
            'total' => 'required|int|in:50,100,150,200',
            'user' => 'required|int|min:1',
        ];
    }
}
