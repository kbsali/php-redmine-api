<?php

declare(strict_types=1);

namespace Redmine\Exception;

use Redmine\Exception as RedmineException;
use Redmine\Http\Response;
use RuntimeException;
use Throwable;

/**
 * Exception if the Redmine server delivers an unexpected response.
 *
 * Use the following methods to investigate the response:
 *
 * - Redmine\Client\Client::getLastResponseStatusCode()
 * - Redmine\Client\Client::getLastResponseContentType()
 * - Redmine\Client\Client::getLastResponseBody()
 */
final class UnexpectedResponseException extends RuntimeException implements RedmineException
{
    /**
     * @var Response|null
     */
    private $response = null;

    public static function create(Response $response, Throwable $prev = null): self
    {
        $e = new self(
            'The Redmine server responded with an unexpected body.',
            ($prev !== null) ? $prev->getCode() : 1,
            $prev
        );

        $e->response = $response;

        return $e;
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }
}
