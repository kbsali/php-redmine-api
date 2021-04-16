<?php

namespace Redmine;

use Redmine\Client\Client as ClientInterface;
use Redmine\Client\ClientApiTrait;

@trigger_error(
    sprintf(
        'The "%s" class is deprecated, use "%s" or "%s" instead.',
        'Redmine\Client',
        'Redmine\Client\NativeCurlClient',
        'Redmine\Client\Psr18Client'
    ),
    E_USER_DEPRECATED
);

/**
 * Simple PHP Redmine client.
 *
 * @deprecated `Redmine\Client` is deprecated, use `Redmine\Client\NativeCurlClient` or `Redmine\Client\Psr18Client` instead
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 * Website: http://github.com/kbsali/php-redmine-api
 *
 * @property Api\Attachment        $attachment
 * @property Api\Group             $group
 * @property Api\CustomField       $custom_fields
 * @property Api\Issue             $issue
 * @property Api\IssueCategory     $issue_category
 * @property Api\IssuePriority     $issue_priority
 * @property Api\IssueRelation     $issue_relation
 * @property Api\IssueStatus       $issue_status
 * @property Api\Membership        $membership
 * @property Api\News              $news
 * @property Api\Project           $project
 * @property Api\Query             $query
 * @property Api\Role              $role
 * @property Api\TimeEntry         $time_entry
 * @property Api\TimeEntryActivity $time_entry_activity
 * @property Api\Tracker           $tracker
 * @property Api\User              $user
 * @property Api\Version           $version
 * @property Api\Wiki              $wiki
 */
class Client implements ClientInterface
{
    use ClientApiTrait;

    /**
     * Value for CURLOPT_SSL_VERIFYHOST.
     *
     * @see http://curl.haxx.se/libcurl/c/CURLOPT_SSL_VERIFYHOST.html
     */
    const SSL_VERIFYHOST = 2;

    private static array $defaultPorts = [
        'http' => 80,
        'https' => 443,
    ];

    private ?int $port = null;
    private string $url;
    private string $apikeyOrUsername;
    private ?string $pass;
    private bool $checkSslCertificate = false;
    private bool $checkSslHost = false;
    private int $sslVersion = 0;
    private bool $useHttpAuth = true;
    private int $responseCode = 0;
    private string $responseContentType = '';
    private string $responseBody = '';
    private array $curlOptions = [];

    /**
     * @var string|null username for impersonating API calls
     */
    protected $impersonateUser = null;

    /**
     * @var string|null customHost
     */
    protected $customHost = null;

    /**
     * Error strings if json is invalid.
     */
    private static array $jsonErrors = [
        JSON_ERROR_NONE => 'No error has occurred',
        JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
        JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
        JSON_ERROR_SYNTAX => 'Syntax error',
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
        $this->apikeyOrUsername = strval($apikeyOrUsername);
        $this->pass = $pass;
    }

    /**
     * PHP getter magic method.
     *
     * @deprecated use getApi() instead
     *
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return Api\AbstractApi
     */
    public function __get($name)
    {
        @trigger_error('The '.__METHOD__.' method is deprecated, use getApi() instead.', E_USER_DEPRECATED);

        return $this->getApi(strval($name));
    }

    /**
     * @param string $name
     *
     * @deprecated use getApi() instead
     *
     * @throws \InvalidArgumentException
     *
     * @return Api\AbstractApi
     */
    public function api($name)
    {
        @trigger_error('The '.__METHOD__.' method is deprecated, use getApi() instead.', E_USER_DEPRECATED);

        return $this->getApi(strval($name));
    }

    /**
     * Returns Url.
     *
     * @deprecated
     *
     * @return string
     */
    public function getUrl()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated. You should stop using it, as it will be removed in the future.', E_USER_DEPRECATED);

        return $this->url;
    }

    /**
     * Create and send a GET request.
     */
    public function requestGet(string $path): bool
    {
        $result = $this->get($path, true);

        return (false === $result) ? false : true;
    }

    /**
     * Create and send a POST request.
     */
    public function requestPost(string $path, string $body): bool
    {
        $result = $this->post($path, $body);

        return (false === $result) ? false : true;
    }

    /**
     * Create and send a PUT request.
     */
    public function requestPut(string $path, string $body): bool
    {
        $result = $this->put($path, $body);

        return (false === $result) ? false : true;
    }

    /**
     * Create and send a DELETE request.
     */
    public function requestDelete(string $path): bool
    {
        $result = $this->delete($path);

        return (false === $result) ? false : true;
    }

    /**
     * Returns status code of the last response.
     */
    public function getLastResponseStatusCode(): int
    {
        return (int) $this->responseCode;
    }

    /**
     * Returns content type of the last response.
     */
    public function getLastResponseContentType(): string
    {
        return (string) $this->responseContentType;
    }

    /**
     * Returns the body of the last response.
     */
    public function getLastResponseBody(): string
    {
        return (string) $this->responseBody;
    }

    /**
     * HTTP GETs a json $path and tries to decode it.
     *
     * @deprecated use requestGet() instead
     *
     * @param string $path
     * @param bool   $decode
     *
     * @return array|string|false
     */
    public function get($path, $decode = true)
    {
        @trigger_error('The '.__METHOD__.' method is deprecated, use requestGet() instead.', E_USER_DEPRECATED);

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
     * @deprecated
     *
     * Returns $json if no error occurred during decoding but decoded value is
     * null
     *
     * @param string $json
     *
     * @return array|string
     */
    public function decode($json)
    {
        @trigger_error('The '.__METHOD__.' method is deprecated. You should stop using it, as it will be removed in the future.', E_USER_DEPRECATED);

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
     * @deprecated use requestPost() instead
     *
     * @param string $path
     * @param string $data
     *
     * @return mixed
     */
    public function post($path, $data)
    {
        @trigger_error('The '.__METHOD__.' method is deprecated, use requestPost() instead.', E_USER_DEPRECATED);

        return $this->runRequest($path, 'POST', $data);
    }

    /**
     * HTTP PUTs $params to $path.
     *
     * @deprecated use requestPut() instead
     *
     * @param string $path
     * @param string $data
     *
     * @return array
     */
    public function put($path, $data)
    {
        @trigger_error('The '.__METHOD__.' method is deprecated, use requestPut() instead.', E_USER_DEPRECATED);

        return $this->runRequest($path, 'PUT', $data);
    }

    /**
     * HTTP PUTs $params to $path.
     *
     * @deprecated use requestDelete() instead
     *
     * @param string $path
     *
     * @return false|\SimpleXMLElement|string
     */
    public function delete($path)
    {
        @trigger_error('The '.__METHOD__.' method is deprecated, use requestDelete() instead.', E_USER_DEPRECATED);

        return $this->runRequest($path, 'DELETE');
    }

    /**
     * Turns on/off ssl certificate check.
     *
     * @deprecated use setCurlOption() instead
     *
     * @param bool $check
     *
     * @return Client
     */
    public function setCheckSslCertificate($check = false)
    {
        @trigger_error('The '.__METHOD__.' method is deprecated, use setCurlOption() instead.', E_USER_DEPRECATED);

        $this->checkSslCertificate = $check;

        return $this;
    }

    /**
     * Get the on/off flag for ssl certificate check.
     *
     * @deprecated
     *
     * @return bool
     */
    public function getCheckSslCertificate()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated. You should stop using it, as it will be removed in the future.', E_USER_DEPRECATED);

        return $this->checkSslCertificate;
    }

    /**
     * Turns on/off ssl host certificate check.
     *
     * @deprecated use setCurlOption() instead
     *
     * @param bool $check
     *
     * @return Client
     */
    public function setCheckSslHost($check = false)
    {
        @trigger_error('The '.__METHOD__.' method is deprecated, use setCurlOption() instead.', E_USER_DEPRECATED);

        $this->checkSslHost = (bool) $check;

        return $this;
    }

    /**
     * Get the on/off flag for ssl host certificate check.
     *
     * @deprecated
     *
     * @return bool
     */
    public function getCheckSslHost()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated. You should stop using it, as it will be removed in the future.', E_USER_DEPRECATED);

        return $this->checkSslHost;
    }

    /**
     * Forces the SSL/TLS version to use.
     *
     * @deprecated use setCurlOption() instead
     * @see http://curl.haxx.se/libcurl/c/CURLOPT_SSLVERSION.html
     *
     * @param int $sslVersion
     *
     * @return Client
     */
    public function setSslVersion($sslVersion = 0)
    {
        @trigger_error('The '.__METHOD__.' method is deprecated, use setCurlOption() instead.', E_USER_DEPRECATED);

        $this->sslVersion = $sslVersion;

        return $this;
    }

    /**
     * Returns the SSL Version used.
     *
     * @deprecated
     *
     * @return int
     */
    public function getSslVersion()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated. You should stop using it, as it will be removed in the future.', E_USER_DEPRECATED);

        return $this->sslVersion;
    }

    /**
     * Turns on/off http auth.
     *
     * @deprecated use setCurlOption() instead
     *
     * @param bool $use
     *
     * @return Client
     */
    public function setUseHttpAuth($use = true)
    {
        @trigger_error('The '.__METHOD__.' method is deprecated, use setCurlOption() instead.', E_USER_DEPRECATED);

        $this->useHttpAuth = $use;

        return $this;
    }

    /**
     * Get the on/off flag for http auth.
     *
     * @deprecated
     *
     * @return bool
     */
    public function getUseHttpAuth()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated. You should stop using it, as it will be removed in the future.', E_USER_DEPRECATED);

        return $this->useHttpAuth;
    }

    /**
     * Set the port of the connection.
     *
     * @deprecated use setCurlOption() instead
     *
     * @param int $port
     *
     * @return Client
     */
    public function setPort($port = null)
    {
        @trigger_error('The '.__METHOD__.' method is deprecated, use setCurlOption() instead.', E_USER_DEPRECATED);

        if (null !== $port) {
            $this->port = (int) $port;
        }

        return $this;
    }

    /**
     * Returns Redmine response code.
     *
     * @deprecated use getLastResponseStatusCode() instead
     *
     * @return int
     */
    public function getResponseCode()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated, use getLastResponseStatusCode() instead.', E_USER_DEPRECATED);

        return (int) $this->getLastResponseStatusCode();
    }

    /**
     * Returns the port of the current connection,
     * if not set, it will try to guess the port
     * from the url of the client.
     *
     * @deprecated
     *
     * @return int the port number
     */
    public function getPort()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated. You should stop using it, as it will be removed in the future.', E_USER_DEPRECATED);

        if (null !== $this->port) {
            return $this->port;
        }

        $tmp = parse_url($this->url);
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
     */
    public function startImpersonateUser(string $username): void
    {
        $this->impersonateUser = $username;
    }

    /**
     * Remove the user impersonate.
     */
    public function stopImpersonateUser(): void
    {
        $this->impersonateUser = null;
    }

    /**
     * Sets to an existing username so api calls can be
     * impersonated to this user.
     *
     * @deprecated use startImpersonateUser() and stopImpersonateUser() instead
     *
     * @param string|null $username
     *
     * @return Client
     */
    public function setImpersonateUser($username = null)
    {
        if (null === $username) {
            @trigger_error('The '.__METHOD__.' method is deprecated, use stopImpersonateUser() instead.', E_USER_DEPRECATED);

            $this->stopImpersonateUser();
        } else {
            @trigger_error('The '.__METHOD__.' method is deprecated, use startImpersonateUser() instead.', E_USER_DEPRECATED);

            $this->startImpersonateUser($username);
        }

        return $this;
    }

    /**
     * Get the impersonate user.
     *
     * @deprecated
     *
     * @return string|null
     */
    public function getImpersonateUser()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated. You should stop using it, as it will be removed in the future.', E_USER_DEPRECATED);

        return $this->impersonateUser;
    }

    /**
     * @param string|null $customHost
     *
     * @deprecated
     *
     * @return Client
     */
    public function setCustomHost($customHost = null)
    {
        @trigger_error('The '.__METHOD__.' method is deprecated, use setCurlOption() instead.', E_USER_DEPRECATED);

        $this->customHost = $customHost;

        return $this;
    }

    /**
     * @return string|null
     *
     * @deprecated
     */
    public function getCustomHost()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated. You should stop using it, as it will be removed in the future.', E_USER_DEPRECATED);

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
     * @param int $option The CURLOPT_XXX option to unset
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
     * @deprecated
     *
     * @return array
     */
    public function getCurlOptions()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated. You should stop using it, as it will be removed in the future.', E_USER_DEPRECATED);

        return $this->curlOptions;
    }

    /**
     * Prepare the request by setting the cURL options.
     *
     * @deprecated
     *
     * @param string $path
     * @param string $method
     * @param string $data
     *
     * @return resource a cURL handle on success, <b>FALSE</b> on errors
     */
    public function prepareRequest($path, $method = 'GET', $data = '')
    {
        @trigger_error('The '.__METHOD__.' method is deprecated. You should stop using it, as it will be removed in the future.', E_USER_DEPRECATED);

        $this->responseCode = 0;
        $this->responseContentType = '';
        $this->responseBody = '';
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
            // Make sure verify value is set to "2" for boolean argument
            // @see http://curl.haxx.se/libcurl/c/CURLOPT_SSL_VERIFYHOST.html
            $this->setCurlOption(CURLOPT_SSL_VERIFYHOST, (true === $this->checkSslHost) ? self::SSL_VERIFYHOST : 0);
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
                if ($this->isUploadCall($path, $data)) {
                    $this->prepareUploadRequest($data);
                } elseif (isset($data)) {
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
        curl_setopt_array($curl, $this->curlOptions);

        return $curl;
    }

    private function isUploadCall($path, $data)
    {
        return
            (preg_match('/\/uploads.(json|xml)/i', $path)) &&
            isset($data) &&
            is_file(strval(str_replace("\0", '', $data)))
        ;
    }

    private function prepareUploadRequest($data)
    {
        $file = fopen($data, 'r');
        $size = filesize($data);
        $filedata = fread($file, $size);

        $this->setCurlOption(CURLOPT_POSTFIELDS, $filedata);
        $this->setCurlOption(CURLOPT_INFILE, $file);
        $this->setCurlOption(CURLOPT_INFILESIZE, $size);
    }

    private function setHttpHeader($path)
    {
        // Additional request headers
        $httpHeader = [
            'Expect: ',
        ];

        // Content type headers
        $tmp = parse_url($this->url.$path);
        if (preg_match('/\/uploads.(json|xml)/i', $path)) {
            $httpHeader[] = 'Content-Type: application/octet-stream';
        } elseif ('json' === substr($tmp['path'], -4)) {
            $httpHeader[] = 'Content-Type: application/json';
        } elseif ('xml' === substr($tmp['path'], -3)) {
            $httpHeader[] = 'Content-Type: text/xml';
        }

        if (null !== $this->customHost) {
            $httpHeader[] = 'Host: '.$this->customHost;
        }

        // Redmine specific headers
        if (null !== $this->impersonateUser) {
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
     * @deprecated
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
        @trigger_error('The '.__METHOD__.' method is deprecated. You should stop using it, as it will be removed in the future.', E_USER_DEPRECATED);

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
     * @deprecated
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
        @trigger_error('The '.__METHOD__.' method is deprecated. You should stop using it, as it will be removed in the future.', E_USER_DEPRECATED);

        $curl = $this->prepareRequest($path, $method, $data);

        // use HTTP 1.1
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        $response = curl_exec($curl);
        $this->responseBody = (false === $response) ? '' : $response;
        $this->responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $this->responseContentType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);

        if (curl_errno($curl)) {
            $e = new \Exception(curl_error($curl), curl_errno($curl));
            curl_close($curl);
            throw $e;
        }
        curl_close($curl);

        return $this->processCurlResponse($response, $this->responseContentType);
    }
}
