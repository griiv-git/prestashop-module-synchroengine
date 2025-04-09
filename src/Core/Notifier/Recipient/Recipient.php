<?php

namespace Griiv\SynchroEngine\Core\Notifier\Recipient;

class Recipient implements RecipientInterface
{
    private $email;

    public function __construct(string $email = '')
    {
        $this->email = $email;
    }

    /**
     * @return $this
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}