<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\LandingPages\LandingPageResource;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Products\ProductResource;
use App\Models\Customer;
use App\Models\LandingPage;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\VisitorEvent;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'لوحة التحكم';

    protected string $view = 'filament.pages.dashboard';

    public array $stats = [];

    public array $recentOrders = [];

    public array $dailyPerformance = [];

    public array $topLandingPages = [];

    public array $orderStatuses = [];

    public array $quickActions = [];

    public function mount(): void
    {
        $accountId = auth()->user()?->account_id;
        $startOfMonth = now()->startOfMonth();
        $visitors = VisitorEvent::query()
            ->where('account_id', $accountId)
            ->where('event_type', 'page_view')
            ->where('created_at', '>=', $startOfMonth)
            ->whereNotNull('session_hash')
            ->distinct()
            ->count('session_hash');

        $ordersThisMonth = Order::query()->where('account_id', $accountId)->where('created_at', '>=', $startOfMonth)->count();
        $revenueThisMonth = (float) Order::query()->where('account_id', $accountId)->where('created_at', '>=', $startOfMonth)->sum('total');
        $currency = Order::query()->where('account_id', $accountId)->latest()->value('currency') ?: 'AED';

        $this->stats = [
            'orders' => Order::query()->where('account_id', $accountId)->count(),
            'orders_today' => Order::query()->where('account_id', $accountId)->whereDate('created_at', today())->count(),
            'new_orders' => Order::query()->where('account_id', $accountId)->whereHas('status', fn ($query) => $query->where('slug', 'new'))->count(),
            'revenue' => $revenueThisMonth,
            'currency' => $currency,
            'customers' => Customer::query()->where('account_id', $accountId)->count(),
            'visitors' => $visitors,
            'conversion' => $visitors > 0 ? round(($ordersThisMonth / $visitors) * 100, 1) : 0,
            'landing_pages' => LandingPage::query()->where('account_id', $accountId)->count(),
            'active_pages' => LandingPage::query()->where('account_id', $accountId)->where('status', 'published')->count(),
            'products' => Product::query()->where('account_id', $accountId)->count(),
        ];

        $this->recentOrders = Order::query()
            ->where('account_id', $accountId)
            ->with(['customer:id,name,phone', 'status:id,name_ar,name_en,color', 'landingPage.translations'])
            ->latest()
            ->limit(7)
            ->get()
            ->map(fn (Order $order): array => [
                'id' => $order->id,
                'number' => $order->order_number,
                'customer' => $order->customer?->name ?: 'عميل بدون اسم',
                'phone' => $order->customer?->phone ?: '—',
                'page' => $order->landingPage?->translations->firstWhere('locale', 'ar')?->title ?: $order->landingPage?->slug ?: 'طلب مباشر',
                'total' => number_format((float) $order->total, 2),
                'currency' => $order->currency,
                'status' => $order->status?->name_ar ?: 'بدون حالة',
                'status_color' => $this->safeColor($order->status?->color),
                'created_at' => $order->created_at?->diffForHumans() ?: '—',
                'url' => OrderResource::getUrl('edit', ['record' => $order]),
            ])->all();

        $this->dailyPerformance = collect(range(6, 0))->map(function (int $daysAgo) use ($accountId): array {
            $day = now()->subDays($daysAgo);

            return [
                'label' => $day->translatedFormat('D'),
                'date' => $day->format('d/m'),
                'orders' => Order::query()->where('account_id', $accountId)->whereDate('created_at', $day)->count(),
                'visits' => VisitorEvent::query()->where('account_id', $accountId)->where('event_type', 'page_view')->whereDate('created_at', $day)->count(),
            ];
        })->all();

        $pageVisits = VisitorEvent::query()
            ->where('account_id', $accountId)
            ->where('event_type', 'page_view')
            ->where('created_at', '>=', now()->subDays(30))
            ->whereNotNull('landing_page_id')
            ->selectRaw('landing_page_id, COUNT(*) as visits')
            ->groupBy('landing_page_id')
            ->orderByDesc('visits')
            ->limit(5)
            ->get();

        $pages = LandingPage::query()->with('translations')->whereIn('id', $pageVisits->pluck('landing_page_id'))->get()->keyBy('id');
        $maxVisits = max(1, (int) $pageVisits->max('visits'));
        $this->topLandingPages = $pageVisits->map(function ($row) use ($pages, $maxVisits): array {
            $page = $pages->get($row->landing_page_id);

            return [
                'title' => $page?->translations->firstWhere('locale', 'ar')?->title ?: $page?->slug ?: 'صفحة محذوفة',
                'slug' => $page?->slug ?: '',
                'visits' => (int) $row->visits,
                'percent' => round(((int) $row->visits / $maxVisits) * 100),
                'edit_url' => $page ? LandingPageResource::getUrl('edit', ['record' => $page]) : '#',
                'public_url' => $page ? url('/l/'.$page->slug) : '#',
            ];
        })->all();

        $statusRows = OrderStatus::query()
            ->where('account_id', $accountId)
            ->withCount(['orders' => fn ($query) => $query->where('account_id', $accountId)])
            ->orderBy('sort_order')
            ->get();
        $statusTotal = max(1, (int) $statusRows->sum('orders_count'));
        $this->orderStatuses = $statusRows->map(fn (OrderStatus $status): array => [
            'label' => $status->name_ar,
            'count' => $status->orders_count,
            'percent' => round(($status->orders_count / $statusTotal) * 100),
            'color' => $this->safeColor($status->color),
        ])->all();

        $this->quickActions = [
            ['label' => 'إنشاء صفحة هبوط', 'description' => 'ابدأ حملة تسويقية جديدة', 'url' => LandingPageResource::getUrl('create'), 'icon' => 'page', 'tone' => 'blue'],
            ['label' => 'إضافة منتج', 'description' => 'أضف عرضًا أو منتجًا جديدًا', 'url' => ProductResource::getUrl('create'), 'icon' => 'product', 'tone' => 'violet'],
            ['label' => 'متابعة الطلبات', 'description' => 'راجع الطلبات الجديدة', 'url' => OrderResource::getUrl(), 'icon' => 'order', 'tone' => 'emerald'],
            ['label' => 'العملاء المحتملون', 'description' => 'تواصل مع العملاء والـ Leads', 'url' => CustomerResource::getUrl(), 'icon' => 'users', 'tone' => 'amber'],
        ];
    }

    private function safeColor(?string $color): string
    {
        return preg_match('/^#[0-9a-fA-F]{6}$/', (string) $color) ? $color : '#64748b';
    }
}
