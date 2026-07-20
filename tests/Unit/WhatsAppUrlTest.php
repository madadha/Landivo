<?php

namespace Tests\Unit;

use App\Support\WhatsAppUrl;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class WhatsAppUrlTest extends TestCase
{
    #[DataProvider('phoneNumbers')]
    public function test_it_normalizes_phone_numbers_for_whatsapp(string $input, string $expected): void
    {
        $this->assertSame($expected, WhatsAppUrl::normalize($input, '971'));
    }

    public static function phoneNumbers(): array
    {
        return [
            'international with plus' => ['+971 52 371 7772', '971523717772'],
            'international with 00' => ['00971523717772', '971523717772'],
            'local with trunk prefix' => ['0523717772', '971523717772'],
            'local without trunk prefix' => ['523717772', '971523717772'],
            'arabic numerals' => ['٠٥٢٣٧١٧٧٧٢', '971523717772'],
        ];
    }

    public function test_it_builds_an_encoded_whatsapp_message_url(): void
    {
        $url = WhatsAppUrl::make('0523717772', 'مرحبًا رعد', '971');

        $this->assertStringStartsWith('https://wa.me/971523717772?text=', $url);
        $query = (string) parse_url($url, PHP_URL_QUERY);
        $this->assertSame('مرحبًا رعد', rawurldecode(substr($query, 5)));
    }

    public function test_it_rejects_invalid_phone_numbers(): void
    {
        $this->assertNull(WhatsAppUrl::normalize('123', '971'));
        $this->assertSame('#', WhatsAppUrl::make('invalid'));
    }
}
