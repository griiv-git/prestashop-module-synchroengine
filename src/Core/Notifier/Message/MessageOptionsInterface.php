<?php


namespace Griiv\SynchroEngine\Core\Notifier\Message;

interface MessageOptionsInterface
{
    public function toArray(): array;

    public function getRecipientId(): ?string;
}