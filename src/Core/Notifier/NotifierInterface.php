<?php

namespace Griiv\SynchroEngine\Core\Notifier;

use Griiv\SynchroEngine\Core\Notifier\Notification\Notification;
use Griiv\SynchroEngine\Core\Notifier\Recipient\Recipient;

interface NotifierInterface
{
    public function send(Recipient ...$recipients): void;
}