@php
    $page = app('livewire')->current();
    $method = match (true) {
        $page instanceof \Filament\Resources\Pages\EditRecord => 'save',
        $page instanceof \Filament\Resources\Pages\CreateRecord => 'create',
        $page instanceof \App\Filament\Pages\SystemSettings => 'save',
        default => null,
    };
@endphp

@if($method)
    <button type="button" class="ldv-header-save" wire:click="{{ $method }}" wire:loading.attr="disabled" wire:target="{{ $method }}">
        <x-filament::icon icon="heroicon-o-check" />
        <span wire:loading.remove wire:target="{{ $method }}">حفظ التغييرات</span>
        <span wire:loading wire:target="{{ $method }}">جارٍ الحفظ...</span>
    </button>
@endif
