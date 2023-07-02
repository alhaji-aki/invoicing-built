<?php

namespace App\Actions\Invoice;

use App\Actions\Customer\FirstOrCreateCustomerAction;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateInvoiceAction
{
    public function __construct(
        private readonly FirstOrCreateCustomerAction $firstOrCreateCustomerAction
    ) {
    }

    public function execute(User $user, array $data): Invoice
    {
        $invoiceItems = $this->formatInvoiceItems($user, $data['items']);

        $this->validateInvoiceItems($invoiceItems);

        /** @var \App\Models\Invoice */
        return DB::transaction(function () use ($user, $data, $invoiceItems) {
            $amount = $invoiceItems->sum('amount');

            // first or create customer
            $customer = $this->firstOrCreateCustomerAction->execute($user, $data['customer']);

            // create invoice
            /** @var \App\Models\Invoice */
            $invoice = $user->invoices()->create([
                'customer_id' => $customer->id,
                'amount' => $amount,
                'issued_at' => $data['issued_at'] ?? now(),
                'due_at' => $data['due_at'],
            ]);

            // create invoice items
            $invoiceItemCollection = $invoice->invoiceItems()
                ->createMany($invoiceItems)
                // reduce product quantity for products selected
                ->map(function (InvoiceItem $invoiceItem) use ($invoiceItems) {
                    /** @var \App\Models\Product */
                    $product = $invoiceItems->where('product.id', $invoiceItem->product_id)->firstOrFail()['product'];

                    $product->decrement('quantity', $invoiceItem->quantity);

                    // set relation of invoice item
                    $invoiceItem->setRelation('product', $product);

                    return $invoiceItem;
                });

            $invoice->setRelations([
                'customer' => $customer,
                'invoiceItems' => $invoiceItemCollection,
            ]);

            return $invoice;
        });
    }

    /**
     * @return Collection<int, array{unit_price: float, product_id: int, amount: float, product: Product, description: string, quantity: int}>
     */
    private function formatInvoiceItems(User $user, array $items): Collection
    {
        $submittedProducts = data_get($items, '*.product_id');

        /** @var EloquentCollection <int, Product> */
        $products = $user->products()->whereIn('uuid', $submittedProducts)->get();

        return collect($items)
            ->map(function (array $item) use ($products) {
                $product = $products->where('uuid', $item['product_id'])->first() ?? new Product();

                return [
                    ...$item,
                    'unit_price' => $unitPrice = $product->formatted_price,
                    'product_id' => $product->id,
                    'amount' => $unitPrice * $item['quantity'],
                    'product' => $product,
                ];
            });
    }

    /**
     * @param  Collection<int, array{unit_price: float, product_id: int, amount: float, product: Product, description: string, quantity: int}>  $invoiceItems
     */
    private function validateInvoiceItems(Collection $invoiceItems): void
    {
        $errors = $invoiceItems->reduce(function (Collection $errors, array $item, int $index) {
            if (! $item['product']->exists) {
                $errors->put("items.$index.product_id", 'The selected invoice item product is invalid.');
            }

            if ($item['product']->quantity < $item['quantity']) {
                $errors->put("items.$index.quantity", 'The invoice item quantity requested cannot be processed.');
            }

            return $errors;
        }, collect([]));

        $errors->whenNotEmpty(function (Collection $errors) {
            throw ValidationException::withMessages($errors->toArray());
        });
    }
}
