<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use SensitiveParameter;

class VerifyEmailAuthentication extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        #[SensitiveParameter]
        public string $code,
        public int $codeExpiryMinutes,
    ) {}

    /** @return array<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $companyName = trim((string) ($notifiable->account?->name ?: config('app.name', 'Landivo')));
        $companyName = $companyName !== '' ? $companyName : 'Landivo';

        $message = (new MailMessage)
            ->subject(__('filament-panels::auth/multi-factor/email/notifications/verify-email-authentication.subject'));

        if (filled($fromAddress = config('mail.from.address'))) {
            $message->from($fromAddress, $companyName);
        }

        return $message->view('mail.auth.login-code', [
            'companyName' => $companyName,
            'userName' => $notifiable->name ?? null,
            'code' => $this->code,
            'expiryMinutes' => $this->codeExpiryMinutes,
        ]);
    }
}
