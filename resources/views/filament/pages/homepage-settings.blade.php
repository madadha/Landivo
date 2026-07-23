<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex items-center gap-3">
            <x-filament::button type="submit" icon="heroicon-o-check">
                حفظ إعدادات الصفحة الرئيسية
            </x-filament::button>

            <x-filament::button
                tag="a"
                color="gray"
                icon="heroicon-o-arrow-top-right-on-square"
                href="{{ route('site.home') }}"
                target="_blank"
            >
                معاينة الصفحة
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
