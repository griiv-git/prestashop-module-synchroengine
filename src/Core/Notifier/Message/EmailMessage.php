<?php

namespace Griiv\SynchroEngine\Core\Notifier\Message;


use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\RawMessage;
use Griiv\SynchroEngine\Core\Notifier\Notification\Notification;
use Griiv\SynchroEngine\Core\Notifier\Recipient\Recipient;

final class EmailMessage implements MessageInterface
{
    private $message;
    private $envelope;

    public function __construct(RawMessage $message, Envelope $envelope = null)
    {
        $this->message = $message;
        $this->envelope = $envelope;
    }

    public static function fromNotification(Notification $notification, Recipient $recipient, string $transport = null): self
    {
        $email = (new Email())
            ->to($recipient->getEmail())
            ->subject($notification->getSubject())
            ->text($notification->getContent() ?: $notification->getSubject());

        return new self($email);
    }

    public function getMessage(): RawMessage
    {
        return $this->message;
    }

    public function getEnvelope(): ?Envelope
    {
        return $this->envelope;
    }

    /**
     * @return $this
     */
    public function envelope(Envelope $envelope): self
    {
        $this->envelope = $envelope;

        return $this;
    }

    public function getSubject(): string
    {
        return '';
    }

    public function getRecipientId(): ?string
    {
        return null;
    }

    public function getOptions(): ?\Griiv\SynchroEngine\Core\Notifier\Message\MessageOptionsInterface
    {
        return null;
    }

    /**
     * @return $this
     */
    public function transport(string $transport): self
    {
        if (!$this->message instanceof Email) {
            throw new \Exception('Cannot set a Transport on a RawMessage instance.');
        }

        $this->message->getHeaders()->addTextHeader('X-Transport', $transport);

        return $this;
    }

    public function getTransport(): ?string
    {
        return $this->message instanceof Email ? $this->message->getHeaders()->getHeaderBody('X-Transport') : null;
    }
}