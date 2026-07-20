@php($ticker = (array) data_get($landingPage->settings, 'store_ticker', []))
@php($tickerItems = collect($ticker['items'] ?? [])->filter(fn ($item) => ($item['is_active'] ?? true) && filled(app()->getLocale() === 'ar' ? ($item['text_ar'] ?? $item['text_en'] ?? null) : ($item['text_en'] ?? $item['text_ar'] ?? null)))->values())
@if(($ticker['enabled'] ?? false) && $tickerItems->isNotEmpty())
    @php($tickerStyle = in_array($ticker['style'] ?? null, ['solid','gradient','dark','outline','glass'], true) ? $ticker['style'] : 'gradient')
    @php($tickerDirection = ($ticker['direction'] ?? 'left') === 'right' ? 'right' : 'left')
    @php($tickerFont = ['inherit' => 'inherit', 'cairo' => 'Cairo, Tahoma, sans-serif', 'tajawal' => 'Tajawal, Tahoma, sans-serif', 'inter' => 'Inter, Arial, sans-serif', 'noto' => 'Noto Sans Arabic, Tahoma, sans-serif'][$ticker['font_family'] ?? 'inherit'] ?? 'inherit')
    @php($tickerWeight = in_array((int) ($ticker['font_weight'] ?? 800), [500,700,800,900], true) ? (int) ($ticker['font_weight'] ?? 800) : 800)
    <aside class="store-ticker ticker-style-{{ $tickerStyle }} ticker-move-{{ $tickerDirection }} {{ ($ticker['full_width'] ?? true) ? 'ticker-full-width' : 'ticker-contained' }} {{ ($ticker['pause_hover'] ?? true) ? 'ticker-pause-hover' : '' }}" style="--ticker-bg:{{ $ticker['background_color'] ?? '#111827' }};--ticker-secondary:{{ $ticker['secondary_color'] ?? '#263A63' }};--ticker-text:{{ $ticker['text_color'] ?? '#FFFFFF' }};--ticker-accent:{{ $ticker['accent_color'] ?? '#F59E0B' }};--ticker-border:{{ $ticker['border_color'] ?? '#E2E8F0' }};--ticker-font:{{ $tickerFont }};--ticker-font-size:{{ max(11,min(28,(int)($ticker['font_size'] ?? 14))) }}px;--ticker-font-weight:{{ $tickerWeight }};--ticker-height:{{ max(36,min(100,(int)($ticker['height'] ?? 52))) }}px;--ticker-gap:{{ max(16,min(120,(int)($ticker['gap'] ?? 48))) }}px;--ticker-speed:{{ max(6,min(180,(int)($ticker['speed'] ?? 28))) }}s" aria-label="{{ app()->getLocale() === 'ar' ? 'إعلانات المتجر' : 'Store announcements' }}">
        <div class="store-ticker-viewport">
            <div class="store-ticker-track">
                @foreach([false, true] as $duplicate)
                    <div class="store-ticker-group" @if($duplicate) aria-hidden="true" @endif>
                        @foreach($tickerItems as $item)
                            @php($tickerText = app()->getLocale() === 'ar' ? ($item['text_ar'] ?? $item['text_en']) : ($item['text_en'] ?? $item['text_ar']))
                            @php($tickerIcon = in_array($item['icon'] ?? null, ['megaphone','sparkles','truck','gift','tag','shield','phone'], true) ? $item['icon'] : 'none')
                            @if(filled($item['url'] ?? null))<a class="store-ticker-item {{ ($item['highlight'] ?? false) ? 'is-highlighted' : '' }}" href="{{ $item['url'] }}" @if($item['open_new_tab'] ?? false) target="_blank" rel="noopener" @endif>
                            @else<span class="store-ticker-item {{ ($item['highlight'] ?? false) ? 'is-highlighted' : '' }}">@endif
                                @if($tickerIcon !== 'none')
                                    <span class="store-ticker-icon" aria-hidden="true">
                                        @if($tickerIcon === 'megaphone')<svg viewBox="0 0 24 24"><path d="M3 11v2a2 2 0 0 0 2 2h2l2 5h3l-1.5-5L20 18V6l-9.5 3H5a2 2 0 0 0-2 2Z"/></svg>
                                        @elseif($tickerIcon === 'truck')<svg viewBox="0 0 24 24"><path d="M3 6h11v11H3V6Zm11 4h4l3 3v4h-7v-7Z"/><circle cx="7" cy="19" r="2"/><circle cx="18" cy="19" r="2"/></svg>
                                        @elseif($tickerIcon === 'gift')<svg viewBox="0 0 24 24"><path d="M4 10h16v11H4V10Zm-1-5h18v5H3V5Zm9 0v16M12 5H8.5A2.5 2.5 0 1 1 11 2.5L12 5Zm0 0h3.5A2.5 2.5 0 1 0 13 2.5L12 5Z"/></svg>
                                        @elseif($tickerIcon === 'tag')<svg viewBox="0 0 24 24"><path d="m3 12 9 9 9-9-9-9H3v9Z"/><circle cx="7.5" cy="7.5" r="1.5"/></svg>
                                        @elseif($tickerIcon === 'shield')<svg viewBox="0 0 24 24"><path d="M12 3 4 6v6c0 5 3.4 8 8 9 4.6-1 8-4 8-9V6l-8-3Z"/><path d="m8.5 12 2.2 2.2 4.8-5"/></svg>
                                        @elseif($tickerIcon === 'phone')<svg viewBox="0 0 24 24"><path d="M5 3h4l2 5-2.5 1.5a15 15 0 0 0 6 6L16 13l5 2v4c0 1.1-.9 2-2 2C10.2 21 3 13.8 3 5c0-1.1.9-2 2-2Z"/></svg>
                                        @else<svg viewBox="0 0 24 24"><path d="m12 2 1.8 6.2L20 10l-6.2 1.8L12 18l-1.8-6.2L4 10l6.2-1.8L12 2Z"/><path d="m19 16 .7 2.3L22 19l-2.3.7L19 22l-.7-2.3L16 19l2.3-.7L19 16Z"/></svg>@endif
                                    </span>
                                @endif
                                <span>{{ $tickerText }}</span>
                                @if(filled($item['url'] ?? null))<span class="store-ticker-arrow" aria-hidden="true">↗</span>@endif
                            @if(filled($item['url'] ?? null))</a>@else</span>@endif
                            @if(($ticker['show_separators'] ?? true) && !$loop->last)<i class="store-ticker-separator" aria-hidden="true"></i>@endif
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </aside>
@endif
