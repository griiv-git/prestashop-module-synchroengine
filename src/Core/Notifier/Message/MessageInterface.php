<?php

namespace Griiv\SynchroEngine\Core\Notifier\Message;

interface MessageInterface
{
    public function getRecipientId(): ?string;

    public function getSubject(): string;

    public function getOptions(): ?MessageOptionsInterface;

    public function getTransport(): ?string;
}