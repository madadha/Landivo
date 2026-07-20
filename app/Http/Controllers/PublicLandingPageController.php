<?php

namespace App\Http\Controllers;

use App\Enums\FieldType;
use App\Forms\Fields\FieldTypeRegistry;
use App\LandingPageStatus;
use App\Models\Customer;
use App\Models\LandingPage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VisitorEvent;
use App\Notifications\NewOrderNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PublicLandingPageController extends Controller
{
    public function show(string $slug): View
    {
        $landingPage = LandingPage::with(['translations', 'sections', 'product.translations', 'product.media', 'productVariant.translations', 'reviews' => fn ($query) => $query->where('is_approved', true)->latest()->limit(50)])
            ->where('slug', $slug)
            ->where('status', LandingPageStatus::Published->value)
            ->firstOrFail();

        VisitorEvent::create([
            'account_id' => $landingPage->account_id,
            'landing_page_id' => $landingPage->id,
            'product_id' => $landingPage->product_id,
            'event_type' => 'page_view',
            'path' => request()->path(),
            'session_hash' => request()->hasSession() ? hash('sha256', (string) request()->session()->getId()) : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'referer' => request()->headers->get('referer'),
            'utm_parameters' => collect(request()->query())->only(['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'])->filter()->all() ?: null,
        ]);

        $showcaseIds = collect(data_get($landingPage->settings, 'product_showcase.product_ids', []))
            ->map(fn ($id): int => (int) $id)
            ->filter()
            ->values();
        $showcaseProducts = Product::with(['translations', 'media'])
            ->where('account_id', $landingPage->account_id)
            ->where('status', 'active')
            ->whereIn('id', $showcaseIds)
            ->get()
            ->sortBy(fn (Product $product): int => $showcaseIds->search($product->id))
            ->values();

        foreach ($showcaseProducts as $showcaseProduct) {
            VisitorEvent::create([
                'account_id' => $landingPage->account_id,
                'landing_page_id' => $landingPage->id,
                'product_id' => $showcaseProduct->id,
                'event_type' => 'product_view',
                'path' => request()->path(),
                'session_hash' => request()->hasSession() ? hash('sha256', (string) request()->session()->getId()) : null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'referer' => request()->headers->get('referer'),
            ]);
        }

        $purchaseNotifications = collect();
        if ((bool) data_get($landingPage->settings, 'purchase_notifications_enabled', false)) {
            $notificationLimit = max(1, min(30, (int) data_get($landingPage->settings, 'purchase_notification_limit', 10)));
            $itemsQuery = OrderItem::query()
                ->with(['product.translations', 'product.media'])
                ->whereHas('order', function ($query) use ($landingPage): void {
                    $query->where('account_id', $landingPage->account_id);

                    if (data_get($landingPage->settings, 'purchase_notification_scope', 'landing_page') === 'landing_page') {
                        $query->where('landing_page_id', $landingPage->id);
                    }

                    if (data_get($landingPage->settings, 'purchase_notification_status', 'all') === 'completed') {
                        $query->whereHas('status', fn ($statusQuery) => $statusQuery->where('is_final', true));
                    }
                });

            $purchaseNotifications = $itemsQuery
                ->latest()
                ->limit(max(30, $notificationLimit * 10))
                ->get()
                ->groupBy(fn (OrderItem $item): string => (string) ($item->product_id ?: $item->product_name))
                ->take($notificationLimit)
                ->map(function ($items): array {
                    /** @var OrderItem $item */
                    $item = $items->first();
                    $product = $item->product;
                    $translation = $product?->translations->firstWhere('locale', app()->getLocale()) ?? $product?->translations->first();
                    $localizedImage = $product?->localizedMedia()?->file_path
                        ?: data_get($product?->metadata, app()->getLocale() === 'ar' ? 'image_ar' : 'image_en')
                        ?: $product?->primary_image_path;

                    return [
                        'product' => (string) ($translation?->name ?: $item->product_name ?: (app()->getLocale() === 'ar' ? 'منتج' : 'a product')),
                        'image' => $localizedImage,
                        'count' => $items->count(),
                    ];
                })
                ->values();
        }

        $currentViewers = $this->currentViewerCount($landingPage);

        return view('public.landing-pages.show', compact('landingPage', 'showcaseProducts', 'purchaseNotifications', 'currentViewers'));
    }

    public function viewers(string $slug): JsonResponse
    {
        $landingPage = LandingPage::query()
            ->where('slug', $slug)
            ->where('status', LandingPageStatus::Published->value)
            ->firstOrFail();

        return response()->json(['count' => $this->currentViewerCount($landingPage)]);
    }

    private function currentViewerCount(LandingPage $landingPage): int
    {
        if (! (bool) data_get($landingPage->settings, 'viewer_counter_enabled', false)) {
            return 0;
        }

        $minutes = max(1, min(60, (int) data_get($landingPage->settings, 'viewer_counter_window', 5)));

        return VisitorEvent::query()
            ->where('landing_page_id', $landingPage->id)
            ->where('event_type', 'page_view')
            ->whereNotNull('session_hash')
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->distinct()
            ->count('session_hash');
    }

    public function submit(Request $request, string $slug): View
    {
        $landingPage = LandingPage::with(['product.translations', 'sections'])
            ->where('slug', $slug)
            ->where('status', LandingPageStatus::Published->value)
            ->firstOrFail();

        $formSection = $landingPage->sections->firstWhere('type.value', 'order_form');
        $formFields = collect(data_get($landingPage->settings, 'order_form_fields', []));
        if ($formFields->isEmpty()) {
            $formFields = collect(data_get($formSection?->settings, 'fields', []));
        }
        $formFields = $formFields
            ->filter(fn ($field) => filled($field['internal_name'] ?? $field['key'] ?? null) && ! (app()->getLocale() === 'ar' ? str_ends_with((string) ($field['internal_name'] ?? $field['key']), '_en') : str_ends_with((string) ($field['internal_name'] ?? $field['key']), '_ar')))
            ->values();
        $fieldKeys = $formFields->map(fn ($field) => $field['internal_name'] ?? $field['key'])->all();
        $hasDynamicName = (bool) array_intersect($fieldKeys, ['name', 'full_name_ar', 'full_name_en']);
        $hasDynamicPhone = in_array('phone', $fieldKeys, true);
        $hasDynamicCity = (bool) array_intersect($fieldKeys, ['city', 'emirate']);
        $hasDynamicQuantity = in_array('quantity', $fieldKeys, true);

        $rules = [];
        if (! $hasDynamicName) {
            $rules['name'] = ['required', 'string', 'max:150'];
        }
        if (! $hasDynamicPhone) {
            $rules['phone'] = ['required', 'string', 'max:40'];
        }
        if (! $hasDynamicQuantity) {
            $rules['quantity'] = ['nullable', 'integer', 'min:1', 'max:100'];
        }
        if (! $hasDynamicCity) {
            $rules['city'] = ['nullable', 'string', 'max:100'];
        }

        foreach ($formFields as $field) {
            if (array_key_exists('is_active', $field) && ! $field['is_active']) {
                continue;
            }
            $key = (string) ($field['internal_name'] ?? $field['key']);
            if (! preg_match('/^[A-Za-z][A-Za-z0-9_-]*$/', $key)) {
                continue;
            }

            $path = 'custom.'.$key;
            $options = collect(preg_split('/\r\n|\r|\n/', (string) (app()->getLocale() === 'en' && filled($field['options_en'] ?? null) ? $field['options_en'] : ($field['options'] ?? ''))))
                ->map(fn ($option) => trim($option))
                ->filter()
                ->values()
                ->all();
            $rawType = ($field['type'] ?? 'text') === 'tel' ? 'phone' : ($field['type'] ?? 'text');
            $type = FieldType::tryFrom($rawType) ?? FieldType::Text;
            $required = ! empty($field['required']) && $this->conditionsMatch($field, (array) $request->input('custom', []));
            $rules[$path] = FieldTypeRegistry::make($type)->rules([...$field, 'required' => $required]);

            if ($type === FieldType::Checkbox) {
                if ($options) {
                    $rules[$path.'.*'] = ['string', 'in:'.implode(',', $options)];
                }
            } elseif ($type->isChoice() || in_array($type, [FieldType::ProductVariant, FieldType::Country], true)) {
                if ($options) {
                    $rules[$path][] = 'in:'.implode(',', $options);
                }
            }
            if (! empty($field['validation_rules'])) {
                $rules[$path] = [...$rules[$path], ...array_filter(array_map('trim', explode('|', $field['validation_rules'])))];
            }
        }

        $validated = $request->validate($rules);
        $trackingKeys = collect(data_get($landingPage->settings, 'tracking_parameters', []))
            ->pluck('key')
            ->filter(fn ($key): bool => is_string($key) && preg_match('/^[A-Za-z][A-Za-z0-9_-]*$/', $key) === 1)
            ->values();
        $trackingValues = collect($request->query())->only($trackingKeys->all())->all();
        $customValues = $validated['custom'] ?? [];
        $customValues['_locale'] = app()->getLocale();
        $customerName = $validated['name'] ?? $customValues['full_name_ar'] ?? $customValues['full_name_en'] ?? 'Customer';
        $customerPhone = $validated['phone'] ?? $customValues['phone'] ?? null;
        $customerCity = $validated['city'] ?? $customValues['emirate'] ?? $customValues['city'] ?? null;
        $customerEmail = $customValues['email'] ?? null;
        $customerCountry = $customValues['country'] ?? null;
        $quantity = (int) ($validated['quantity'] ?? $customValues['quantity'] ?? 1);
        $notes = $formFields->map(function ($field) use ($customValues): ?string {
            $key = (string) ($field['internal_name'] ?? $field['key'] ?? '');
            if (! array_key_exists($key, $customValues) || empty($field['include_in_invoice'])) {
                return null;
            }

            $value = $customValues[$key];
            if ($value instanceof UploadedFile) {
                $value = Storage::disk('public')->url($value->store('orders', 'public'));
            } elseif (is_array($value)) {
                $value = collect($value)->map(fn ($item) => $item instanceof UploadedFile ? Storage::disk('public')->url($item->store('orders', 'public')) : $item)->implode(', ');
            }

            $translation = collect($field['translations'] ?? [])->firstWhere('locale', app()->getLocale());
            $label = $translation['label'] ?? ($field['label'] ?? $key);

            return $label.': '.(is_array($value) ? implode(', ', $value) : $value);
        })->filter()->implode("\n");

        abort_unless($landingPage->product, 422, __('landivo.public.product_unavailable'));

        abort_unless($customerPhone, 422, __('landivo.public.phone_required'));

        $variantField = $formFields->first(fn (array $field): bool => ($field['type'] ?? null) === FieldType::ProductVariant->value);
        $variantKey = (string) ($variantField['internal_name'] ?? $variantField['key'] ?? '');
        $variantSelection = $variantKey !== '' ? data_get($customValues, $variantKey) : null;
        $productVariant = $landingPage->productVariant;

        if (filled($variantSelection)) {
            $selectedVariant = ProductVariant::query()
                ->where('product_id', $landingPage->product_id)
                ->where('is_active', true)
                ->where(function ($query) use ($variantSelection): void {
                    if (is_numeric($variantSelection)) {
                        $query->whereKey((int) $variantSelection)->orWhere('sku', (string) $variantSelection);
                    } else {
                        $query->where('sku', (string) $variantSelection);
                    }
                })
                ->first();

            $productVariant = $selectedVariant ?: $productVariant;
        }

        if ($productVariant && (int) $productVariant->product_id !== (int) $landingPage->product_id) {
            $productVariant = null;
        }

        $unitPrice = (float) ($productVariant?->price ?? $landingPage->product->price);

        $order = DB::transaction(function () use ($landingPage, $productVariant, $unitPrice, $notes, $customValues, $customerName, $customerPhone, $customerCity, $customerEmail, $customerCountry, $quantity, $trackingValues, $request): Order {
            $customer = Customer::updateOrCreate(
                ['account_id' => $landingPage->account_id, 'phone' => $customerPhone],
                ['name' => $customerName, 'email' => $customerEmail, 'city' => $customerCity, 'country' => $customerCountry],
            );
            $status = OrderStatus::where('account_id', $landingPage->account_id)->where('slug', 'new')->firstOrFail();
            $total = $unitPrice * max(1, $quantity);
            $order = Order::create([
                'account_id' => $landingPage->account_id,
                'landing_page_id' => $landingPage->id,
                'customer_id' => $customer->id,
                'order_status_id' => $status->id,
                'order_number' => 'LDV-'.now()->format('ymdHis').'-'.random_int(100, 999),
                'subtotal' => $total, 'total' => $total, 'currency' => $landingPage->product->currency,
                'source' => $trackingValues['utm_source'] ?? 'landing_page:'.$landingPage->slug,
                'utm_parameters' => $trackingValues ?: null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'notes' => $notes ?: null,
                'form_data' => $customValues ?: null,
            ]);
            $order->activities()->create([
                'type' => 'system',
                'body' => 'Order submitted from landing page.',
                'metadata' => ['tracking' => $trackingValues, 'ip_address' => $request->ip()],
            ]);
            OrderItem::create([
                'order_id' => $order->id, 'product_id' => $landingPage->product->id,
                'product_variant_id' => $productVariant?->id,
                'product_name' => trim(($landingPage->product->translations->firstWhere('locale', app()->getLocale())?->name ?? $landingPage->product->sku ?? 'Product').($productVariant ? ' — '.$productVariant->label() : '')),
                'quantity' => max(1, $quantity), 'unit_price' => $unitPrice, 'total' => $total,
            ]);

            return $order;
        });

        $notificationEmails = collect(preg_split('/[,;\s]+/', (string) data_get($landingPage->settings, 'notification_emails', '')))
            ->map(fn ($email): string => trim($email))
            ->filter(fn ($email): bool => filter_var($email, FILTER_VALIDATE_EMAIL) !== false)
            ->unique()
            ->values();

        if ($notificationEmails->isNotEmpty()) {
            try {
                Notification::route('mail', $notificationEmails->all())->notify(new NewOrderNotification($order->load('customer')));
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return view('public.landing-pages.thank-you', compact('landingPage', 'order'));
    }

    private function conditionsMatch(array $field, array $values): bool
    {
        foreach ($field['conditions'] ?? [] as $condition) {
            $actual = data_get($values, $condition['field'] ?? '');
            $expected = $condition['value'] ?? null;
            $matches = match ($condition['operator'] ?? 'equals') {
                'not_equals' => (string) $actual !== (string) $expected,
                'contains' => is_array($actual) ? in_array($expected, $actual, true) : str_contains((string) $actual, (string) $expected),
                default => (string) $actual === (string) $expected,
            };
            if (! $matches) {
                return false;
            }
        }

        return true;
    }
}
