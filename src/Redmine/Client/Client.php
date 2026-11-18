<?php

declare(strict_types=1);

namespace Redmine\Client;

use Redmine\Api;
use Redmine\Exception\InvalidApiNameException;

/**
 * client interface.
 */
interface Client
{
    /**
     * @throws InvalidApiNameException if $name is not a valid api name
     */
    public function getApi(string $name): Api;

    /**
     * Sets to an existing username so api calls can be
     * impersonated to this user.
     */
    public function startImpersonateUser(string $username): void;

    /**
     * Remove the user impersonate.
     */
    public function stopImpersonateUser(): void;
}
