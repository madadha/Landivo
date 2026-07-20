<x-filament-panels::page>
    <div class="ldv-customer-search" dir="rtl">
        <section class="ldv-customer-hero">
            <div>
                <span>بحث موحّد وآمن</span>
                <h1>اعثر على أي عميل فورًا</h1>
                <p>اكتب أي جزء من الاسم أو رقم الهاتف أو البريد الإلكتروني، وستظهر بيانات العميل وطلباته مباشرة.</p>
            </div>
            <div class="ldv-customer-total"><strong>{{ number_format($customersCount) }}</strong><span>عميل مسجل</span></div>
        </section>

        <section class="ldv-customer-searchbar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
            <input type="search" wire:model.live.debounce.250ms="search" autofocus placeholder="ابدأ بكتابة الاسم أو الهاتف أو البريد...">
            <span wire:loading wire:target="search">جاري البحث...</span>
            @if($search)<button type="button" wire:click="clearSearch">مسح</button>@endif
        </section>

        <div class="ldv-search-context">
            <div><strong>{{ number_format($customers->total()) }}</strong><span>{{ $isSearching ? 'نتيجة مطابقة' : 'أحدث العملاء' }}</span></div>
            @if($isSearching)<p>نتائج البحث عن: <b>“{{ $search }}”</b></p>@else<p>ابدأ بحرف واحد فقط، أو تصفح أحدث العملاء المسجلين.</p>@endif
        </div>

        <section class="ldv-customer-results">
            @forelse($customers as $customer)
                @php($metadata = collect((array) $customer->metadata)->filter(fn ($value) => is_scalar($value) && filled($value))->take(8))
                <article class="ldv-customer-card" wire:key="customer-card-{{ $customer->id }}">
                    <header>
                        <div class="ldv-customer-avatar">{{ mb_strtoupper(mb_substr($customer->name, 0, 1)) }}</div>
                        <div class="ldv-customer-identity"><span>العميل #{{ $customer->id }}</span><h2>{{ $customer->name }}</h2><small>آخر طلب {{ $customer->last_order_at ? \Illuminate\Support\Carbon::parse($customer->last_order_at)->diffForHumans() : 'لا يوجد' }}</small></div>
                        <a href="{{ $this->customerUrl($customer) }}">فتح الملف</a>
                    </header>

                    <div class="ldv-customer-contact">
                        <a href="tel:{{ $customer->phone }}"><span>الهاتف</span><strong dir="ltr">{{ $customer->phone }}</strong></a>
                        <a href="mailto:{{ $customer->email }}" class="{{ blank($customer->email) ? 'is-empty' : '' }}"><span>البريد الإلكتروني</span><strong dir="ltr">{{ $customer->email ?: 'غير مسجل' }}</strong></a>
                        <div><span>الموقع</span><strong>{{ collect([$customer->city, $customer->country])->filter()->join('، ') ?: 'غير محدد' }}</strong></div>
                    </div>

                    <div class="ldv-customer-kpis">
                        <div><span>إجمالي الطلبات</span><strong>{{ number_format($customer->orders_count) }}</strong></div>
                        <div><span>إجمالي المشتريات</span><strong>{{ number_format((float) $customer->orders_total, 2) }} <small>{{ $customer->orders->first()?->currency ?? 'AED' }}</small></strong></div>
                        <div><span>تاريخ التسجيل</span><strong>{{ $customer->created_at?->format('Y/m/d') }}</strong></div>
                    </div>

                    <details class="ldv-customer-details">
                        <summary><span>عرض الطلبات والبيانات الكاملة</span><b>+</b></summary>
                        <div class="ldv-customer-details-body">
                            <section><h3>آخر الطلبات</h3><div class="ldv-customer-orders">
                                @forelse($customer->orders as $order)
                                    <a href="{{ $this->orderUrl($order->id) }}"><div><strong>{{ $order->order_number }}</strong><span>{{ $order->landingPage?->translations?->firstWhere('locale', 'ar')?->title ?? $order->landingPage?->slug ?? 'طلب مباشر' }}</span></div><div><b style="--status:{{ $this->safeColor($order->status?->color) }}">{{ $order->status?->name_ar ?? 'بدون حالة' }}</b><strong>{{ number_format((float) $order->total, 2) }} {{ $order->currency }}</strong></div></a>
                                @empty<div class="ldv-no-customer-data">لا توجد طلبات لهذا العميل.</div>@endforelse
                            </div></section>
                            <section><h3>بيانات إضافية</h3><dl class="ldv-customer-metadata">
                                @forelse($metadata as $key => $value)<div><dt>{{ str($key)->replace('_', ' ')->headline() }}</dt><dd>{{ $value }}</dd></div>@empty<div class="ldv-no-customer-data">لا توجد بيانات إضافية.</div>@endforelse
                            </dl></section>
                        </div>
                    </details>
                </article>
            @empty
                <div class="ldv-customer-empty"><strong>لم نعثر على عميل مطابق</strong><span>تحقق من الاسم أو الهاتف أو البريد، ويمكنك البحث بأي جزء منها.</span></div>
            @endforelse
        </section>

        @if($customers->hasPages())<div class="ldv-gallery-pagination">{{ $customers->links() }}</div>@endif
    </div>
</x-filament-panels::page>
