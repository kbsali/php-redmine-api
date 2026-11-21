<?php

declare(strict_types=1);

namespace Redmine\Api;

use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Http\HttpClient;
use Redmine\Http\HttpFactory;
use Redmine\Serializer\JsonSerializer;
use Redmine\Serializer\PathSerializer;

/**
 * Attachment details.
 *
 * @see   http://www.redmine.org/projects/redmine/wiki/Rest_Attachments
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 */
final class Attachment extends AbstractApi
{
    use ResponseValidationTrait;

    public static function fromHttpClient(HttpClient $httpClient): self
    {
        return new self($httpClient);
    }

    private function __construct(HttpClient $httpClient)
    {
        parent::__construct($httpClient);
    }

    /**
     * Get extended information about an attachment.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Attachments
     *
     * @param int $id the attachment number
     *
     * @throws UnexpectedResponseException if the Redmine server delivers an unexpected response
     *
     * @return array<mixed> information about the attachment
     */
    public function show($id): array
    {
        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeJsonRequest(
            'GET',
            '/attachments/' . urlencode(strval($id)) . '.json',
        ));

        return $this->getJsonArrayOrThrow();
    }

    /**
     * Update information about an attachment.
     *
     * @see https://www.redmine.org/projects/redmine/wiki/Rest_Attachments#PATCH
     *
     * @param int          $id     the attachment id
     * @param array<mixed> $params available $params:
     *                             - filename: filename of the attachment
     *                             - description: new description of the attachment
     *
     * @throws SerializerException if $params contains invalid values
     * @throws UnexpectedResponseException if the Redmine server delivers an unexpected response
     *
     * @return true if the request was successful
     */
    final public function update(int $id, array $params): bool
    {
        // Other values are sent unchanged, only the marker for clearing a field is converted
        $params = array_map(
            static fn($value) => $value === Param::Clear ? '' : $value,
            $params,
        );

        // we are using `PUT` instead of documented `PATCH`
        // @see https://github.com/kbsali/php-redmine-api/pull/395#issuecomment-2004089154
        // @see https://www.redmine.org/projects/redmine/wiki/Rest_Attachments#PATCH
        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeJsonRequest(
            'PUT',
            '/attachments/' . $id . '.json',
            JsonSerializer::createFromArray(['attachment' => $params])->getEncoded(),
        ));

        $this->throwOnUnexpectedStatusCode(204);

        return true;
    }

    /**
     * Get attachment content as a binary file.
     *
     * @param int $id the attachment number
     *
     * @throws UnexpectedResponseException if the Redmine server delivers an unexpected response
     *
     * @return string the attachment content
     */
    public function download($id): string
    {
        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeRequest(
            'GET',
            '/attachments/download/' . urlencode(strval($id)),
        ));

        $this->throwOnUnexpectedStatusCode(200);

        return $this->lastResponse->getContent();
    }

    /**
     * Upload a file to redmine.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_api#Attaching-files
     * available $params :
     * - filename: filename of the attachment
     *
     * @param string       $attachment the attachment content
     * @param array<mixed> $params     optional parameters to be passed to the api
     *
     * @return string information about the attachment
     */
    public function upload($attachment, $params = []): string
    {
        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeRequest(
            'POST',
            PathSerializer::create('/uploads.json', $params)->getPath(),
            'application/octet-stream',
            $attachment,
        ));

        $body = $this->lastResponse->getContent();

        $this->throwOnUnexpectedStatusCode(201);

        return $body;
    }

    /**
     * Delete an attachment.
     *
     * @see https://www.redmine.org/projects/redmine/wiki/Rest_Attachments#DELETE
     *
     * @param int $id id of the attachment
     *
     * @return string empty string on success
     */
    public function remove($id): string
    {
        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'DELETE',
            '/attachments/' . urlencode(strval($id)) . '.xml',
        ));

        $body = $this->lastResponse->getContent();

        $this->throwOnUnexpectedStatusCode(204);

        return $body;
    }
}
