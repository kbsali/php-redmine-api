<?php

declare(strict_types=1);

namespace Redmine\Client;

use Exception;
use Redmine\Api;

/**
 * Native cURL client
 */
class NativeCurlClient implements Client
{
    use ClientApiTrait;

    /**
     * Value for CURLOPT_SSL_VERIFYHOST.
     *
     * @see http://curl.haxx.se/libcurl/c/CURLOPT_SSL_VERIFYHOST.html
     */
    const SSL_VERIFYHOST = 2;

    private string $url;
    private string $apikeyOrUsername;
    private ?string $password;
    private ?string $impersonateUser = null;
    private int $lastResponseStatusCode = 0;
    private string $lastResponseContentType = '';
    private string $lastResponseBody = '';
    private array $curlOptions = [];
    private ?int $port = null;
    private bool $checkSslCertificate = false;
    private bool $checkSslHost = false;
    private int $sslVersion = 0;
    private bool $useHttpAuth = true;

    private static array $defaultPorts = [
        'http' => 80,
        'https' => 443,
    ];

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
        $this->getPort();
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
     * @throws Exception If anything goes wrong on curl request
     */
    private function request(string $method, string $path, string $body = ''): bool
    {
        $this->lastResponseStatusCode = 0;
        $this->lastResponseContentType = '';
        $this->lastResponseBody = '';

        $curl = $this->createCurl($method, $path, $body);

        $response = curl_exec($curl);

        $this->lastResponseBody = ($response === false) ? '' : $response;
        $this->lastResponseStatusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $this->lastResponseContentType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);

        return false;

        if (curl_errno($curl)) {
            $e = new Exception(curl_error($curl), curl_errno($curl));
            curl_close($curl);
            throw $e;
        }
        curl_close($curl);

        return ($response !== false);
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
        $curl = curl_init();

        // General cURL options
        $this->setCurlOption(CURLOPT_VERBOSE, 0);
        $this->setCurlOption(CURLOPT_HEADER, 0);
        $this->setCurlOption(CURLOPT_RETURNTRANSFER, 1);
        // use HTTP 1.1
        $this->setCurlOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        // HTTP Basic Authentication
        if ($this->apikeyOrUsername && $this->useHttpAuth) {
            if (null === $this->password) {
                $this->setCurlOption(CURLOPT_USERPWD, $this->apikeyOrUsername.':199999');
            } else {
                $this->setCurlOption(CURLOPT_USERPWD, $this->apikeyOrUsername.':'.$this->password);
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
            $this->setCurlOption(CURLOPT_SSL_VERIFYHOST, ($this->checkSslHost === true) ? self::SSL_VERIFYHOST : 0);
            $this->setCurlOption(CURLOPT_SSLVERSION, $this->sslVersion);
        }

        // Set the HTTP request headers
        $this->setCurlOption(CURLOPT_HTTPHEADER, $this->createHttpHeader($path));

        $this->unsetCurlOption(CURLOPT_CUSTOMREQUEST);
        $this->unsetCurlOption(CURLOPT_POST);
        $this->unsetCurlOption(CURLOPT_POSTFIELDS);
        switch ($method) {
            case 'post':
                $this->setCurlOption(CURLOPT_POST, 1);
                if ($this->isUploadCall($path, $body)) {
                    $file = fopen($body, 'r');
                    $size = filesize($body);
                    $filedata = fread($file, $size);

                    $this->setCurlOption(CURLOPT_POSTFIELDS, $filedata);
                    $this->setCurlOption(CURLOPT_INFILE, $file);
                    $this->setCurlOption(CURLOPT_INFILESIZE, $size);
                } elseif (isset($body)) {
                    $this->setCurlOption(CURLOPT_POSTFIELDS, $body);
                }
                break;
            case 'put':
                $this->setCurlOption(CURLOPT_CUSTOMREQUEST, 'PUT');
                if (isset($body)) {
                    $this->setCurlOption(CURLOPT_POSTFIELDS, $body);
                }
                break;
            case 'delete':
                $this->setCurlOption(CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            default: // GET
                break;
        }

        // Set all cURL options to the current cURL resource
        curl_setopt_array($curl, $this->curlOptions);

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

    /**
     * Unset a cURL option.
     *
     * @param int $option The CURLOPT_XXX option to unset
     */
    private function unsetCurlOption(int $option): void
    {
        if (array_key_exists($option, $this->curlOptions)) {
            unset($this->curlOptions[$option]);
        }
    }

    /**
     * Returns the port of the current connection,
     * if not set, it will try to guess the port
     * from the url of the client.
     */
    private function getPort(): int
    {
        if (null !== $this->port) {
            return $this->port;
        }

        $tmp = parse_url($this->url);
        if (isset($tmp['port'])) {
            $this->setPort($tmp['port']);
        } elseif (isset($tmp['scheme'])) {
            $this->port = self::$defaultPorts[$tmp['scheme']];
        }

        return $this->port;
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
        if (null === $this->password) {
            $httpHeader[] = 'X-Redmine-API-Key: '.$this->apikeyOrUsername;
        }

        return $httpHeader;
    }
}
