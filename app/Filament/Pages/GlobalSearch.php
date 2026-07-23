<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Products\ProductResource;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class GlobalSearch extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlass;

    protected static ?string $navigationLabel = 'البحث الموحد';

    protected static string|\UnitEnum|null $navigationGroup = 'التقارير والتحليلات';

    protected static ?int $navigationSort = 5;

    protected static ?string $title = 'البحث الموحد';

    protected static ?string $slug = 'global-search';

    protected string $view = 'filament.pages.global-search';

    public string $search = '';

    protected function getViewData(): array
    {
        $term = trim($this->search);
        $empty = mb_strlen($term) < 1;

        return [
            'orders' => $empty ? collect() : $this->orders($term),
            'customers' => $empty ? collect() : $this->customers($term),
            'products' => $empty ? collect() : $this->products($term),
            'isEmpty' => $empty,
        ];
    }

    private function orders(string $term)
    {
        $prefix = $term.'%';

        return Order::query()
            ->where('account_id', $this->accountId())
            ->where(function (Builder $query) use ($prefix): void {
                $query->where('order_number', 'like', $prefix)
                    ->orWhereHas('customer', fn (Builder $customer) => $customer
                        ->where('name', 'like', $prefix)
                        ->orWhere('phone', 'like', $prefix)
                        ->orWhere('email', 'like', $prefix));
            })
            ->with(['customer:id,name,phone,email', 'status:id,name_ar,color'])
            ->latest()
            ->limit(10)
            ->get();
    }

    private function customers(string $term)
    {
        $prefix = $term.'%';

        return Customer::query()
            ->where('account_id', $this->accountId())
            ->where(fn (Builder $query) => $query
                ->where('name', 'like', $prefix)
                ->orWhere('phone', 'like', $prefix)
                ->orWhere('email', 'like', $prefix))
            ->withCount('orders')
            ->latest()
            ->limit(10)
            ->get();
    }

    private function products(string $term)
    {
        $prefix = $term.'%';

        return Product::query()
            ->where('account_id', $this->accountId())
            ->where(function (Builder $query) use ($prefix): void {
                $query->where('sku', 'like', $prefix)
                    ->orWhereHas('translations', fn (Builder $translation) => $translation
                        ->whereIn('locale', ['ar', 'en'])
                        ->where('name', 'like', $prefix));
            })
            ->with('translations:id,product_id,locale,name')
            ->latest()
            ->limit(10)
            ->get();
    }

    public function orderUrl(Order $order): string
    {
        return OrderResource::getUrl('edit', ['record' => $order]);
    }

    public function customerUrl(Customer $customer): string
    {
        return CustomerResource::getUrl('edit', ['record' => $customer]);
    }

    public function productUrl(Product $product): string
    {
        return ProductResource::getUrl('edit', ['record' => $product]);
    }

    private function accountId(): ?int
    {
        return auth()->user()?->account_id;
    }
}
