<?php

namespace Redmine;

use Redmine\Api\ApiInterface;

/**
 * client interface.
 */
interface ClientInterface
{
    /**
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return ApiInterface
     */
    public function getApi(string $name): ApiInterface;
}
