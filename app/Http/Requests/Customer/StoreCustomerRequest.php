<?php

namespace App\Http\Requests\Customer;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        /** @var User */
        $user = $this->user();

        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique(Customer::class)->where('user_id', $user->id),
            ],
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique(Customer::class)->where('user_id', $user->id),
            ],
        ];
    }
}
