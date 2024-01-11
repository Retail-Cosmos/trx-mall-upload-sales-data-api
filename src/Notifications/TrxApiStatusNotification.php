<?php

namespace RetailCosmos\TrxMallUploadSalesDataApi\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrxApiStatusNotification extends Notification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(public string $name, public string $status, public string $messages)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable)
    {
        return (new MailMessage)
            ->subject('TRX Mall Sales Data Upload Status Notification')
            ->markdown('trx-mall-upload-sales-data-api::mail.api-status', [
                'name' => $this->name,
                'status' => $this->status,
                'messages' => $this->messages,
            ]);
    }
}
