<?php

namespace Griiv\SynchroEngine\Core\Notifier\Notification;

use Configuration;
use Griiv\SynchroEngine\Core\Notifier\Message\EmailMessage;
use Griiv\SynchroEngine\Core\Notifier\Recipient\Recipient;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

class EmailNotification extends Notification
{
    public function notify(Recipient $recipient): void
    {
        $emailMessage = EmailMessage::fromNotification($this, $recipient);

        $message = $this->createSwiftMessage($emailMessage, $recipient);

        $connection = (new Swift_SmtpTransport(
            getenv('GRIIVSYNCHRO_SMTP_HOST'),
            getenv('GRIIVSYNCHRO_SMTP_PORT'),
            'ssl',
        ))
            ->setUsername(getenv('GRIIVSYNCHRO_SMTP_EMAIL'))
            ->setPassword(getenv('GRIIVSYNCHRO_SMTP_PWD'));

        $swift = new Swift_Mailer($connection);
        $swift->send($message);
    }

    public function createSwiftMessage(EmailMessage $emailMessage, Recipient $recipient): Swift_Message
    {
        $message = new Swift_Message();

        $configuration = Configuration::getMultiple(
            [
                'PS_SHOP_EMAIL',
                'PS_MAIL_METHOD',
                'PS_MAIL_SERVER',
                'PS_MAIL_USER',
                'PS_MAIL_PASSWD',
                'PS_SHOP_NAME',
                'PS_MAIL_SMTP_ENCRYPTION',
                'PS_MAIL_SMTP_PORT',
                'PS_MAIL_TYPE',
                'PS_MAIL_DKIM_ENABLE',
                'PS_MAIL_DKIM_DOMAIN',
                'PS_MAIL_DKIM_SELECTOR',
                'PS_MAIL_DKIM_KEY',
            ],
            null,
            null,
            \Context::getContext()->shop->id
        );

        $from = $configuration['PS_SHOP_EMAIL'];
        $fromName = "Griiv Synchro Engine - " . $configuration['PS_SHOP_NAME'];


        $message->addTo($recipient->getEmail());
        $message->setFrom('sonde@griiv.fr', $fromName);
        $message->setBody($emailMessage->getMessage()->getTextBody(), 'text/plain', 'utf-8');
        $message->setSubject($this->getDefaultEmoji() . ' ' . $emailMessage->getMessage()->getSubject());

        $message->setCharset('utf-8');

        return $message;
    }

    public function supports(Notification $notification, Recipient $recipient): bool
    {
        return ($notification instanceof EmailNotification);
    }
}