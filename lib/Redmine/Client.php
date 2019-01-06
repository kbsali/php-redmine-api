<?php

namespace Redmine;

/**
 * Simple PHP Redmine client.
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 * Website: http://github.com/kbsali/php-redmine-api
 *
 * @property Api\Attachment $attachment
 * @property Api\Group $group
 * @property Api\CustomField $custom_fields
 * @property Api\Issue $issue
 * @property Api\IssueCategory $issue_category
 * @property Api\IssuePriority $issue_priority
 * @property Api\IssueRelation $issue_relation
 * @property Api\IssueStatus $issue_status
 * @property Api\Membership $membership
 * @property Api\News $news
 * @property Api\Project $project
 * @property Api\Query $query
 * @property Api\Role $role
 * @property Api\TimeEntry $time_entry
 * @property Api\TimeEntryActivity $time_entry_activity
 * @property Api\Tracker $tracker
 * @property Api\User $user
 * @property Api\Version $version
 * @property Api\Wiki $wiki
 */
class Client
{
    /**
     * Value for CURLOPT_SSL_VERIFYHOST.
     *
     * @see http://curl.haxx.se/libcurl/c/CURLOPT_SSL_VERIFYHOST.html
     */
    const SSL_VERIFYHOST = 2;

    /**
     * @var array
     */
    private static $defaultPorts = [
        'http' => 80,
        'https' => 443,
    ];

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
     * @var string|null
     */
    private $pass;

    /**
     * @var bool
     */
    private $checkSslCertificate = false;

    /**
     * @var bool
     */
    private $checkSslHost = false;

    /**
     * @var int
     */
    private $sslVersion = 0;

    /**
     * @var bool Flag to determine authentication method
     */
    private $useHttpAuth = true;

    /**
     * @var array APIs
     */
    private $apis = [];

    /**
     * @var string|null username for impersonating API calls
     */
    protected $impersonateUser = null;

    /**
     * @var string|null customHost
     */
    protected $customHost = null;

    /**
     * @var int|null Redmine response code, null if request is not still completed
     */
    private $responseCode = null;

    /**
     * @var array cURL options
     */
    private $curlOptions = [];

    /**
     * Error strings if json is invalid.
     */
    private static $jsonErrors = [
        JSON_ERROR_NONE => 'No error has occurred',
        JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
        JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
        JSON_ERROR_SYNTAX => 'Syntax error',
    ];

    private $classes = [
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
        'search' => 'Search',
    ];

    /**
     * Usage: apikeyOrUsername can be auth key or username.
     * Password needs to be set if username is given.
     *
     * @param string      $url
     * @param string      $apikeyOrUsername
     * @param string|null $pass
     */
    public function __construct($url, $apikeyOrUsername, $pass = null)
    {
        $this->url = $url;
        $this->getPort();
        $this->apikeyOrUsername = $apikeyOrUsername;
        $this->pass = $pass;
    }

    /**
     * PHP getter magic method.
     *
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return Api\AbstractApi
     */
    public function __get($name)
    {
        return $this->api($name);
    }

    /**
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return Api\AbstractApi
     */
    public function api($name)
    {
        if (!isset($this->classes[$name])) {
            throw new \InvalidArgumentException();
        }
        if (isset($this->apis[$name])) {
            return $this->apis[$name];
        }
        $class = 'Redmine\Api\\'.$this->classes[$name];
        $this->apis[$name] = new $class($this);

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
     * @return array|string|false
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
     * Returns $json if no error occurred during decoding but decoded value is
     * null.
     *
     * @param string $json
     *
     * @return array|string
     */
    public function decode($json)
    {
        if (empty($json)) {
            return '';
        }
        $decoded = json_decode($json, true);
        if (null !== $decoded) {
            return $decoded;
        }
        if (JSON_ERROR_NONE === json_last_error()) {
            return $json;
        }

        return self::$jsonErrors[json_last_error()];
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
     * @return false|\SimpleXMLElement|string
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
     * @return Client
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
     * @return Client
     */
    public function setCheckSslHost($check = false)
    {
        // Make sure verify value is set to "2" for boolean argument
        // @see http://curl.haxx.se/libcurl/c/CURLOPT_SSL_VERIFYHOST.html
        if (true === $check) {
            $check = self::SSL_VERIFYHOST;
        }
        $this->checkSslHost = $check;

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
     *
     * @see http://curl.haxx.se/libcurl/c/CURLOPT_SSLVERSION.html
     *
     * @param int $sslVersion
     *
     * @return Client
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
     * @return Client
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
     * @return Client
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
     * @param string|null $customHost
     *
     * @return Client
     */
    public function setCustomHost($customHost = null)
    {
        $this->customHost = $customHost;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCustomHost()
    {
        return $this->customHost;
    }

    /**
     * Set a cURL option.
     *
     * @param int   $option The CURLOPT_XXX option to set
     * @param mixed $value  The value to be set on option
     *
     * @return Client
     */
    public function setCurlOption($option, $value)
    {
        $this->curlOptions[$option] = $value;

        return $this;
    }

     /**
      * Unset a cURL option.
      *
      * @param int   $option The CURLOPT_XXX option to unset
      *
      * @return Client
      */
     public function unsetCurlOption($option)
     {
         unset($this->curlOptions[$option]);

         return $this;
     }

    /**
     * Get all set cURL options.
     *
     * @return array
     */
    public function getCurlOptions()
    {
        return $this->curlOptions;
    }

    /**
     * Prepare the request by setting the cURL options.
     *
     * @param string $path
     * @param string $method
     * @param string $data
     *
     * @return resource a cURL handle on success, <b>FALSE</b> on errors
     */
    public function prepareRequest($path, $method = 'GET', $data = '')
    {
        $this->responseCode = null;
        $curl = curl_init();

        // General cURL options
        $this->setCurlOption(CURLOPT_VERBOSE, 0);
        $this->setCurlOption(CURLOPT_HEADER, 0);
        $this->setCurlOption(CURLOPT_RETURNTRANSFER, 1);

        // HTTP Basic Authentication
        if ($this->apikeyOrUsername && $this->useHttpAuth) {
            if (null === $this->pass) {
                $this->setCurlOption(CURLOPT_USERPWD, $this->apikeyOrUsername.':'.rand(100000, 199999));
            } else {
                $this->setCurlOption(CURLOPT_USERPWD, $this->apikeyOrUsername.':'.$this->pass);
            }
            $this->setCurlOption(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        }

        // Host and request options
        $this->setCurlOption(CURLOPT_URL, $this->url.$path);
        $this->setCurlOption(CURLOPT_PORT, $this->getPort());
        if (80 !== $this->getPort()) {
            $this->setCurlOption(CURLOPT_SSL_VERIFYPEER, (int) $this->checkSslCertificate);
            $this->setCurlOption(CURLOPT_SSL_VERIFYHOST, (int) $this->checkSslHost);
            $this->setCurlOption(CURLOPT_SSLVERSION, $this->sslVersion);
        }

        // Set the HTTP request headers
        $httpHeader = $this->setHttpHeader($path);
        $this->setCurlOption(CURLOPT_HTTPHEADER, $httpHeader);

        $this->unsetCurlOption(CURLOPT_CUSTOMREQUEST);
        $this->unsetCurlOption(CURLOPT_POST);
        $this->unsetCurlOption(CURLOPT_POSTFIELDS);
        switch ($method) {
            case 'POST':
                $this->setCurlOption(CURLOPT_POST, 1);
                if (isset($data)) {
                    $this->setCurlOption(CURLOPT_POSTFIELDS, $data);
                }
                break;
            case 'PUT':
                $this->setCurlOption(CURLOPT_CUSTOMREQUEST, 'PUT');
                if (isset($data)) {
                    $this->setCurlOption(CURLOPT_POSTFIELDS, $data);
                }
                break;
            case 'DELETE':
                $this->setCurlOption(CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            default: // GET
                break;
        }

        // Set all cURL options to the current cURL resource
        curl_setopt_array($curl, $this->getCurlOptions());

        return $curl;
    }

    private function setHttpHeader($path)
    {
        // Additional request headers
        $httpHeader = [
            'Expect: ',
        ];

        // Content type headers
        $tmp = parse_url($this->url.$path);
        if ('/uploads.json' === $path || '/uploads.xml' === $path) {
            $httpHeader[] = 'Content-Type: application/octet-stream';
        } elseif ('json' === substr($tmp['path'], -4)) {
            $httpHeader[] = 'Content-Type: application/json';
        } elseif ('xml' === substr($tmp['path'], -3)) {
            $httpHeader[] = 'Content-Type: text/xml';
        }

        if ($this->customHost !== null) {
            $httpHeader[] = 'Host: '.$this->customHost;
        }

        // Redmine specific headers
        if ($this->impersonateUser !== null) {
            $httpHeader[] = 'X-Redmine-Switch-User: '.$this->impersonateUser;
        }
        if (null === $this->pass) {
            $httpHeader[] = 'X-Redmine-API-Key: '.$this->apikeyOrUsername;
        }

        return $httpHeader;
    }

    /**
     * Process the cURL response.
     *
     * @param string $response
     * @param string $contentType
     *
     * @throws \Exception If anything goes wrong on curl request
     *
     * @return false|\SimpleXMLElement|string
     */
    public function processCurlResponse($response, $contentType)
    {
        if ($response) {
            // if response is XML, return an SimpleXMLElement object
            if (0 === strpos($contentType, 'application/xml')) {
                return new \SimpleXMLElement($response);
            }

            return $response;
        }

        return false;
    }

    /**
     * @codeCoverageIgnore Ignore due to untestable curl_* function calls.
     *
     * @param string $path
     * @param string $method
     * @param string $data
     *
     * @throws \Exception If anything goes wrong on curl request
     *
     * @return false|\SimpleXMLElement|string
     */
    protected function runRequest($path, $method = 'GET', $data = '')
    {
        $curl = $this->prepareRequest($path, $method, $data);

        $response = curl_exec($curl);
        $this->responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);

        if (curl_errno($curl)) {
            $e = new \Exception(curl_error($curl), curl_errno($curl));
            curl_close($curl);
            throw $e;
        }
        curl_close($curl);

        return $this->processCurlResponse($response, $contentType);
    }
}
