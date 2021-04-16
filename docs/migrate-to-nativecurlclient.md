# Migrating from `Redmine\Client` to `Redmine\Client\NativeCurlClient`

Since `v1.8.0`, there is a new native cURL client `Redmine\Client\NativeCurlClient`. This guide will help migrate your code to this client.

## 1. New client's methods

With the new interface `Redmine\Client\Client` there are now standardized methods for all clients. The new `Redmine\Client\NativeCurlClient` and the current `Redmine\Client` both implement this interface.

### `api()` -> `getApi()`

We got rid of `api()` method as well as the magic getters. So now to query the `issue` API for instance, use `$client->getApi('issue')` instead. See the following example :

```diff
-$issue = $client->issue->show($issueId);
+$issue = $client->getApi('issue')->show($issueId);

-$client->api('issue')->create($data);
+$client->getApi('issue')->create($data);
```

### `getResponseCode()` -> `getLastResponseStatusCode()`

Replace every calls for `$client->getResponseCode()` with `$client->getLastResponseStatusCode()`.

```diff
-if ($client->getResponseCode() === 500)
+if ($client->getLastResponseStatusCode() === 500)
{
    throw new \Exception('Redmine call failed');
}
```

### `get()` -> `requestGet()`

If you are using `$client->get()`, `$client->post()`, `$client->put()` or
`$client->delete()` directly you will have to change your code. This methods
parse a possible JSON or XML response but in future the parsing of the raw
response body will be up to you.

To help you with the parsing of the raw response the client interface introduces
two new methods: `getLastResponseContentType()` and `getLastResponseBody()`.

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
+$this->client->requestDelete($path);
+$dataAsString = $this->client->getLastResponseBody();
```

### `setImpersonateUser()` -> `startImpersonateUser()`

If you are using the [Redmine user impersonation](https://www.redmine.org/projects/redmine/wiki/Rest_api#User-Impersonation)
you have to change your code as follow :

```diff
// impersonate the user `robin`
-$client->setImpersonateUser('robin');
+$client->startImpersonateUser('robin');

$userData = $client->getApi('user')->getCurrentUser();

// Now stop impersonation
-$client->setImpersonateUser(null);
+$client->stopImpersonateUser();
```

### `setCheckSslCertificate()` -> `setCurlOption()`

```diff
-$client->setCheckSslCertificate(true);
+$client->setCurlOption(CURLOPT_SSL_VERIFYPEER, true);
```

### `setCheckSslHost()` -> `setCurlOption()`

```diff
-$client->setCheckSslHost(true);
+$client->setCurlOption(CURLOPT_SSL_VERIFYHOST, 2);
```

### `setSslVersion()` -> `setCurlOption()`

```diff
-$client->setSslVersion(true);
+$client->setCurlOption(CURLOPT_SSLVERSION, CURL_SSLVERSION_DEFAULT);
```

### `setUseHttpAuth()` can be removed

Authorization for Redmine uses always the http headers `X-Redmine-API-Key` or
`Authorization`. So the setting `setUseHttpAuth()` is not longer needed.

```diff
-$client->setUseHttpAuth(false);
```

If you want to override the `X-Redmine-API-Key` or `Authorization` header you
can set them via `setCurlOption()` method.

```diff
-$client->setUseHttpAuth(false);
+$client->setCurlOption(CURLOPT_HTTPHEADER, ['Authorization: Basic username_password_base64_encoded']);
// or
+$client->setCurlOption(CURLOPT_HTTPHEADER, ['X-Redmine-API-Key: secret_access_key']);
```

### `setPort()` -> `setCurlOption()`

```diff
-$client->setPort(8080);
+$client->setCurlOption(CURLOPT_PORT, 8080);
```

### `getResponseCode()` -> `getLastResponseStatusCode()`

```diff
-$statusCode = $client->getResponseCode();
+$statuscode = $client->getLastResponseStatusCode();
```

### `setImpersonateUser()` -> `startImpersonateUser()`

```diff
// impersonate the user `robin`
-$client->setImpersonateUser('robin');
+$client->startImpersonateUser('robin');

$userData = $client->getApi('user')->getCurrentUser();

// Now stop impersonation
-$client->setImpersonateUser(null);
+$client->stopImpersonateUser();
```

### `setCustomHost()` -> `setCurlOption()`

```diff
-$client->setCustomHost('http://custom.example.com');
+$client->setCurlOption(CURLOPT_HTTPHEADER, ['Host: http://custom.example.com']);
```

## 2. Methods deprecation

The following methods are deprecated and were set public or protected only for
testing. They are not available in `NativeCurlClient`. If you are using them,
please remove them.

- `getUrl()`
- `decode()`
- `getCheckSslCertificate()`
- `getCheckSslHost()`
- `getSslVersion()`
- `getUseHttpAuth()`
- `getPort()`
- `getImpersonateUser()`
- `getCustomHost()`
- `getCurlOptions()`
- `prepareRequest()`
- `processCurlResponse()`
- `runRequest()`

## 3. Switch to `NativeCurlClient`

Just like with the old `Redmine\Client`, the `Redmine\Client\NativeCurlClient` requires :

- a URL to your Redmine instance
- an Apikey or username
- and optional a password if you want to use username/password (not recommended).

So after making all the changes suggested in the previous sections you should be able to
test your code without errors and now simply switch the client.

```diff
// Instantiate with ApiKey
-$client = new \Redmine\Client(
+$client = new \Redmine\Client\NativeCurlClient(
    'https://redmine.example.com',
    '1234567890abcdfgh'
);
```

Now you should be ready. Please make sure that you are only using client public
methods that are defined in `Redmine\Client\NativeCurlClient` because all the other
methods will be removed or set to private in the next major release. Otherwise
you will have to change your code in the future again.
