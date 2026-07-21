<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Reviews\ReviewResource;
use App\Models\Customer;
use App\Support\BankTransferMessage;
use App\Support\OrderDeletionService;
use App\Support\OrderMessageTemplate;
use App\Support\WhatsAppUrl;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\URL;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (array_key_exists('customer_phone', $data)) {
            $phone = trim((string) $data['customer_phone']);
            $customerId = (int) ($data['customer_id'] ?? $this->record->customer_id);
            $customer = Customer::query()
                ->whereKey($customerId)
                ->where('account_id', $this->record->account_id)
                ->first();

            if ($customer && $customer->phone !== $phone) {
                $oldPhone = $customer->phone;
                $customer->update(['phone' => $phone]);
                $this->record->activities()->create([
                    'user_id' => auth()->id(),
                    'type' => 'update',
                    'body' => 'تم تحديث رقم هاتف العميل من '.$oldPhone.' إلى '.$phone.'.',
                    'metadata' => [
                        'event' => 'customer_phone_updated',
                        'old_phone' => $oldPhone,
                        'new_phone' => $phone,
                    ],
                ]);
            }

            unset($data['customer_phone']);
        }

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
            Action::make('complete_follow_up')
                ->label('تمت المتابعة')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('تأكيد إنجاز المتابعة')
                ->modalDescription('سيتم إغلاق التذكير وتسجيل العملية تلقائيًا في سجل النشاط.')
                ->action(function ($record): void {
                    $record->update(['follow_up_completed_at' => now()]);
                    Notification::make()->title('تم إغلاق التذكير وتسجيل النشاط')->success()->send();
                })
                ->visible(fn ($record): bool => $record->hasPendingFollowUp()),
            Action::make('reopen_follow_up')
                ->label('إعادة فتح التذكير')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->action(function ($record): void {
                    $record->update(['follow_up_completed_at' => null]);
                    Notification::make()->title('تمت إعادة فتح التذكير')->success()->send();
                })
                ->visible(fn ($record): bool => filled($record->follow_up_at) && filled($record->follow_up_completed_at)),
            Action::make('whatsapp_customer')
                ->label('فتح واتساب للزبون')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('success')
                ->url(function ($record): string {
                    $locale = (string) (data_get($record->form_data, '_locale') ?: $record->landingPage?->default_locale ?: 'ar');
                    $message = OrderMessageTemplate::render($record, $locale);

                    return WhatsAppUrl::forOrder($record, $message);
                }, true)
                ->visible(fn ($record): bool => WhatsAppUrl::hasValidOrderPhone($record)),
            Action::make('whatsapp_customer_en')
                ->label('WhatsApp English')
                ->icon('heroicon-o-language')
                ->color('success')
                ->url(fn ($record): string => WhatsAppUrl::forOrder($record, OrderMessageTemplate::render($record, 'en')), true)
                ->visible(fn ($record): bool => WhatsAppUrl::hasValidOrderPhone($record)),
            Action::make('whatsapp_invoice')
                ->label('واتساب + رابط الفاتورة')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->url(fn ($record): string => WhatsAppUrl::forOrder($record, 'الفاتورة: '.URL::temporarySignedRoute('orders.invoice', now()->addDays(7), ['order' => $record->id])), true)
                ->visible(fn ($record): bool => WhatsAppUrl::hasValidOrderPhone($record)),
            Action::make('whatsapp_bank_details')
                ->label('واتساب بيانات البنك')
                ->icon('heroicon-o-banknotes')
                ->color('info')
                ->url(fn ($record): string => WhatsAppUrl::forOrder($record, BankTransferMessage::render($record)), true)
                ->visible(fn ($record): bool => WhatsAppUrl::hasValidOrderPhone($record) && BankTransferMessage::isConfigured($record->account)),
            Action::make('whatsapp_review')
                ->label('واتساب + رابط التقييم')
                ->icon('heroicon-o-star')
                ->color('warning')
                ->url(function ($record): string {
                    $reviewUrl = URL::temporarySignedRoute('reviews.order.form', now()->addDays(90), ['order' => $record->id]);
                    $message = 'مرحبًا '.$record->customer?->name.'، شكرًا لطلبك منّا. يسعدنا تقييم تجربتك عبر الرابط التالي: '.$reviewUrl;

                    return WhatsAppUrl::forOrder($record, $message);
                }, true)
                ->visible(fn ($record): bool => WhatsAppUrl::hasValidOrderPhone($record) && ! $record->review()->exists()),
            Action::make('open_review')
                ->label('عرض تقييم العميل')
                ->icon('heroicon-o-star')
                ->color('info')
                ->url(fn ($record): string => ReviewResource::getUrl('edit', ['record' => $record->review]), true)
                ->visible(fn ($record): bool => $record->review()->exists()),
            Action::make('invoice')
                ->label('عرض الفاتورة')
                ->icon('heroicon-o-document-text')
                ->url(fn ($record): string => URL::temporarySignedRoute('orders.invoice', now()->addDays(7), ['order' => $record->id]), true),
            Action::make('delete_test_order')
                ->label('حذف طلب تجريبي')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->modalHeading('حذف الطلب التجريبي نهائيًا')
                ->modalDescription('سيُحذف الطلب وسجل نشاطه ومرفقاته نهائيًا، وستُعاد كميته إلى المخزون إذا سبق خصمها. استخدم هذا الخيار للطلبات التجريبية فقط.')
                ->modalSubmitActionLabel('نعم، حذف نهائي')
                ->requiresConfirmation()
                ->action(function ($record): void {
                    app(OrderDeletionService::class)->delete($record);
                    $this->redirect(OrderResource::getUrl('index'));
                })
                ->successNotificationTitle('تم حذف الطلب التجريبي'),
        ];
    }
}
