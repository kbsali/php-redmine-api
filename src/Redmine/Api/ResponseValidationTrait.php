<?php

declare(strict_types=1);

namespace Redmine\Api;

use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Serializer\JsonSerializer;

/**
 * Validate the last response of an API.
 *
 * @internal
 */
trait ResponseValidationTrait
{
    /**
     * @throws UnexpectedResponseException if the status code of the last response is not one of the expected status codes
     */
    private function throwOnUnexpectedStatusCode(int ...$expectedStatusCodes): void
    {
        if (!in_array($this->lastResponse->getStatusCode(), $expectedStatusCodes, true)) {
            throw UnexpectedResponseException::create($this->lastResponse);
        }
    }

    /**
     * @throws UnexpectedResponseException if the last response is not a successful response with a JSON array
     *
     * @return array<mixed>
     */
    private function getJsonArrayOrThrow(): array
    {
        $this->throwOnUnexpectedStatusCode(200);

        $body = $this->lastResponse->getContent();

        if ($body === '') {
            throw UnexpectedResponseException::create($this->lastResponse);
        }

        try {
            $data = JsonSerializer::createFromString($body)->getNormalized();
        } catch (SerializerException $e) {
            throw UnexpectedResponseException::create($this->lastResponse, $e);
        }

        if (!is_array($data)) {
            throw UnexpectedResponseException::create($this->lastResponse);
        }

        return $data;
    }
}
