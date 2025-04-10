<?php

namespace Griiv\SynchroEngine\Core\Notifier\Notification;

use Griiv\SynchroEngine\Core\Api\KchatApi;
use Griiv\SynchroEngine\Core\Notifier\Message\ChatMessage;
use Griiv\SynchroEngine\Core\Notifier\Recipient\Recipient;

class ChatNotification extends Notification
{

    private $kchatApi;

    public function __construct(KchatApi $kchatApi)
    {
        $this->kchatApi = $kchatApi;
    }

    public function notify(?Recipient $recipient): void
    {
        $message = ChatMessage::fromNotification($this);

        $this->kchatApi->sendMessage($message, getenv('GRIIVSYNCHRO_KCHAT_CHANNEL_ID'));
    }

    public function supports(Notification $notification, Recipient $recipient): bool
    {
        return ($notification instanceof ChatNotification);
    }

}