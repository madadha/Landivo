<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomNotFoundPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_web_page_uses_the_branded_not_found_screen(): void
    {
        $response = $this->get('/this-page-intentionally-does-not-exist');

        $response
            ->assertNotFound()
            ->assertSee('404')
            ->assertSee('الصفحة التي تبحث عنها');
    }

    public function test_missing_web_page_supports_english_locale(): void
    {
        $response = $this
            ->withSession(['locale' => 'en'])
            ->get('/this-page-intentionally-does-not-exist');

        $response
            ->assertNotFound()
            ->assertSee('Sorry, we could not find that page')
            ->assertSee('Browse products');
    }
}
