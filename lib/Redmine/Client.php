<?php

namespace Redmine;

use Redmine\Api\SimpleXMLElement;

/**
 * Simple PHP Redmine client.
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 * Website: http://github.com/kbsali/php-redmine-api
 */
class Client extends AbstractClient
{

    /**
     * Value for CURLOPT_SSL_VERIFYHOST
     *
     * @see http://curl.haxx.se/libcurl/c/CURLOPT_SSL_VERIFYHOST.html
     */
    const SSL_VERIFYHOST = 2;

    /**
     * @var array cUrl options
     */
    private $curlOptions = array();

    /**
     * {@inheritdocs}
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
            $this->setCurlOption(CURLOPT_SSL_VERIFYPEER, $this->checkSslCertificate);
            $this->setCurlOption(CURLOPT_SSL_VERIFYHOST, $this->checkSslHost);
            $this->setCurlOption(CURLOPT_SSLVERSION, $this->sslVersion);
        }

        // Set the HTTP request headers
        $this->setCurlOption(CURLOPT_HTTPHEADER, $this->generateHttpHeader($path));

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

        return $this->processResponse($response, $contentType);
    }
}