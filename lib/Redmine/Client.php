<?php

namespace Redmine;

/**
 * Simple PHP Redmine client
 * @author Kevin Saliou <kevin at saliou dot name>
 * Website: http://github.com/kbsali/php-redmine-api
 */
class Client
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $apikey;

    /**
     * @param string $url
     * @param string $apikey
     */
    public function __construct($url, $apikey)
    {
        $this->url    = $url;
        $this->apikey = $apikey;
    }

    /**
     * @param string $name
     * @return ApiInterface
     * @throws \InvalidArgumentException
     */
    public function api($name)
    {
        if (!isset($this->apis[$name])) {
            switch ($name) {

                case 'attachment':
                    $api = new Api\Attachment($this);
                    break;

                // @todo implement!
                // case 'group':
                //     $api = new Api\Group($this);
                //     break;

                case 'issue':
                    $api = new Api\Issue($this);
                    break;

                case 'issue_category':
                    $api = new Api\IssueCategory($this);
                    break;

                // @todo implement!
                // case 'issue_relation':
                //     $api = new Api\IssueRelation($this);
                //     break;

                case 'issue_status':
                    $api = new Api\IssueStatus($this);
                    break;

                case 'news':
                    $api = new Api\News($this);
                    break;

                case 'project':
                    $api = new Api\Project($this);
                    break;

                // @todo implement!
                // case 'project_membershipt':
                //     $api = new Api\ProjectMembershipt($this);
                //     break;

                case 'query':
                    $api = new Api\Query($this);
                    break;

                case 'role':
                    $api = new Api\Role($this);
                    break;

                case 'time_entry':
                    $api = new Api\TimeEntry($this);
                    break;

                case 'tracker':
                    $api = new Api\Tracker($this);
                    break;

                case 'user':
                    $api = new Api\User($this);
                    break;

                case 'version':
                    $api = new Api\Version($this);
                    break;

                default:
                    throw new \InvalidArgumentException();
            }

            $this->apis[$name] = $api;
        }

        return $this->apis[$name];
    }

    /**
     * HTTP GETs a json $path and tries to decode it
     * @param  string $path
     * @return array
     */
    public function get($path)
    {
        if (false === $json = $this->runRequest($path, 'GET')) {
            return false;
        }

        return json_decode($json, true);
    }

    /**
     * HTTP POSTs $params to $path
     * @param  string $path
     * @param  string $data
     * @return mixed
     */
    public function post($path, $data)
    {
        return $this->runRequest($path, 'POST', $data);
    }

    /**
     * HTTP PUTs $params to $path
     * @param  string $path
     * @param  string $data
     * @return array
     */
    public function put($path, $data)
    {
        return $this->runRequest($path, 'PUT', $data);
    }

    /**
     * HTTP PUTs $params to $path
     * @param  string $path
     * @return array
     */
    public function delete($path)
    {
        return $this->runRequest($path, 'DELETE');
    }

    /**
     * @param  string                        $path
     * @param  string                        $method
     * @param  string                        $data
     * @return false|SimpleXMLElement|string
     */
    private function runRequest($path, $method = 'GET', $data = '')
    {
        $curl = curl_init();
        if (isset($this->apikey)) {
            curl_setopt($curl, CURLOPT_USERPWD, $this->apikey.':'.rand(100000, 199999) );
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        }
        curl_setopt($curl, CURLOPT_URL, $this->url.$path);
        curl_setopt($curl, CURLOPT_PORT , 80);
        curl_setopt($curl, CURLOPT_VERBOSE, 0);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $tmp = parse_url($path);
        if ('xml' === substr($tmp['path'], -3)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: text/xml',
            ));
        }

        switch ($method) {
            case 'POST':
                curl_setopt($curl, CURLOPT_POST, 1);
                if (isset($data)) {curl_setopt($curl, CURLOPT_POSTFIELDS, $data);}
                break;
            case 'PUT':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
                if (isset($data)) {curl_setopt($curl, CURLOPT_POSTFIELDS, $data);}
                break;
            case 'DELETE':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            default: // get
                break;
        }
        try {
            $response = curl_exec($curl);
            if (curl_errno($curl)) {
                curl_close($curl);

                return false;
            }
            curl_close($curl);
        } catch (\Exception $e) {
            return false;
        }
        if ($response) {
            if ('<' === substr($response, 0, 1)) {
                return new \SimpleXMLElement($response);
            }

            return $response;
        }

        return true;
    }
}
