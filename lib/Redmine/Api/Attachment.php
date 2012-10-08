<?php

namespace Redmine\Api;

/**
 * Attachment details
 *
 * @link   http://www.redmine.org/projects/redmine/wiki/Rest_Attachments
 * @author Kevin Saliou <kevin at saliou dot name>
 */
class Attachment extends AbstractApi
{
    /**
     * Get extended information about an attachment
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_Attachments
     *
     * @param  string $id the attachment number
     * @return array  information about the attachment
     */
    public function show($id)
    {
        return $this->get('/attachments/'.urlencode($id).'.json');
    }
}
