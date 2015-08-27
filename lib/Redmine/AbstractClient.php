<?php

namespace Redmine;

use Redmine\Api\SimpleXMLElement;

/**
 * Simple PHP Redmine client.
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 * Website: http://github.com/kbsali/php-redmine-api
 */
abstract class AbstractClient
{
    /**
     * @var array
     */
    protected static $defaultPorts = array(
        'http' => 80,
        'https' => 443,
    );

    /**
     * @var int
     */
    protected $port;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $apikeyOrUsername;

    /**
     * @var string or null
     */
    protected $pass;

    /**
     * @var bool
     */
    protected $checkSslCertificate = false;

    /**
     * @var bool
     */
    protected $checkSslHost = false;

    /**
     * @var int
     */
    protected $sslVersion = 0;

    /**
     * @var bool Flag to determine authentication method
     */
    protected $useHttpAuth = true;

    /**
     * @var array APIs
     */
    protected $apis = array();

    /**
     * @var string|null username for impersonating API calls
     */
    protected $impersonateUser = null;

    /**
     * @var int|null Redmine response code, null if request is not still completed
     */
    protected $responseCode = null;

    /**
     * Error strings if json is invalid.
     */
    protected static $json_errors = array(
        JSON_ERROR_NONE => 'No error has occurred',
        JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
        JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
        JSON_ERROR_SYNTAX => 'Syntax error',
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
        $this->getPort();
        $this->apikeyOrUsername = $apikeyOrUsername;
        $this->pass = $pass;
    }

    /**
     * @param string $name
     *
     * @return Api\AbstractApi
     *
     * @throws \InvalidArgumentException
     */
    public function api($name)
    {
        $classes = array(
            'attachment' => 'Attachment',
            'group' => 'Group',
            'custom_fields' => 'CustomField',
            'issue' => 'Issue',
            'issue_category' => 'IssueCategory',
            'issue_priority' => 'IssuePriority',
            'issue_relation' => 'IssueRelation',
            'issue_status' => 'IssueStatus',
            'membership' => 'Membership',
            'news' => 'News',
            'project' => 'Project',
            'query' => 'Query',
            'role' => 'Role',
            'time_entry' => 'TimeEntry',
            'time_entry_activity' => 'TimeEntryActivity',
            'tracker' => 'Tracker',
            'user' => 'User',
            'version' => 'Version',
            'wiki' => 'Wiki',
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
     * Returns Url.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * HTTP GETs a json $path and tries to decode it.
     *
     * @param string $path
     * @param bool   $decode
     *
     * @return array
     */
    public function get($path, $decode = true)
    {
        if (false === $json = $this->runRequest($path, 'GET')) {
            return false;
        }

        if (!$decode) {
            return $json;
        }

        return $this->decode($json);
    }

    /**
     * Decodes json response.
     *
     * Returns $json if no error occured during decoding but decoded value is
     * null.
     *
     * @param string $json
     *
     * @return array|string
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
     * HTTP POSTs $params to $path.
     *
     * @param string $path
     * @param string $data
     *
     * @return mixed
     */
    public function post($path, $data)
    {
        return $this->runRequest($path, 'POST', $data);
    }

    /**
     * HTTP PUTs $params to $path.
     *
     * @param string $path
     * @param string $data
     *
     * @return array
     */
    public function put($path, $data)
    {
        return $this->runRequest($path, 'PUT', $data);
    }

    /**
     * HTTP PUTs $params to $path.
     *
     * @param string $path
     *
     * @return array
     */
    public function delete($path)
    {
        return $this->runRequest($path, 'DELETE');
    }

    /**
     * Turns on/off ssl certificate check.
     *
     * @param bool $check
     *
     * @return AbstractClient
     */
    public function setCheckSslCertificate($check = false)
    {
        $this->checkSslCertificate = $check;

        return $this;
    }

    /**
     * Get the on/off flag for ssl certificate check.
     *
     * @return bool
     */
    public function getCheckSslCertificate()
    {
        return $this->checkSslCertificate;
    }

    /**
     * Turns on/off ssl host certificate check.
     *
     * @param bool $check
     *
     * @return AbstractClient
     */
    public function setCheckSslHost($check = false)
    {
        $this->checkSslCertificate = $check;

        return $this;
    }

    /**
     * Get the on/off flag for ssl host certificate check.
     *
     * @return bool
     */
    public function getCheckSslHost()
    {
        return $this->checkSslHost;
    }

    /**
     * Forces the SSL/TLS version to use.
     * @see http://curl.haxx.se/libcurl/c/CURLOPT_SSLVERSION.html
     *
     * @param int $sslVersion
     *
     * @return AbstractClient
     */
    public function setSslVersion($sslVersion = 0)
    {
        $this->sslVersion = $sslVersion;

        return $this;
    }

    /**
     * Returns the SSL Version used.
     *
     * @return int
     */
    public function getSslVersion()
    {
        return $this->sslVersion;
    }

    /**
     * Turns on/off http auth.
     *
     * @param bool $use
     *
     * @return AbstractClient
     */
    public function setUseHttpAuth($use = true)
    {
        $this->useHttpAuth = $use;

        return $this;
    }

    /**
     * Get the on/off flag for http auth.
     *
     * @return bool
     */
    public function getUseHttpAuth()
    {
        return $this->useHttpAuth;
    }

    /**
     * Set the port of the connection.
     *
     * @param int $port
     *
     * @return AbstractClient
     */
    public function setPort($port = null)
    {
        if (null !== $port) {
            $this->port = (int) $port;
        }

        return $this;
    }

    /**
     * Returns Redmine response code.
     *
     * @return int
     */
    public function getResponseCode()
    {
        return (int) $this->responseCode;
    }

    /**
     * Returns the port of the current connection,
     * if not set, it will try to guess the port
     * from the url of the client.
     *
     * @return int the port number
     */
    public function getPort()
    {
        if (null !== $this->port) {
            return $this->port;
        }

        $tmp = parse_url($this->getUrl());
        if (isset($tmp['port'])) {
            $this->setPort($tmp['port']);
        } elseif (isset($tmp['scheme'])) {
            $this->setPort(self::$defaultPorts[$tmp['scheme']]);
        }

        return $this->port;
    }

    /**
     * Sets to an existing username so api calls can be
     * impersonated to this user.
     *
     * @param string|null $username
     *
     * @return AbstractClient
     */
    public function setImpersonateUser($username = null)
    {
        $this->impersonateUser = $username;

        return $this;
    }

    /**
     * Get the impersonate user.
     *
     * @return string|null
     */
    public function getImpersonateUser()
    {
        return $this->impersonateUser;
    }

    /**
     * Generates a set of content type and redmine
     * specific headers to be send with the request.
     *
     * @param string $path
     *
     * @return array
     */
    protected function generateHttpHeader($path)
    {
        $httpHeader = array(
            'Expect: ',
        );

        // Content type headers
        $tmp = parse_url($this->url.$path);
        if ('/uploads.json' === $path || '/uploads.xml' === $path) {
            $httpHeader[] = 'Content-Type: application/octet-stream';
        } elseif ('json' === substr($tmp['path'], -4)) {
            $httpHeader[] = 'Content-Type: application/json';
        } elseif ('xml' === substr($tmp['path'], -3)) {
            $httpHeader[] = 'Content-Type: text/xml';
        }

        // Redmine specific headers
        if ($this->impersonateUser) {
            $httpHeader[] = 'X-Redmine-Switch-User: '.$this->impersonateUser;
        }
        if (null === $this->pass) {
            $httpHeader[] = 'X-Redmine-API-Key: '.$this->apikeyOrUsername;
        }

        return $httpHeader;
    }

    /**
     * Process the response.
     *
     * @param string $response
     * @param string $contentType
     *
     * @return bool|SimpleXMLElement|string
     *
     * @throws \Exception If anything goes wrong on request
     */
    public function processResponse($response, $contentType)
    {
        if ($response) {
            // if response is XML, return an SimpleXMLElement object
            if (0 === strpos($contentType, 'application/xml')) {
                return new SimpleXMLElement($response);
            }

            return $response;
        }

        return false;
    }

    /**
     * @param string $path
     * @param string $method
     * @param string $data
     *
     * @return bool|SimpleXMLElement|string
     *
     * @throws \Exception If anything goes wrong on request
     */
    protected abstract function runRequest($path, $method = 'GET', $data = '');
}