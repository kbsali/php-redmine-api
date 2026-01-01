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
 * Use `getResponse()` to investigate the response
 */
final class UnexpectedResponseException extends RuntimeException implements RedmineException
{
    /**
     * @var Response|null
     */
    private $response;

    public static function create(Response $response, ?Throwable $prev = null): self
    {
        $e = new self(
            'The Redmine server replied with an unexpected response.',
            ($prev instanceof \Throwable) ? $prev->getCode() : 1,
            $prev,
        );

        $e->response = $response;

        return $e;
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }
}
