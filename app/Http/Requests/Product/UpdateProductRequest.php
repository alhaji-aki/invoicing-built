<?php

namespace App\Http\Requests\Product;

use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
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

        /** @var Product */
        $product = $this->route('product');

        return [
            'title' => ['nullable', 'string', 'max:255', Rule::unique(Product::class)->ignoreModel($product)->where('user_id', $user->id)],
            'description' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'gte:0'],
            'quantity' => ['nullable', 'integer', 'gte:0'],
        ];
    }
}
