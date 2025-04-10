<?php

namespace Griiv\SynchroEngine\Core\Notifier;

use Griiv\SynchroEngine\Core\Notifier\Recipient\NoRecipient;
use Griiv\SynchroEngine\Core\Notifier\Notification\Notification;
use Griiv\SynchroEngine\Core\Notifier\Recipient\Recipient;

final class Notifier implements NotifierInterface
{
    private $notifications = [];


    public function __construct(array $notifications)
    {
        $this->notifications = $notifications;
    }

    public function send(Recipient ...$recipients): void
    {
        if (!$recipients) {
            $recipients = [new NoRecipient()];
        }

        foreach ($recipients as $recipient) {
            /** @var Notification $notification */
            foreach ($this->notifications as $notification) {
                $notification->notify($recipient);
            }
        }
    }
}