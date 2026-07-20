<?php

namespace App\Filament\Pages;

use App\Filament\Resources\LandingPages\LandingPageResource;
use App\LandingPageStatus;
use App\Models\LandingPage;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Livewire\WithPagination;

class LandingPageGallery extends Page
{
    use WithPagination;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $navigationLabel = 'معرض صفحات الهبوط';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'معرض صفحات الهبوط';

    protected static ?string $slug = 'landing-page-gallery';

    protected string $view = 'filament.pages.landing-page-gallery';

    public string $search = '';

    public string $status = '';

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'status'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->status = '';
        $this->resetPage();
    }

    protected function getViewData(): array
    {
        $accountId = auth()->user()?->account_id;
        $query = LandingPage::query()
            ->where('account_id', $accountId)
            ->with(['translations', 'product.translations', 'product.media'])
            ->withCount([
                'orders',
                'visitorEvents as views_count' => fn (Builder $query) => $query->where('event_type', 'page_view'),
            ])
            ->withSum('orders as revenue_total', 'total')
            ->when($this->status, fn (Builder $query) => $query->where('status', $this->status))
            ->when(trim($this->search), function (Builder $query): void {
                $term = '%'.trim($this->search).'%';
                $query->where(function (Builder $search) use ($term): void {
                    $search->where('slug', 'like', $term)
                        ->orWhereHas('translations', fn (Builder $translation) => $translation->where('title', 'like', $term))
                        ->orWhereHas('product', fn (Builder $product) => $product->where('sku', 'like', $term));
                });
            })
            ->latest('updated_at');

        return [
            'pages' => $query->paginate(12),
            'totalPages' => LandingPage::query()->where('account_id', $accountId)->count(),
            'publishedPages' => LandingPage::query()->where('account_id', $accountId)->where('status', LandingPageStatus::Published->value)->count(),
            'statuses' => LandingPageStatus::cases(),
            'createUrl' => LandingPageResource::getUrl('create'),
        ];
    }

    public function pageTitle(LandingPage $page): string
    {
        return $page->translations->firstWhere('locale', 'ar')?->title
            ?? $page->translations->first()?->title
            ?? $page->slug;
    }

    public function productName(LandingPage $page): string
    {
        return $page->product?->translations?->firstWhere('locale', 'ar')?->name
            ?? $page->product?->translations?->first()?->name
            ?? $page->product?->sku
            ?? 'بدون منتج مرتبط';
    }

    public function previewImage(LandingPage $page): ?string
    {
        $path = data_get($page->settings, 'product_image_ar')
            ?: data_get($page->settings, 'product_image_en')
            ?: $page->product?->media?->firstWhere('locale', 'ar')?->file_path
            ?: $page->product?->media?->first()?->file_path
            ?: data_get($page->product?->metadata, 'image_ar')
            ?: data_get($page->product?->metadata, 'image_en')
            ?: $page->product?->primary_image_path;

        if (blank($path)) {
            return null;
        }

        return str_starts_with($path, 'http://') || str_starts_with($path, 'https://')
            ? $path
            : Storage::disk('public')->url($path);
    }

    public function editUrl(LandingPage $page): string
    {
        return LandingPageResource::getUrl('edit', ['record' => $page]);
    }

    public function publicUrl(LandingPage $page): string
    {
        return url('/l/'.$page->slug);
    }
}
