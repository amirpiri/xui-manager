<?php

namespace App\Http\Requests;

use App\Enums\UserRoleEnum;
use App\Models\UserClientTraffic;
use Illuminate\Foundation\Http\FormRequest;

class ClientTrafficRenewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (
            (
                auth()->user()->role === UserRoleEnum::RESELLER->value and
                UserClientTraffic::where('client_traffic_id', $this->route('clientId'))
                    ->where('user_id', auth()->user()->id)->count() === 1
            ) or auth()->user()->role === UserRoleEnum::ADMIN->value
        ) {
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
            'traffic' => 'required|in:50,100,150,200'
        ];
    }
}
