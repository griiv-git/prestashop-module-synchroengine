<?php

namespace  Griiv\SynchroEngine\Core\Notifier\Notification;

use Griiv\SynchroEngine\Core\Notifier\Recipient\Recipient;

interface NotificationInterface
{
    public function notify(Recipient $recipient): void;

    public function supports(Notification $notification, Recipient $recipient): bool;
}