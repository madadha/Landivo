@forelse($products as $product)
    @include('site.partials.product-card', ['product' => $product])
@empty
    <div class="web-empty">
        {{ app()->getLocale() === 'ar' ? 'لا توجد منتجات متاحة حاليًا.' : 'No products are currently available.' }}
    </div>
@endforelse
