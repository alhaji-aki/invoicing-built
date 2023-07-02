<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Stringable;

class ProductController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'verified']);

        $this->authorizeResource(Product::class, 'product');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Responsable
    {
        /** @var \App\Models\User */
        $user = $request->user();

        $products = $user->products()
            ->getQuery()
            ->when($request->filled('query'), function (Builder $query) use ($request) {
                $searchTerm = $request->string('query')
                    ->trim()
                    ->tap(function (Stringable $value) {
                        abort_if($value->length() < 3, 400, 'The search term should be 3 or more characters');
                    })
                    ->pipe('htmlspecialchars')
                    ->append('%')
                    ->prepend('%')
                    ->toString();

                return $query->where('title', 'LIKE', $searchTerm);
            })
            ->when($request->filled('in_stock'), function (Builder $query) use ($request) {
                return match ($request->boolean('in_stock')) {
                    true => $query->where('quantity', '>', 0),
                    default => $query->where('quantity', 0)
                };
            })
            ->paginate()
            ->withQueryString();

        return ProductResource::collection($products)->additional(['message' => 'Get products.']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request): Responsable
    {
        $data = (array) $request->validated();

        /** @var \App\Models\User */
        $user = $request->user();

        $product = $user->products()->create($data)->refresh();

        return (new ProductResource($product))->additional(['message' => 'Product created successfully.']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): Responsable
    {
        return (new ProductResource($product))->additional(['message' => 'Get product.']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product): Responsable
    {
        $data = array_filter((array) $request->validated());

        abort_if(empty($data), 400, 'No data submitted.');

        $product->update($data);

        return (new ProductResource($product->refresh()))->additional(['message' => 'Product updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully.']);
    }
}
