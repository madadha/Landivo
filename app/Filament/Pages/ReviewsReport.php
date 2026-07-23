<?php

namespace App\Filament\Pages;

use App\Models\LandingPage;
use App\Models\Product;
use App\Models\Review;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Livewire\WithPagination;

class ReviewsReport extends Page
{
    use WithPagination;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static ?string $navigationLabel = 'تقرير التقييمات';

    protected static string|\UnitEnum|null $navigationGroup = 'التقارير والتحليلات';

    protected static ?int $navigationSort = 50;

    protected static ?string $title = 'تقرير التقييمات';

    protected static ?string $slug = 'reviews-report';

    protected string $view = 'filament.pages.reviews-report';

    public string $dateFrom = '';

    public string $dateTo = '';

    public string $rating = '';

    public string $approval = '';

    public string $productId = '';

    public string $landingPageId = '';

    public string $search = '';

    public function mount(): void
    {
        $this->dateFrom = now()->subDays(29)->toDateString();
        $this->dateTo = now()->toDateString();
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['dateFrom', 'dateTo', 'rating', 'approval', 'productId', 'landingPageId', 'search'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->dateFrom = now()->subDays(29)->toDateString();
        $this->dateTo = now()->toDateString();
        $this->rating = '';
        $this->approval = '';
        $this->productId = '';
        $this->landingPageId = '';
        $this->search = '';
        $this->resetPage();
    }

    protected function getViewData(): array
    {
        $query = $this->filteredQuery();
        $reviewsCount = (clone $query)->count();
        $averageRating = (float) ((clone $query)->avg('rating') ?? 0);

        $ratingDistribution = collect(range(5, 1))->mapWithKeys(
            fn (int $stars): array => [$stars => (clone $query)->where('rating', $stars)->count()]
        );

        $topProducts = (clone $query)
            ->whereNotNull('product_id')
            ->selectRaw('product_id, COUNT(*) AS reviews_count, AVG(rating) AS average_rating')
            ->with('product.translations')
            ->groupBy('product_id')
            ->orderByDesc('reviews_count')
            ->limit(6)
            ->get();

        $topLandingPages = (clone $query)
            ->whereNotNull('landing_page_id')
            ->selectRaw('landing_page_id, COUNT(*) AS reviews_count, AVG(rating) AS average_rating')
            ->with('landingPage.translations')
            ->groupBy('landing_page_id')
            ->orderByDesc('reviews_count')
            ->limit(6)
            ->get();

        return [
            'reviews' => (clone $query)->with(['product.translations', 'landingPage.translations', 'order'])->latest()->paginate(20),
            'reviewsCount' => $reviewsCount,
            'averageRating' => $averageRating,
            'approvedCount' => (clone $query)->where('is_approved', true)->count(),
            'verifiedCount' => (clone $query)->where('is_verified_purchase', true)->count(),
            'ratingDistribution' => $ratingDistribution,
            'topProducts' => $topProducts,
            'topLandingPages' => $topLandingPages,
            'products' => Product::query()->where('account_id', $this->accountId())->with('translations')->latest()->get(),
            'landingPages' => LandingPage::query()->where('account_id', $this->accountId())->with('translations')->latest()->get(),
        ];
    }

    public function exportUrl(): string
    {
        return route('reports.reviews.export', array_filter([
            'date_from' => $this->dateFrom, 'date_to' => $this->dateTo, 'rating' => $this->rating,
            'approval' => $this->approval, 'product_id' => $this->productId,
            'landing_page_id' => $this->landingPageId, 'search' => $this->search,
        ], fn (mixed $value): bool => filled($value)));
    }

    private function filteredQuery(): Builder
    {
        return Review::query()
            ->where('reviews.account_id', $this->accountId())
            ->when($this->dateFrom, fn (Builder $query) => $query->where('reviews.created_at', '>=', Carbon::parse($this->dateFrom)->startOfDay()))
            ->when($this->dateTo, fn (Builder $query) => $query->where('reviews.created_at', '<=', Carbon::parse($this->dateTo)->endOfDay()))
            ->when($this->rating, fn (Builder $query) => $query->where('rating', $this->rating))
            ->when($this->approval === 'approved', fn (Builder $query) => $query->where('is_approved', true))
            ->when($this->approval === 'pending', fn (Builder $query) => $query->where('is_approved', false))
            ->when($this->productId, fn (Builder $query) => $query->where('product_id', $this->productId))
            ->when($this->landingPageId, fn (Builder $query) => $query->where('landing_page_id', $this->landingPageId))
            ->when($this->search, function (Builder $query): void {
                $term = '%'.trim($this->search).'%';
                $query->where(function (Builder $search) use ($term): void {
                    $search->where('name', 'like', $term)
                        ->orWhere('customer_email', 'like', $term)
                        ->orWhere('customer_phone', 'like', $term)
                        ->orWhere('content', 'like', $term);
                });
            });
    }

    private function accountId(): ?int
    {
        return auth()->user()?->account_id;
    }

    private function productName(?Product $product): string
    {
        return $product?->translations?->firstWhere('locale', 'ar')?->name
            ?? $product?->translations?->first()?->name
            ?? $product?->sku
            ?? '';
    }

    private function landingPageName(?LandingPage $page): string
    {
        return $page?->translations?->firstWhere('locale', 'ar')?->title
            ?? $page?->translations?->first()?->title
            ?? $page?->slug
            ?? '';
    }
}
