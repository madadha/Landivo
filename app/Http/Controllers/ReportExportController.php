<?php

namespace App\Http\Controllers;

use App\Models\LandingPage;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    public function orders(Request $request): StreamedResponse
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'], 'date_to' => ['nullable', 'date'],
            'status_id' => ['nullable', 'integer'], 'landing_page_id' => ['nullable', 'integer'],
            'product_id' => ['nullable', 'integer'], 'source' => ['nullable', 'string', 'max:255'],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $query = Order::query()
            ->where('orders.account_id', $request->user()->account_id)
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->where('orders.created_at', '>=', Carbon::parse($date)->startOfDay()))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query->where('orders.created_at', '<=', Carbon::parse($date)->endOfDay()))
            ->when($filters['status_id'] ?? null, fn (Builder $query, int $id) => $query->where('order_status_id', $id))
            ->when($filters['landing_page_id'] ?? null, fn (Builder $query, int $id) => $query->where('landing_page_id', $id))
            ->when($filters['product_id'] ?? null, fn (Builder $query, int $id) => $query->whereHas('items', fn (Builder $items) => $items->where('product_id', $id)))
            ->when($filters['source'] ?? null, fn (Builder $query, string $source) => $query->where('source', $source))
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $term = '%'.trim($search).'%';
                $query->where(function (Builder $nested) use ($term): void {
                    $nested->where('order_number', 'like', $term)
                        ->orWhereHas('customer', fn (Builder $customer) => $customer
                            ->where('name', 'like', $term)->orWhere('phone', 'like', $term)->orWhere('email', 'like', $term));
                });
            });

        return response()->streamDownload(function () use ($query): void {
            $stream = fopen('php://output', 'wb');
            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, ['رقم الطلب', 'التاريخ', 'العميل', 'الهاتف', 'البريد الإلكتروني', 'الحالة', 'صفحة الهبوط', 'المنتجات والعروض', 'الكمية', 'الإجمالي', 'العملة', 'المصدر', 'UTM Source', 'UTM Medium', 'UTM Campaign', 'عنوان IP']);

            $query->with(['customer', 'status', 'landingPage.translations', 'items'])->orderBy('orders.id')->chunkById(300, function ($orders) use ($stream): void {
                foreach ($orders as $order) {
                    $utm = $order->utm_parameters ?? [];
                    fputcsv($stream, array_map($this->csvValue(...), [
                        $order->order_number, $order->created_at?->format('Y-m-d H:i'), $order->customer?->name,
                        $order->customer?->phone, $order->customer?->email, $order->status?->name_ar,
                        $this->landingPageName($order->landingPage), $order->items->pluck('product_name')->filter()->implode(' | '),
                        $order->items->sum('quantity'), $order->total, $order->currency, $order->source ?: 'direct',
                        $utm['utm_source'] ?? '', $utm['utm_medium'] ?? '', $utm['utm_campaign'] ?? '', $order->ip_address,
                    ]));
                }
            }, 'orders.id', 'id');

            fclose($stream);
        }, 'landivo-orders-'.now()->format('Y-m-d-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function reviews(Request $request): StreamedResponse
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'], 'date_to' => ['nullable', 'date'],
            'rating' => ['nullable', 'integer', 'between:1,5'], 'approval' => ['nullable', 'in:approved,pending'],
            'product_id' => ['nullable', 'integer'], 'landing_page_id' => ['nullable', 'integer'],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $query = Review::query()
            ->where('reviews.account_id', $request->user()->account_id)
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->where('reviews.created_at', '>=', Carbon::parse($date)->startOfDay()))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query->where('reviews.created_at', '<=', Carbon::parse($date)->endOfDay()))
            ->when($filters['rating'] ?? null, fn (Builder $query, int $rating) => $query->where('rating', $rating))
            ->when(($filters['approval'] ?? null) === 'approved', fn (Builder $query) => $query->where('is_approved', true))
            ->when(($filters['approval'] ?? null) === 'pending', fn (Builder $query) => $query->where('is_approved', false))
            ->when($filters['product_id'] ?? null, fn (Builder $query, int $id) => $query->where('product_id', $id))
            ->when($filters['landing_page_id'] ?? null, fn (Builder $query, int $id) => $query->where('landing_page_id', $id))
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $term = '%'.trim($search).'%';
                $query->where(function (Builder $nested) use ($term): void {
                    $nested->where('name', 'like', $term)->orWhere('customer_email', 'like', $term)
                        ->orWhere('customer_phone', 'like', $term)->orWhere('content', 'like', $term);
                });
            });

        return response()->streamDownload(function () use ($query): void {
            $stream = fopen('php://output', 'wb');
            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, ['التاريخ', 'اسم العميل', 'البريد الإلكتروني', 'الهاتف', 'التقييم', 'التعليق', 'المنتج', 'صفحة الهبوط', 'رقم الطلب', 'شراء موثّق', 'الحالة', 'المصدر']);

            $query->with(['product.translations', 'landingPage.translations', 'order'])->orderBy('reviews.id')->chunkById(300, function ($reviews) use ($stream): void {
                foreach ($reviews as $review) {
                    fputcsv($stream, array_map($this->csvValue(...), [
                        $review->created_at?->format('Y-m-d H:i'), $review->name, $review->customer_email,
                        $review->customer_phone, $review->rating, trim(strip_tags((string) $review->content)),
                        $this->productName($review->product), $this->landingPageName($review->landingPage),
                        $review->order?->order_number, $review->is_verified_purchase ? 'نعم' : 'لا',
                        $review->is_approved ? 'معتمد' : 'بانتظار الاعتماد', $review->source,
                    ]));
                }
            }, 'reviews.id', 'id');

            fclose($stream);
        }, 'landivo-reviews-'.now()->format('Y-m-d-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function productName(?Product $product): string
    {
        return $product?->translations?->firstWhere('locale', 'ar')?->name ?? $product?->translations?->first()?->name ?? $product?->sku ?? '';
    }

    private function landingPageName(?LandingPage $page): string
    {
        return $page?->translations?->firstWhere('locale', 'ar')?->title ?? $page?->translations?->first()?->title ?? $page?->slug ?? '';
    }

    private function csvValue(mixed $value): string
    {
        $value = (string) ($value ?? '');

        return preg_match('/^[=+\-@]/u', $value) ? "'{$value}" : $value;
    }
}
