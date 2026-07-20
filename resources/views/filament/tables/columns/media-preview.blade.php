@php($record = $getRecord())
<div style="position:relative;overflow:hidden;display:grid;place-items:center;width:100%;aspect-ratio:16/10;border-radius:16px;background:linear-gradient(145deg,#f8fafc,#eef2f7);border:1px solid #e2e8f0">
    @if($record->file_exists && $record->is_image)
        <img src="{{ $record->public_url }}" alt="{{ $record->alt_text ?: $record->original_name }}" loading="lazy" style="width:100%;height:100%;object-fit:contain">
    @else
        <div style="display:grid;place-items:center;gap:8px;text-align:center;color:#64748b">
            <x-filament::icon :icon="$record->file_exists ? 'heroicon-o-document' : 'heroicon-o-exclamation-triangle'" style="width:44px;height:44px" />
            <strong>{{ strtoupper($record->extension ?: 'FILE') }}</strong>
        </div>
    @endif
    <span style="position:absolute;top:10px;inset-inline-end:10px;padding:4px 8px;border-radius:999px;background:{{ $record->file_exists ? '#0f172acc' : '#dc2626' }};color:#fff;font-size:11px;font-weight:800">
        {{ $record->file_exists ? $record->human_size : 'الملف مفقود' }}
    </span>
</div>
