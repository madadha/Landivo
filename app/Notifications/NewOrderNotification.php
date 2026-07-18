<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array { return ['mail']; }

    public function toMail(object $notifiable): MailMessage
    {
        $customer = $this->order->customer;
        $offer = data_get($this->order->form_data, 'offer');

        return (new MailMessage)
            ->subject('طلب جديد '.$this->order->order_number)
            ->greeting('تم استلام طلب جديد')
            ->line('رقم الطلب: '.$this->order->order_number)
            ->line('الاسم: '.($customer?->name ?? 'غير محدد'))
            ->line('الهاتف: '.($customer?->phone ?? 'غير محدد'))
            ->line('البريد الإلكتروني: '.($customer?->email ?? 'غير محدد'))
            ->line('العرض المختار: '.($offer ?: 'غير محدد'))
            ->line('الإجمالي: '.$this->order->total.' '.$this->order->currency)
            ->action('فتح الطلب', url('/admin/orders/'.$this->order->id.'/edit'));
    }
}
