<?php

namespace App\Http\Requests\Customer;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        /** @var \App\Models\User */
        $user = $this->user();

        /** @var \App\Models\Customer */
        $customer = $this->route('customer');

        return [
            'name' => [
                'nullable', 'string', 'max:255',
                Rule::unique(Customer::class)->ignoreModel($customer)->where('user_id', $user->id),
            ],
            'email' => [
                'nullable', 'string', 'email', 'max:255',
                Rule::unique(Customer::class)->ignoreModel($customer)->where('user_id', $user->id),
            ],
        ];
    }
}
