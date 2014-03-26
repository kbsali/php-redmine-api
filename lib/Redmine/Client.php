<?php

namespace Redmine;

use SimpleXMLElement;

/**
 * Simple PHP Redmine client
 * @author Kevin Saliou <kevin at saliou dot name>
 * Website: http://github.com/kbsali/php-redmine-api
 */
class Client
{
    /**
     * @var array
     */
    private static $defaultPorts = array(
        'http'  => 80,
        'https' => 443,
    );

    /**
     * @var int
     */
    private $port;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $apikeyOrUsername;

    /**
     * @var string or null
     */
    private $pass;

    /**
     * @var boolean
     */
    private $checkSslCertificate = false;

    /**
     * @var boolean
     */
    private $checkSslHost = false;

    /**
     *
     * Flag to determine authentication method
     *
     * @var boolean
     */
    private $useHttpAuth = true;

    /**
     * @var array APIs
     */
    private $apis = array();

    /**
     * @var int|null Redmine response code, null if request is not still completed
     */
    private $responseCode = null;

    /**
     * Error strings if json is invalid
     */
    private static $json_errors = array(
        JSON_ERROR_NONE      => 'No error has occurred',
        JSON_ERROR_DEPTH     => 'The maximum stack depth has been exceeded',
        JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
        JSON_ERROR_SYNTAX    => 'Syntax error',
    );

    /**
     * Usage: apikeyOrUsername can be auth key or username.
     * Password needs to be set if username is given.
     *
     * @param string $url
     * @param string $apikeyOrUsername
     * @param string $pass             (string or null)
     */
    public function __construct($url, $apikeyOrUsername, $pass = null)
    {
        $this->url = $url;
        $this->apikeyOrUsername = $apikeyOrUsername;
        $this->pass = $pass;
    }

    /**
     * @param  string                    $name
     * @return Api\AbstractApi
     * @throws \InvalidArgumentException
     */
    public function api($name)
    {
        $classes = array(
            'attachment'          => 'Attachment',
            'group'               => 'Group',
            'custom_fields'       => 'CustomField',
            'issue'               => 'Issue',
            'issue_category'      => 'IssueCategory',
            'issue_priority'      => 'IssuePriority',
            'issue_relation'      => 'IssueRelation',
            'issue_status'        => 'IssueStatus',
            'membership'          => 'Membership',
            'news'                => 'News',
            'project'             => 'Project',
            'query'               => 'Query',
            'role'                => 'Role',
            'time_entry'          => 'TimeEntry',
            'time_entry_activity' => 'TimeEntryActivity',
            'tracker'             => 'Tracker',
            'user'                => 'User',
            'version'             => 'Version',
            'wiki'                => 'Wiki',
        );
        if (!isset($classes[$name])) {
            throw new \InvalidArgumentException();
        }
        if (isset($this->apis[$name])) {
            return $this->apis[$name];
        }
        $c = 'Redmine\Api\\'.$classes[$name];
        $this->apis[$name] = new $c($this);

        return $this->apis[$name];
    }

    /**
     * Returns Url
     * @return string
     */
    public function getUrl()
    {
       return $this->url;
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

        return $this->decode($json);
    }

    /**
     * Decodes json response
     * @param  string $json
     * @return array
     */
    public function decode($json)
    {
        $decoded = json_decode($json, true);
        if (null !== $decoded) {
            return $decoded;
        }
        if (JSON_ERROR_NONE === json_last_error()) {
            return $json;
        }

        return self::$json_errors[json_last_error()];
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
     * Turns on/off ssl certificate check
     * @param  boolean $check
     * @return Client
     */
    public function setCheckSslCertificate($check = false)
    {
        $this->checkSslCertificate = $check;

        return $this;
    }

    /**
     * Turns on/off ssl host certificate check
     * @param  boolean $check
     * @return Client
     */
    public function setCheckSslHost($check = false)
    {
        $this->checkSslHost = $check;

        return $this;
    }

    /**
     * Turns on/off http auth
     * @param  bool   $use
     * @return Client
     */
    public function setUseHttpAuth($use = true)
    {
        $this->useHttpAuth = $use;

        return $this;
    }

    /**
     * Set the port of the connection
     * @param  int    $port
     * @return Client
     */
    public function setPort($port = null)
    {
        if (null !== $port) {
            $this->port = (int) $port;
        }

        return $this;
    }

    /**
     * Returns Redmine response code
     * @return int
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * Returns the port of the current connection,
     * if not set, it will try to guess the port
     * from the given $urlPath
     * @param  string $urlPath the url called
     * @return int    the port number
     */
    public function getPort($urlPath = null)
    {
        if (null === $urlPath) {
            return $this->port;
        }
        if (null !== $this->port) {
            return $this->port;
        }
        $tmp = parse_url($urlPath);

        if (isset($tmp['port'])) {
            $this->setPort($tmp['port']);

            return $this->port;
        }
        $this->setPort(self::$defaultPorts[$tmp['scheme']]);

        return $this->port;
    }

    /**
     * @param  string                        $path
     * @param  string                        $method
     * @param  string                        $data
     * @return false|SimpleXMLElement|string
     * @throws \Exception                    If anything goes wrong on curl request
     */
    private function runRequest($path, $method = 'GET', $data = '')
    {
        $this->responseCode = null;
        $this->getPort($this->url.$path);

        $curl = curl_init();
        if (isset($this->apikeyOrUsername) && $this->useHttpAuth) {
            if (null === $this->pass) {
                curl_setopt($curl, CURLOPT_USERPWD, $this->apikeyOrUsername.':'.rand(100000, 199999) );
            } else {
                curl_setopt($curl, CURLOPT_USERPWD, $this->apikeyOrUsername.':'.$this->pass );
            }
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        }
        curl_setopt($curl, CURLOPT_URL, $this->url.$path);
        curl_setopt($curl, CURLOPT_VERBOSE, 0);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_PORT , $this->port);
        if (80 !== $this->port) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $this->checkSslCertificate);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, $this->checkSslHost);
        }

        $tmp = parse_url($this->url.$path);
        $httpHeader = array();
        if ('xml' === substr($tmp['path'], -3)) {
            $httpHeader[] = 'Content-Type: text/xml';
        }
        if ('/uploads.json' === $path || '/uploads.xml' === $path) {
            $httpHeader[] = 'Content-Type: application/octet-stream';
        } elseif ('json' === substr($tmp['path'], -4)) {
            $httpHeader[] = 'Content-Type: application/json';
        }

        if (!empty($httpHeader)) {
            if (null === $this->pass) {
                $httpHeader[] = 'X-Redmine-API-Key: '.$this->apikeyOrUsername;
            }
            curl_setopt($curl, CURLOPT_HTTPHEADER, $httpHeader);
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
            default: // GET
                break;
        }
        $response = curl_exec($curl);
        $this->responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if (curl_errno($curl)) {
            $e = new \Exception(curl_error($curl), curl_errno($curl));
            curl_close($curl);
            throw $e;
        }
        curl_close($curl);

        if ($response) {
            // if response is XML, return an SimpleXMLElement object
            if ('<' === substr($response, 0, 1)) {
                return new \SimpleXMLElement($response);
            }

            return $response;
        }

        return true;
    }
}
