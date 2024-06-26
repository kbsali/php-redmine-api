<?php

namespace Redmine\Api;

use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Http\HttpFactory;
use Redmine\Serializer\JsonSerializer;
use Redmine\Serializer\PathSerializer;
use SimpleXMLElement;

/**
 * Attachment details.
 *
 * @see   http://www.redmine.org/projects/redmine/wiki/Rest_Attachments
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 */
class Attachment extends AbstractApi
{
    /**
     * Get extended information about an attachment.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Attachments
     *
     * @param int $id the attachment number
     *
     * @return array|false|string information about the attachment as array or false|string on error
     */
    public function show($id)
    {
        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeJsonRequest(
            'GET',
            '/attachments/' . urlencode(strval($id)) . '.json',
        ));

        $body = $this->lastResponse->getContent();

        if ('' === $body) {
            return false;
        }

        try {
            return JsonSerializer::createFromString($body)->getNormalized();
        } catch (SerializerException $e) {
            return 'Error decoding body as JSON: ' . $e->getPrevious()->getMessage();
        }
    }

    /**
     * Update information about an attachment.
     *
     * @see https://www.redmine.org/projects/redmine/wiki/Rest_Attachments#PATCH
     *
     * @param int $id the attachment id
     * @param array $params available $params:
     *   - filename: filename of the attachment
     *   - description: new description of the attachment
     *
     * @throws SerializerException if $params contains invalid values
     * @throws UnexpectedResponseException if the Redmine server delivers an unexpected response
     *
     * @return true if the request was successful
     */
    final public function update(int $id, array $params): bool
    {
        // we are using `PUT` instead of documented `PATCH`
        // @see https://github.com/kbsali/php-redmine-api/pull/395#issuecomment-2004089154
        // @see https://www.redmine.org/projects/redmine/wiki/Rest_Attachments#PATCH
        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeJsonRequest(
            'PUT',
            '/attachments/' . $id . '.json',
            JsonSerializer::createFromArray(['attachment' => $params])->getEncoded(),
        ));

        if ($this->lastResponse->getStatusCode() !== 204) {
            throw UnexpectedResponseException::create($this->lastResponse);
        }

        return true;
    }

    /**
     * Get attachment content as a binary file.
     *
     * @param int $id the attachment number
     *
     * @return string|false the attachment content as string of false on error
     */
    public function download($id)
    {
        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeRequest(
            'GET',
            '/attachments/download/' . urlencode(strval($id)),
        ));

        if (200 !== $this->lastResponse->getStatusCode()) {
            return false;
        }

        return $this->lastResponse->getContent();
    }

    /**
     * Upload a file to redmine.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_api#Attaching-files
     * available $params :
     * - filename: filename of the attachment
     *
     * @param string $attachment the attachment content
     * @param array  $params     optional parameters to be passed to the api
     *
     * @return string information about the attachment
     */
    public function upload($attachment, $params = [])
    {
        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeRequest(
            'POST',
            PathSerializer::create('/uploads.json', $params)->getPath(),
            'application/octet-stream',
            $attachment,
        ));

        return $this->lastResponse->getContent();
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
    public function remove($id)
    {
        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'DELETE',
            '/attachments/' . urlencode(strval($id)) . '.xml',
        ));

        return $this->lastResponse->getContent();
    }
}
