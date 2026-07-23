<x-filament-panels::page>
    <div class="ldv-global-search" dir="rtl">
        <section class="ldv-global-search__hero">
            <div>
                <span>بحث مركزي سريع</span>
                <h1>ابحث في كامل بيانات العمل</h1>
                <p>ابدأ بكتابة الاسم، الهاتف، البريد، رقم الطلب، SKU أو اسم المنتج.</p>
            </div>
            <div class="ldv-global-search__input">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                <input type="search" wire:model.live.debounce.250ms="search" autofocus autocomplete="off" placeholder="اكتب أول حرف للبحث...">
                <span wire:loading wire:target="search">جاري البحث...</span>
            </div>
        </section>

        @if($isEmpty)
            <div class="ldv-global-search__empty">
                <strong>كل شيء في مكان واحد</strong>
                <span>ستظهر النتائج مقسمة إلى طلبات وعملاء ومنتجات فور الكتابة.</span>
            </div>
        @else
            @php($total = $orders->count() + $customers->count() + $products->count())
            <div class="ldv-global-search__summary"><strong>{{ $total }}</strong><span>نتيجة سريعة لـ “{{ $search }}”</span></div>

            <div class="ldv-global-search__columns">
                <section>
                    <header><span>الطلبات</span><b>{{ $orders->count() }}</b></header>
                    @forelse($orders as $order)
                        <a href="{{ $this->orderUrl($order) }}">
                            <i class="is-order">#</i>
                            <span><strong>{{ $order->order_number }}</strong><small>{{ $order->customer?->name }} · {{ $order->customer?->phone }}</small></span>
                            <em style="--result-color:{{ $order->status?->color ?: '#64748b' }}">{{ $order->status?->name_ar ?: 'بدون حالة' }}</em>
                        </a>
                    @empty <div class="ldv-global-search__none">لا توجد طلبات مطابقة.</div> @endforelse
                </section>

                <section>
                    <header><span>العملاء</span><b>{{ $customers->count() }}</b></header>
                    @forelse($customers as $customer)
                        <a href="{{ $this->customerUrl($customer) }}">
                            <i class="is-customer">{{ mb_strtoupper(mb_substr($customer->name, 0, 1)) }}</i>
                            <span><strong>{{ $customer->name }}</strong><small dir="ltr">{{ $customer->phone }} · {{ $customer->email }}</small></span>
                            <em>{{ $customer->orders_count }} طلب</em>
                        </a>
                    @empty <div class="ldv-global-search__none">لا يوجد عملاء مطابقون.</div> @endforelse
                </section>

                <section>
                    <header><span>المنتجات</span><b>{{ $products->count() }}</b></header>
                    @forelse($products as $product)
                        @php($name = $product->translations->firstWhere('locale', 'ar')?->name ?? $product->translations->first()?->name ?? $product->sku)
                        <a href="{{ $this->productUrl($product) }}">
                            <i class="is-product">P</i>
                            <span><strong>{{ $name }}</strong><small>{{ $product->sku ?: 'بدون SKU' }} · المخزون {{ $product->quantity }}</small></span>
                            <em>{{ number_format((float) $product->price, 2) }} {{ $product->currency }}</em>
                        </a>
                    @empty <div class="ldv-global-search__none">لا توجد منتجات مطابقة.</div> @endforelse
                </section>
            </div>
        @endif
    </div>
</x-filament-panels::page>
