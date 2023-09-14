# Migrate from `Redmine\Client` to `Redmine\Client\Psr18Client`

Since `v1.7.0` there is a new PSR-18 based client `Redmine\Client\Psr18Client`. This guide will help you to migrate your code if you want to use an app-wide PSR-18 HTTP client.

## 1. Use new client methods

With the new interface `Redmine\Client\Client` there are now standarized methods for all clients. The new `Redmine\Client\Psr18Client` and the current `Redmine\Client` implementing this interface.

### `api()` -> `getApi()`

Search in your code for the usage of `$client->api('issue')` and the magic getter like `$client->issue`. Then replace this calls with `$client->getApi('issue')`.

```diff
-$issue = $client->issue->show($issueId);
+$issue = $client->getApi('issue')->show($issueId);

-$client->api('issue')->create($data);
+$client->getApi('issue')->create($data);
```

### `getResponseCode()` -> `getLastResponseStatusCode()`

Replace every call for `$client->getResponseCode()` with `$client->getLastResponseStatusCode()`.

```diff
-if ($client->getResponseCode() === 500)
+if ($client->getLastResponseStatusCode() === 500)
{
    throw new \Exception('Redmine call failed');
}
```

### `get()` -> `requestGet()`

If you are using `$client->get()`, `$client->post()`, `$client->put()` or `$client->delete()` directly you will have to change your code. This methods parse a possible JSON or XML response but in future the parsing of the raw response body will be up to you.

To help you with the parsing of the raw response the client interface introduces two new methods: `getLastResponseContentType()` and `getLastResponseBody()`.

This example shows how you can parse the response body of a GET request.

```diff
-// We dont know if we will get json, xml or a string
-$dataAsJsonOrXmlOrString = $this->client->get($path);
+$this->client->requestGet($path);
+// $body contains the raw http body of the response
+$body = $this->client->getLastResponseBody();
+
+// if response is XML, create a SimpleXMLElement object
+if ($body !== '' && 0 === strpos($this->client->getLastResponseContentType(), 'application/xml')) {
+    $dataAsXML = new \SimpleXMLElement($body);
+} else if ($body !== '' && 0 === strpos($this->client->getLastResponseContentType(), 'application/json')) {
+    try {
+        $dataAsJson = json_decode($body, true, 512, \JSON_THROW_ON_ERROR);
+    } catch (\JsonException $e) {
+        throw new \Exception('Error decoding body as JSON: '.$e->getMessage());
+    }
+} else {
+    $dataAsString = $body;
+}
```

### `post()` -> `requestPost()`

This example shows how you can parse the response body of a POST request.

```diff
-// We dont know if we will get xml or a string
-$dataAsXmlOrString = $this->client->post($path, $data);
+$this->client->requestPost($path, $data);
+// $body contains the raw http body of the response
+$body = $this->client->getLastResponseBody();
+
+// if response is XML, create a SimpleXMLElement object
+if ($body !== '' && 0 === strpos($this->client->getLastResponseContentType(), 'application/xml')) {
+    $dataAsXML = new \SimpleXMLElement($body);
+} else {
+    $dataAsString = $body;
+}
```

### `put()` -> `requestPut()`

This example shows how you can parse the response body of a PUT request.

```diff
-// We dont know if we will get xml or a string
-$dataAsXmlOrString = $this->client->put($path, $data);
+$this->client->requestPut($path, $data);
+// $body contains the raw http body of the response
+$body = $this->client->getLastResponseBody();
+
+// if response is XML, create a SimpleXMLElement object
+if ($body !== '' && 0 === strpos($this->client->getLastResponseContentType(), 'application/xml')) {
+    $dataAsXML = new \SimpleXMLElement($body);
+} else {
+    $dataAsString = $body;
+}
```

### `delete()` -> `requestDelete()`

This example shows how you can parse the response body of a DELETE request.

```diff
-$dataAsString = $this->client->delete($path);
+$this->client->requestDelte($path);
+$dataAsString = $this->client->getLastResponseBody();
```

### `setImpersonateUser()` -> `startImpersonateUser()`

If you are using the [Redmine user impersonation](https://www.redmine.org/projects/redmine/wiki/Rest_api#User-Impersonation) you have to change your code.

```diff
// impersonate the user `robin`
-$client->setImpersonateUser('robin');
+$client->startImpersonateUser('robin');

$userData = $client->getApi('user')->getCurrentUser();

// Now stop impersonation
-$client->setImpersonateUser(null);
+$client->stopImpersonateUser();
```

After this changes you should be able to test your code without errors.

## 2. Switch to `Psr18Client`

The `Redmine\Client\Psr18Client` requires:

- a `Psr\Http\Client\ClientInterface` implementation (like guzzlehttp/guzzle), [see packagist.org](https://packagist.org/providers/psr/http-client-implementation)
- a `Psr\Http\Message\RequestFactoryInterface` implementation (like nyholm/psr7), [see packagist.org](https://packagist.org/providers/psr/http-factory-implementation)
- a `Psr\Http\Message\StreamFactoryInterface` implementation (like nyholm/psr7), [see packagist.org](https://packagist.org/providers/psr/http-message-implementation)
- a URL to your Redmine instance
- an Apikey or username
- and optional a password if you want to use username/password (not recommended).

```diff
+$guzzle = new \GuzzleHttp\Client();
+$psr17Factory = new \GuzzleHttp\Psr7\HttpFactory();
+
// Instantiate with ApiKey
-$client = new \Redmine\Client(
+$client = new \Redmine\Client\Psr18Client(
+    $guzzle,
+    $psr17Factory,
+    $psr17Factory,
    'https://redmine.example.com',
    '1234567890abcdfgh'
);
```

If you want more control over the PSR-17 RequestFactory you can also create a anonymous class:

```diff
+use Psr\Http\Message\RequestFactoryInterface;
+use Psr\Http\Message\RequestInterface;
+use Psr\Http\Message\StreamFactoryInterface;
+use Psr\Http\Message\StreamInterface;
+
$guzzle = new \GuzzleHttp\Client();
-$psr17Factory = new \GuzzleHttp\Psr7\HttpFactory();
+$psr17Factory = new class() implements RequestFactoryInterface, StreamFactoryInterface {
+    public function createRequest(string $method, $uri): RequestInterface
+    {
+        return new \GuzzleHttp\Psr7\Request($method, $uri);
+    }
+
+    public function createStream(string $content = ''): StreamInterface
+    {
+        return \GuzzleHttp\Psr7\Utils::streamFor($content);
+    }
+
+    public function createStreamFromFile(string $file, string $mode = 'r'): StreamInterface
+    {
+        return \GuzzleHttp\Psr7\Utils::streamFor(\GuzzleHttp\Psr7\Utils::tryFopen($file, $mode));
+    }
+
+    public function createStreamFromResource($resource): StreamInterface
+    {
+        return \GuzzleHttp\Psr7\Utils::streamFor($resource);
+    }
+};

// Instantiate with ApiKey
$client = new \Redmine\Client\Psr18Client(
    $guzzle,
    $psr17Factory,
    $psr17Factory,
    'https://redmine.example.com',
    '1234567890abcdfgh'
);
```

## 3. Set `cURL` options

If you have set custom `cURL` options you now have to set them to `Guzzle`. Thanks to the HTTP client you can set them to every request:

```diff
$guzzle = new \GuzzleHttp\Client();
$psr17Factory = new \GuzzleHttp\Psr7\HttpFactory();

+$guzzleWrapper = new class(\GuzzleHttp\Client $guzzle) implements \Psr\Http\Client\ClientInterface
+{
+    private $guzzle;
+
+    public function __construct(\GuzzleHttp\Client $guzzle)
+    {
+        $this->guzzle = $guzzle;
+    }
+
+    public function sendRequest(\Psr\Http\Message\RequestInterface $request): \Psr\Http\Message\ResponseInterface
+    {
+        return $this->guzzle->send($request, [
+            // Set other the options for every request here
+            'auth' => ['username', 'password', 'digest'],
+            'cert' => ['/path/server.pem', 'password'],
+            'connect_timeout' => 3.14,
+            // Set specific CURL options, see https://docs.guzzlephp.org/en/stable/faq.html#how-can-i-add-custom-curl-options
+            'curl' => [
+                CURLOPT_SSL_VERIFYPEER => 1,
+                CURLOPT_SSL_VERIFYHOST => 2,
+                CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
+            ],
+        ]);
+    }
+};
+
// Instantiate with ApiKey
$client = new \Redmine\Client\Psr18Client(
-    $guzzle,
+    $guzzleWrapper,
    $psr17Factory,
    $psr17Factory,
    'https://redmine.example.com',
    '1234567890abcdfgh'
);
-
-$client->setCheckSslCertificate(true);
-$client->setCheckSslHost(true);
-$client->setSslVersion(CURL_SSLVERSION_TLSv1_3);
```

If you don't want `php-redmine-api` to use HTTP auth, you can disable it by removing the headers from the request.

```diff
$guzzle = new \GuzzleHttp\Client();
$psr17Factory = new \GuzzleHttp\Psr7\HttpFactory();

$guzzleWrapper = new class(\GuzzleHttp\Client $guzzle) implements ClientInterface
{
    private $guzzle;

    public function __construct(\GuzzleHttp\Client $guzzle)
    {
        $this->guzzle = $guzzle;
    }

    public function sendRequest(\Psr\Http\Message\RequestInterface $request): \Psr\Http\Message\ResponseInterface
    {
+        // Remove the auth headers
+        $request = $request->withoutHeader('X-Redmine-API-Key');
+        $request = $request->withoutHeader('Authorization');
+
        return $this->guzzle->send($request, [
            // Set other the options for every request here
            'auth' => ['username', 'password', 'digest'],
            'cert' => ['/path/server.pem', 'password'],
            'connect_timeout' => 3.14,
            // Set specific CURL options, see https://docs.guzzlephp.org/en/stable/faq.html#how-can-i-add-custom-curl-options
            'curl' => [
                CURLOPT_SSL_VERIFYPEER => 1,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
            ],
        ]);
    }
};

// Instantiate with ApiKey
$client = new \Redmine\Client\Psr18Client(
    $guzzleWrapper,
    $psr17Factory,
    $psr17Factory,
    'https://redmine.example.com',
    '1234567890abcdfgh'
);
-
-$client->setUseHttpAuth(false);
```

Now you should be ready. Please make sure that you are only using client methods that are defined in `Redmine\Client\Client` because all other methods will be removed or set to private in a future major release. Otherwise you will have to change your code in future again.
