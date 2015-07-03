<?php

namespace Redmine;

use Redmine\Api\SimpleXMLElement;

/**
 * Simple PHP Redmine client.
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 * Website: http://github.com/kbsali/php-redmine-api
 */
class Client
{
    
    /**
     * Value for CURLOPT_SSL_VERIFYHOST
     * 
     * @see http://curl.haxx.se/libcurl/c/CURLOPT_SSL_VERIFYHOST.html
     */
    const SSL_VERIFYHOST = 2;

    /**
     * @var array
     */
    private static $defaultPorts = array(
        'http' => 80,
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
     * @var bool
     */
    private $checkSslCertificate = false;

    /**
     * @var bool
     */
    private $checkSslHost = false;

    /**
     * @var bool Flag to determine authentication method
     */
    private $useHttpAuth = true;

    /**
     * @var array APIs
     */
    private $apis = array();

    /**
     * @var string|null username for impersonating API calls
     */
    protected $impersonateUser = null;

    /**
     * @var int|null Redmine response code, null if request is not still completed
     */
    private $responseCode = null;

    /**
     * @var array cURL options
     */
    private $curlOptions = array();

    /**
     * @var string username for HTTP authentication
     */
    private $httpAuthUsername = null;

    /**
     * @var string password for HTTP authentication
     */
    private $httpAuthPassword = null;

    /**
     * Error strings if json is invalid.
     */
    private static $json_errors = array(
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
     * Turns on/off http auth.
     *
     * @param bool $use
     * @param string|null $username A custom username for http authentication
     * @param string|null $password A custom password for http authentication
     * @return Client
     */
    public function setUseHttpAuth($use = true, $username = null, $password = null)
    {
        $this->useHttpAuth = $use;
        
        if ($use) {
            $this->httpAuthUsername = $username;
            $this->httpAuthPassword = $password;
        }

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
     * Set a cURL option.
     * 
     * @param int   $option The CURLOPT_XXX option to set
     * @param mixed $value The value to be set on option
     *
     * @return Client
     */
    public function setCurlOption($option, $value)
    {
        $this->curlOptions[$option] = $value;
        
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
     * @return resource a cURL handle on success, <b>FALSE</b> on errors.
     */
    public function prepareRequest($path, $method = 'GET', $data = '')
    {
        $this->responseCode = null;
        $this->curlOptions = array();
        $curl = curl_init();
        
        // General cURL options
        $this->setCurlOption(CURLOPT_VERBOSE, 0);
        $this->setCurlOption(CURLOPT_HEADER, 0);
        $this->setCurlOption(CURLOPT_RETURNTRANSFER, 1);

        // HTTP Basic Authentication
        if ($this->useHttpAuth) {
            if ($this->httpAuthUsername && $this->httpAuthPassword) {
                $this->setCurlOption(CURLOPT_USERPWD, $this->httpAuthUsername.':'.$this->httpAuthPassword);
            } else {
                if (null === $this->pass) {
                    $this->setCurlOption(CURLOPT_USERPWD, $this->apikeyOrUsername.':'.rand(100000, 199999));
                } else {
                    $this->setCurlOption(CURLOPT_USERPWD, $this->apikeyOrUsername.':'.$this->pass);
                }
            }
            $this->setCurlOption(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        }

        // Host and request options
        $this->setCurlOption(CURLOPT_URL, $this->url.$path);
        $this->setCurlOption(CURLOPT_PORT, $this->getPort());
        if (80 !== $this->getPort()) {
            $this->setCurlOption(CURLOPT_SSL_VERIFYPEER, $this->checkSslCertificate);
            $this->setCurlOption(CURLOPT_SSL_VERIFYHOST, $this->checkSslHost);
        }

        // Additional request headers
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

        // Set the HTTP request headers
        $this->setCurlOption(CURLOPT_HTTPHEADER, $httpHeader);

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

    /**
     * Process the cURL response.
     *
     * @param string $response
     * @param string $contentType
     *
     * @return bool|SimpleXMLElement|string
     *
     * @throws \Exception If anything goes wrong on curl request
     */
    public function processCurlResponse($response, $contentType)
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
     * @codeCoverageIgnore Ignore due to untestable curl_* function calls.
     * 
     * @param string $path
     * @param string $method
     * @param string $data
     *
     * @return bool|SimpleXMLElement|string
     *
     * @throws \Exception If anything goes wrong on curl request
     */
    protected function runRequest($path, $method = 'GET', $data = '')
    {
        $curl = $this->prepareRequest($path, $method, $data);

        $response = trim(curl_exec($curl));
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
