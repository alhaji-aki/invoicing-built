<?php

namespace App\Http\Requests\Product;

use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255', Rule::unique(Product::class)->where('user_id', $user->id)],
            'description' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'gte:0'],
            'quantity' => ['nullable', 'integer', 'gte:0'],
        ];
    }
}
