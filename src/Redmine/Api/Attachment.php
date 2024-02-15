<?php

namespace Redmine\Api;

use Redmine\Exception\SerializerException;
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
            '/attachments/download/' . urlencode(strval($id))
        ));

        $body = $this->lastResponse->getContent();

        return ('' === $body) ? false : $body;
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
        return $this->post(
            PathSerializer::create('/uploads.json', $params)->getPath(),
            $attachment
        );
    }

    /**
     * Delete an attachment.
     *
     * @see https://www.redmine.org/projects/redmine/wiki/Rest_Attachments#DELETE
     *
     * @param int $id id of the attachment
     *
     * @return false|\SimpleXMLElement|string
     */
    public function remove($id)
    {
        return $this->delete('/attachments/' . urlencode(strval($id)) . '.xml');
    }
}
