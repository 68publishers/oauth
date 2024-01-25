<?php

declare(strict_types=1);

namespace SixtyEightPublishers\OAuth\Tests\Fixtures;

use Nette\Security\IIdentity;
use Nette\Security\UserStorage;

final class InMemoryUserStorage implements UserStorage
{
    private bool $authenticated = false;

    private ?int $reason = null;

    private ?IIdentity $identity = null;

    public function saveAuthentication(IIdentity $identity): void
    {
        $this->authenticated = true;
        $this->reason = null;
        $this->identity = $identity;
    }

    public function clearAuthentication(bool $clearIdentity): void
    {
        $this->authenticated = false;
        $this->reason = 1;

        if ($clearIdentity) {
            $this->identity = null;
        }
    }

    public function getState(): array
    {
        return [
            $this->authenticated,
            $this->identity,
            $this->reason,
        ];
    }

    public function setExpiration(?string $expire, bool $clearIdentity): void
    {
    }
}
