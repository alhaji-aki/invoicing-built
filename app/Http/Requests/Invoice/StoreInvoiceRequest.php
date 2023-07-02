<?php

namespace App\Http\Requests\Invoice;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvoiceRequest extends FormRequest
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

        return [
            'customer.uuid' => [
                Rule::prohibitedIf(fn () => $this->filled('customer.name')),
                Rule::requiredIf(fn () => $this->isNotFilled('customer.name')),
                'string',
                Rule::exists(Customer::class, 'uuid')->where('user_id', $user->id),
            ],
            'customer.name' => [
                Rule::prohibitedIf(fn () => $this->filled('customer.uuid')),
                Rule::requiredIf(fn () => $this->isNotFilled('customer.uuid')),
                'string', 'max:255',
                Rule::unique(Customer::class, 'name')->where('user_id', $user->id),
            ],
            'customer.email' => [
                Rule::excludeIf(fn () => $this->filled('customer.uuid')),
                'required_with:customer.name', 'string', 'email', 'max:255',
                Rule::unique(Customer::class, 'email')->where('user_id', $user->id),
            ],
            'issued_at' => ['nullable', 'date', 'before_or_equal:today'],
            'due_at' => ['required', 'date', 'after:issued_at'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required', 'string', 'distinct:ignore_case',
            ],
            'items.*.description' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'customer.uuid' => 'customer',
            'customer.name' => 'customer name',
            'customer.email' => 'customer email',
            'items.*.product_id' => 'invoice item product',
            'items.*.description' => 'invoice item description',
            'items.*.quantity' => 'invoice item quantity',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'customer.uuid.prohibited' => 'You have to either select an existing customer or submit details to create a new one.',
            'customer.name.prohibited' => 'You have to either select an existing customer or submit details to create a new one.',
        ];
    }
}
