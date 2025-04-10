<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Griiv\SynchroEngine\Core\Notifier\Message;

use Griiv\SynchroEngine\Core\Notifier\Notification\Notification;
use Symfony\Component\Mime\RawMessage;

final class ChatMessage implements MessageInterface
{
    private $message;

    public function __construct(RawMessage $message, MessageOptionsInterface $options = null)
    {
        $this->message = $message;
    }

    public static function fromNotification(Notification $notification): self
    {
        $message = (new RawMessage($notification->getContent()));

        return new self($message);
    }

    public function getMessage(): RawMessage
    {
        return $this->message;
    }

    public function getRecipientId(): ?string
    {
        return null;
    }

    public function getSubject(): string
    {
        return '';
    }

    public function getOptions(): ?MessageOptionsInterface
    {
        return null;
    }

    public function getTransport(): ?string
    {
        return $this->message instanceof RawMessage ? $this->message->getHeaders()->getHeaderBody('X-Transport') : null;
    }
}