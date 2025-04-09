<?php

namespace Griiv\SynchroEngine\Core\Notifier\Notification;

use Griiv\SynchroEngine\Core\Notifier\Notifier\Message\MessageInterface;
use Griiv\SynchroEngine\Core\Notifier\Recipient\Recipient;

class ChatNotification extends Notification
{
    public function notify(Recipient $recipient): void
    {
        //parent::notify($message, $recipient, $transportName);
    }

    public function supports(Notification $notification, Recipient $recipient): bool
    {
        return ($notification instanceof ChatNotification);
    }
}