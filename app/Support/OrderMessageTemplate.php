<?php

namespace App\Support;

use App\Models\Order;
use Illuminate\Support\Facades\URL;

class OrderMessageTemplate
{
    public static function render(Order $order, string $locale = 'ar'): string
    {
        $order->loadMissing(['customer', 'status', 'landingPage.translations']);
        $locale = $locale === 'en' ? 'en' : 'ar';
        $template = (string) data_get($order->landingPage?->settings, "order_whatsapp_message_{$locale}", '');

        if (blank($template)) {
            $template = $locale === 'en'
                ? "Hello {customer_name},\nRegarding your order {order_number}.\nSelected offer: {selected_offer}\nTotal: {total} {currency}"
                : "مرحبًا {customer_name}،\nبخصوص طلبك رقم {order_number}.\nالعرض المختار: {selected_offer}\nالإجمالي: {total} {currency}";
        }

        $tokens = self::tokens($order, $locale);

        return (string) preg_replace_callback('/\{([A-Za-z0-9_.-]+)\}/', function (array $matches) use ($tokens): string {
            return array_key_exists($matches[1], $tokens) ? $tokens[$matches[1]] : $matches[0];
        }, $template);
    }

    /** @return array<string, string> */
    public static function tokens(Order $order, string $locale = 'ar'): array
    {
        $order->loadMissing(['customer', 'status', 'landingPage.translations']);
        $formData = collect((array) $order->form_data)
            ->map(fn (mixed $value): string => self::stringify($value))
            ->all();
        $customer = $order->customer;
        $offer = $formData['offer'] ?? $formData['selected_offer'] ?? '';
        $pageTranslation = $order->landingPage?->translations->firstWhere('locale', $locale)
            ?? $order->landingPage?->translations->first();

        return array_merge($formData, [
            'name' => (string) $customer?->name,
            'phone' => (string) $customer?->phone,
            'email' => (string) $customer?->email,
            'customer_name' => (string) $customer?->name,
            'customer_phone' => (string) $customer?->phone,
            'customer_email' => (string) $customer?->email,
            'customer_city' => (string) $customer?->city,
            'customer_country' => (string) $customer?->country,
            'order_number' => (string) $order->order_number,
            'order_status' => (string) ($locale === 'en' ? $order->status?->name_en : $order->status?->name_ar),
            'total' => number_format((float) $order->total, 2, '.', ''),
            'currency' => (string) $order->currency,
            'source' => (string) $order->source,
            'selected_offer' => $offer,
            'offer' => $offer,
            'follow_up_note' => (string) $order->follow_up_note,
            'follow_up_at' => $order->follow_up_at?->format('Y-m-d H:i') ?? '',
            'landing_page_title' => (string) $pageTranslation?->title,
            'landing_page_url' => $order->landingPage?->slug ? url('/l/'.$order->landingPage->slug) : '',
            'invoice_url' => URL::temporarySignedRoute('orders.invoice', now()->addDays(7), ['order' => $order->id]),
            'review_url' => URL::temporarySignedRoute('reviews.order.form', now()->addDays(90), ['order' => $order->id]),
        ]);
    }

    private static function stringify(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'نعم' : 'لا';
        }

        if (is_array($value)) {
            return collect($value)->map(fn (mixed $item): string => self::stringify($item))->implode('، ');
        }

        return is_scalar($value) ? (string) $value : '';
    }
}
