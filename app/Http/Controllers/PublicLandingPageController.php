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
use App\Notifications\NewOrderNotification;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class PublicLandingPageController extends Controller
{
    public function show(string $slug): View
    {
        $landingPage = LandingPage::with(['translations', 'sections', 'product.translations', 'reviews' => fn ($query) => $query->where('is_approved', true)->where('is_featured', true)->latest()])
            ->where('slug', $slug)
            ->where('status', LandingPageStatus::Published->value)
            ->firstOrFail();

        $showcaseIds = collect(data_get($landingPage->settings, 'product_showcase.product_ids', []))
            ->map(fn ($id): int => (int) $id)
            ->filter()
            ->values();
        $showcaseProducts = Product::with('translations')
            ->where('account_id', $landingPage->account_id)
            ->where('status', 'active')
            ->whereIn('id', $showcaseIds)
            ->get()
            ->sortBy(fn (Product $product): int => $showcaseIds->search($product->id))
            ->values();

        return view('public.landing-pages.show', compact('landingPage', 'showcaseProducts'));
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

        $order = DB::transaction(function () use ($landingPage, $notes, $customValues, $customerName, $customerPhone, $customerCity, $customerEmail, $customerCountry, $quantity, $trackingValues, $request): Order {
            $customer = Customer::updateOrCreate(
                ['account_id' => $landingPage->account_id, 'phone' => $customerPhone],
                ['name' => $customerName, 'email' => $customerEmail, 'city' => $customerCity, 'country' => $customerCountry],
            );
            $status = OrderStatus::where('account_id', $landingPage->account_id)->where('slug', 'new')->firstOrFail();
            $total = (float) $landingPage->product->price * max(1, $quantity);
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
                'product_name' => $landingPage->product->translations->firstWhere('locale', app()->getLocale())?->name ?? $landingPage->product->sku ?? 'Product',
                'quantity' => max(1, $quantity), 'unit_price' => $landingPage->product->price, 'total' => $total,
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
