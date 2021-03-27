<?php

declare(strict_types=1);

namespace Redmine\Client;

use Exception;
use Redmine\Api;

/**
 * Native cURL client
 */
final class NativeCurlClient implements Client
{
    use ClientApiTrait;

    private static array $defaultPorts = [
        'http' => 80,
        'https' => 443,
    ];

    private string $url;
    private string $apikeyOrUsername;
    private ?string $password;
    private ?string $impersonateUser = null;
    private int $lastResponseStatusCode = 0;
    private string $lastResponseContentType = '';
    private string $lastResponseBody = '';
    private array $curlOptions = [];
    private ?int $port = null;

    /**
     * @var string|null customHost
     */
    private $customHost = null;

    /**
     * $apikeyOrUsername should be your ApiKey, but it could also be your username.
     * $password needs to be set if a username is given (not recommended).
     */
    public function __construct(
        string $url,
        string $apikeyOrUsername,
        string $password = null
    ) {
        $this->url = $url;
        $this->apikeyOrUsername = $apikeyOrUsername;
        $this->password = $password;

        // get Port from url
        $defaultPorts = [
            'http' => 80,
            'https' => 443,
        ];

        $tmp = parse_url($this->url);

        if (isset($tmp['port'])) {
            $this->port = $tmp['port'];
        } elseif (isset($tmp['scheme']) && array_key_exists($tmp['scheme'], $defaultPorts)) {
            $this->port = $defaultPorts[$tmp['scheme']];
        } else {
            $this->port = $defaultPorts['http'];
        }
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
     * Create and send a GET request.
     */
    public function requestGet(string $path): bool
    {
        return $this->request('get', $path);
    }

    /**
     * Create and send a POST request.
     */
    public function requestPost(string $path, string $body): bool
    {
        return $this->request('post', $path, $body);
    }

    /**
     * Create and send a PUT request.
     */
    public function requestPut(string $path, string $body): bool
    {
        return $this->request('put', $path, $body);
    }

    /**
     * Create and send a DELETE request.
     */
    public function requestDelete(string $path): bool
    {
        return $this->request('delete', $path);
    }

    /**
    * Returns status code of the last response.
    */
    public function getLastResponseStatusCode(): int
    {
        return $this->lastResponseStatusCode;
    }

    /**
    * Returns content type of the last response.
    */
    public function getLastResponseContentType(): string
    {
        return $this->lastResponseContentType;
    }

    /**
     * Returns the body of the last response.
     */
    public function getLastResponseBody(): string
    {
        return $this->lastResponseBody;
    }

    /**
     * Set a cURL option.
     *
     * @param int   $option The CURLOPT_XXX option to set
     * @param mixed $value  The value to be set on option
     */
    public function setCurlOption(int $option, $value): void
    {
        $this->curlOptions[$option] = $value;
    }

    /**
     * Unset a cURL option.
     *
     * @param int $option The CURLOPT_XXX option to unset
     */
    public function unsetCurlOption(int $option): void
    {
        if (array_key_exists($option, $this->curlOptions)) {
            unset($this->curlOptions[$option]);
        }
    }

    /**
     * Get all permanent cURL options.
     *
     * @return array
     */
    public function getAllPermanentCurlOptions()
    {
        return $this->curlOptions;
    }

    /**
     * @throws Exception If anything goes wrong on curl request
     */
    private function request(string $method, string $path, string $body = ''): bool
    {
        $this->lastResponseStatusCode = 0;
        $this->lastResponseContentType = '';
        $this->lastResponseBody = '';

        $curl = $this->createCurl($method, $path, $body);

        $response = curl_exec($curl);

        $curlErrorNumber = curl_errno($curl);

        if ($curlErrorNumber !== CURLE_OK) {
            $e = new Exception(curl_error($curl), $curlErrorNumber);
            curl_close($curl);
            throw $e;
        }

        $this->lastResponseBody = ($response === false) ? '' : $response;
        $this->lastResponseStatusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $this->lastResponseContentType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);

        curl_close($curl);

        return ($this->lastResponseStatusCode < 400);
    }

    /**
     * Prepare the request by setting the cURL options.
     *
     * @param string $path
     * @param string $method
     * @param string $body
     *
     * @return resource a cURL handle on success, <b>FALSE</b> on errors
     */
    private function createCurl(string $method, string $path, string $body = '')
    {
        // General cURL options
        $curlOptions = [
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, // use HTTP 1.1
            CURLOPT_PORT => $this->port,
        ];

        // Merge custom curl options
        $curlOptions = array_replace($curlOptions, $this->curlOptions);

        // Host and request options
        $curlOptions[CURLOPT_URL] = $this->url.$path;

        // Set the HTTP request headers
        $curlOptions[CURLOPT_HTTPHEADER] = $this->createHttpHeader($path);

        unset($curlOptions[CURLOPT_CUSTOMREQUEST]);
        unset($curlOptions[CURLOPT_POST]);
        unset($curlOptions[CURLOPT_POSTFIELDS]);
        switch ($method) {
            case 'post':
                $curlOptions[CURLOPT_POST] = 1;
                if ($this->isUploadCall($path, $body)) {
                    $file = fopen($body, 'r');
                    $size = filesize($body);
                    $filedata = fread($file, $size);

                    $curlOptions[CURLOPT_POSTFIELDS] = $filedata;
                    $curlOptions[CURLOPT_INFILE] = $file;
                    $curlOptions[CURLOPT_INFILESIZE] = $size;
                } elseif (isset($body)) {
                    $curlOptions[CURLOPT_POSTFIELDS] = $body;
                }
                break;
            case 'put':
                $curlOptions[CURLOPT_CUSTOMREQUEST] = 'PUT';
                if (isset($body)) {
                    $curlOptions[CURLOPT_POSTFIELDS] = $body;
                }
                break;
            case 'delete':
                $curlOptions[CURLOPT_CUSTOMREQUEST] = 'DELETE';
                break;
            default: // GET
                break;
        }

        // Set or reset mandatory curl options
        $curlOptions = array_replace($curlOptions, [
            CURLOPT_VERBOSE => 0,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
        ]);

        $curl = curl_init();

        // Set all cURL options to the current cURL resource
        curl_setopt_array($curl, $curlOptions);

        return $curl;
    }

    private function isUploadCall(string $path, string $body): bool
    {
        return
            (preg_match('/\/uploads.(json|xml)/i', $path)) &&
            $body !== '' &&
            is_file(strval(str_replace("\0", '', $body)))
        ;
    }

    private function createHttpHeader($path): array
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
        // Set Authentication header
        // @see https://www.redmine.org/projects/redmine/wiki/Rest_api#Authentication
        if (null === $this->password) {
            $httpHeader[] = 'X-Redmine-API-Key: ' . $this->apikeyOrUsername;
        } else {
            // Setting Header "Authorization: Basic base64" is the same as
            // $this->setCurlOption(CURLOPT_USERPWD, "$username:$password")
            // @see https://stackoverflow.com/a/26285941
            $httpHeader[] = 'Authorization: Basic ' . base64_encode($this->apikeyOrUsername . ':' . $this->password);
        }

        return $httpHeader;
    }
}
