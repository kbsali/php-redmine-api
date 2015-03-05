<?php

namespace Redmine\Api;

/**
 * Attachment details.
 *
 * @link   http://www.redmine.org/projects/redmine/wiki/Rest_Attachments
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 */
class Attachment extends AbstractApi
{
    /**
     * Get extended information about an attachment.
     *
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_Attachments
     *
     * @param string $id the attachment number
     *
     * @return array information about the attachment
     */
    public function show($id)
    {
        return $this->get('/attachments/'.urlencode($id).'.json');
    }

    /**
     * Get attachment content as a binary file.
     *
     * @param string $id the attachment number
     *
     * @return string the attachment content
     */
    public function download($id)
    {
        return $this->get('/attachments/'.urlencode($id), false);
    }

    /**
     * Upload a file to redmine.
     *
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_api#Attaching-files
     *
     * @param string $attachment the attachment content
     *
     * @return array information about the attachment
     */
    public function upload($attachment)
    {
        return $this->post('/uploads.json', $attachment);
    }
}
