<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\URL;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (array_key_exists('selected_offer', $data)) {
            $formData = (array) ($data['form_data'] ?? $this->record->form_data ?? []);

            if (filled($data['selected_offer'])) {
                $formData['offer'] = $data['selected_offer'];
            }

            $data['form_data'] = $formData;
            unset($data['selected_offer']);
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('whatsapp_customer')
                ->label('فتح واتساب للزبون')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('success')
                ->url(fn ($record): string => 'https://wa.me/'.preg_replace('/\D+/', '', (string) $record->customer?->phone).'?text='.rawurlencode('مرحباً '.$record->customer?->name.'، بخصوص طلبك رقم '.$record->order_number.' بقيمة '.$record->total.' '.$record->currency), true)
                ->visible(fn ($record): bool => filled($record->customer?->phone)),
            Action::make('whatsapp_invoice')
                ->label('واتساب + رابط الفاتورة')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->url(fn ($record): string => 'https://wa.me/'.preg_replace('/\D+/', '', (string) $record->customer?->phone).'?text='.rawurlencode('الفاتورة: '.URL::temporarySignedRoute('orders.invoice', now()->addDays(7), ['order' => $record->id])), true)
                ->visible(fn ($record): bool => filled($record->customer?->phone)),
            Action::make('invoice')
                ->label('عرض الفاتورة')
                ->icon('heroicon-o-document-text')
                ->url(fn ($record): string => URL::temporarySignedRoute('orders.invoice', now()->addDays(7), ['order' => $record->id]), true),
            DeleteAction::make(),
        ];
    }
}
