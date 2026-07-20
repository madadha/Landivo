<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Customer;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Livewire\WithPagination;

class CustomerSearchReport extends Page
{
    use WithPagination;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlass;

    protected static ?string $navigationLabel = 'بحث العملاء';

    protected static string|\UnitEnum|null $navigationGroup = 'التقارير';

    protected static ?int $navigationSort = 5;

    protected static ?string $title = 'بحث العملاء';

    protected static ?string $slug = 'customer-search';

    protected string $view = 'filament.pages.customer-search-report';

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    protected function getViewData(): array
    {
        $accountId = auth()->user()?->account_id;
        $term = trim($this->search);

        $query = Customer::query()
            ->where('account_id', $accountId)
            ->withCount('orders')
            ->withSum('orders as orders_total', 'total')
            ->withMax('orders as last_order_at', 'created_at')
            ->with(['orders' => fn ($orders) => $orders
                ->with(['status:id,name_ar,name_en,color', 'landingPage.translations'])
                ->latest()
                ->limit(5)])
            ->when($term, function (Builder $query) use ($term): void {
                $like = '%'.$term.'%';
                $query->where(function (Builder $search) use ($like): void {
                    $search->where('name', 'like', $like)
                        ->orWhere('phone', 'like', $like)
                        ->orWhere('email', 'like', $like);
                });
            })
            ->orderByDesc('last_order_at')
            ->latest('customers.updated_at');

        return [
            'customers' => $query->paginate(12),
            'customersCount' => Customer::query()->where('account_id', $accountId)->count(),
            'isSearching' => filled($term),
        ];
    }

    public function customerUrl(Customer $customer): string
    {
        return CustomerResource::getUrl('edit', ['record' => $customer]);
    }

    public function orderUrl(int $orderId): string
    {
        return OrderResource::getUrl('edit', ['record' => $orderId]);
    }

    public function safeColor(?string $color): string
    {
        return preg_match('/^#[0-9a-fA-F]{6}$/', (string) $color) ? $color : '#64748b';
    }
}
