<x-filament-panels::page>
    <div class="ldv-transfer" dir="rtl" wire:poll.3s>
        <section class="ldv-transfer__hero">
            <div>
                <span>عمليات خلفية آمنة</span>
                <h1>استيراد وتصدير البيانات الكبيرة</h1>
                <p>شغّل العملية واترك الصفحة؛ ستعالج الطوابير الملف وتعرض التقدم والنتيجة تلقائيًا.</p>
            </div>
            <div class="ldv-transfer__worker">
                <i></i><span>يتطلب تشغيل Queue Worker في الإنتاج</span>
            </div>
        </section>

        <section class="ldv-transfer__builder">
            <div class="ldv-transfer__choice">
                <label class="{{ $operation === 'export' ? 'is-active' : '' }}">
                    <input type="radio" value="export" wire:model.live="operation">
                    <strong>تصدير</strong><span>إنشاء ملف CSV في الخلفية</span>
                </label>
                <label class="{{ $operation === 'import' ? 'is-active' : '' }}">
                    <input type="radio" value="import" wire:model.live="operation">
                    <strong>استيراد</strong><span>إضافة أو تحديث سجلات CSV</span>
                </label>
            </div>

            <div class="ldv-transfer__fields">
                <label>
                    <span>نوع البيانات</span>
                    <select wire:model.live="entity">
                        @if($operation === 'export')<option value="orders">الطلبات</option>@endif
                        <option value="customers">العملاء</option>
                        <option value="products">المنتجات</option>
                    </select>
                </label>

                @if($operation === 'import')
                    <label class="ldv-transfer__upload">
                        <span>ملف CSV — بحد أقصى 50 MB</span>
                        <input type="file" wire:model="importFile" accept=".csv,text/csv">
                        <small wire:loading wire:target="importFile">جاري رفع الملف المؤقت...</small>
                        @error('importFile')<b>{{ $message }}</b>@enderror
                    </label>
                    <div class="ldv-transfer__templates">
                        <span>ابدأ بقالب صحيح:</span>
                        <a href="{{ $this->templateUrl('customers') }}">قالب العملاء</a>
                        <a href="{{ $this->templateUrl('products') }}">قالب المنتجات</a>
                    </div>
                @endif
            </div>

            <button type="button" wire:click="startTransfer" wire:loading.attr="disabled" wire:target="startTransfer,importFile">
                <span wire:loading.remove wire:target="startTransfer">{{ $operation === 'import' ? 'بدء الاستيراد' : 'بدء التصدير' }}</span>
                <span wire:loading wire:target="startTransfer">جاري جدولة العملية...</span>
            </button>
        </section>

        <section class="ldv-transfer__history">
            <header>
                <div><span>سجل العمليات</span><h2>التقدم والملفات الناتجة</h2></div>
                <div class="ldv-transfer__history-actions">
                    <small>آخر 30 عملية</small>
                    @if($transfers->contains(fn ($transfer) => ! $transfer->isRunning()))
                        <button
                            type="button"
                            wire:click="clearHistory"
                            wire:confirm="سيتم حذف كل العمليات المكتملة والفاشلة وملفات CSV التابعة لها. هل تريد المتابعة؟"
                        >تنظيف السجل</button>
                    @endif
                </div>
            </header>
            <div class="ldv-transfer__list">
                @forelse($transfers as $transfer)
                    @php($progress = $transfer->progressPercentage())
                    <article wire:key="transfer-{{ $transfer->id }}">
                        <div class="ldv-transfer__icon is-{{ $transfer->type }}">{{ $transfer->type === 'import' ? '↑' : '↓' }}</div>
                        <div class="ldv-transfer__info">
                            <div>
                                <strong>{{ $transfer->type === 'import' ? 'استيراد' : 'تصدير' }} {{ ['orders' => 'الطلبات', 'customers' => 'العملاء', 'products' => 'المنتجات'][$transfer->entity] ?? $transfer->entity }}</strong>
                                <span>{{ $transfer->created_at->diffForHumans() }} · {{ $transfer->user?->name }}</span>
                            </div>
                            <div class="ldv-transfer__bar"><i style="width:{{ $progress }}%"></i></div>
                            <small>{{ number_format($transfer->processed_rows) }} / {{ number_format($transfer->total_rows) }} · نجح {{ number_format($transfer->succeeded_rows) }} · أخفق {{ number_format($transfer->failed_rows) }}</small>
                            @if($transfer->error_message)<b class="ldv-transfer__error">{{ $transfer->error_message }}</b>@endif
                        </div>
                        <div class="ldv-transfer__state is-{{ $transfer->status }}">
                            <span>{{ ['queued' => 'بالانتظار', 'processing' => 'قيد المعالجة', 'completed' => 'مكتمل', 'failed' => 'فشل'][$transfer->status] ?? $transfer->status }}</span>
                            <strong>{{ $progress }}%</strong>
                            @if($transfer->type === 'export' && $transfer->status === 'completed')
                                <a href="{{ $this->downloadUrl($transfer) }}">تنزيل CSV</a>
                            @endif
                            @if(! $transfer->isRunning())
                                <button
                                    type="button"
                                    wire:click="deleteTransfer({{ $transfer->id }})"
                                    wire:confirm="حذف هذه العملية وملفاتها نهائيًا؟"
                                    title="حذف العملية"
                                >حذف</button>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="ldv-transfer__empty">لا توجد عمليات بعد. ابدأ أول استيراد أو تصدير من الأعلى.</div>
                @endforelse
            </div>
        </section>
    </div>
</x-filament-panels::page>
