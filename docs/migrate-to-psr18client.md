# Migrate from `Redmine\Client` to `Redmine\Client\Psr18Client`

Since `php-redmine-api` v1.7.0 there is a new PSR-18 based client `Redmine\Client\Psr18Client`. This guide will help you to migrate your code if you want to use an app-wide PSR-18 HTTP client.

## 1. Use new client methods

### api() to getApi()

With the new interface `Redmine\Client\Client` there are now standarized methods for all clients. The new `Redmine\Client\Psr18Client` and the current `Redmine\Client` implementing this interface.

Search in your code for the usage of `$client->api('issue')` and the magic getter like `$client->issue`. Then replace this calls with `$client->getApi('issue')`.

```diff
-$issue = $client->issue->show($issueId);
+$issue = $client->getApi('issue')->show($issueId);

-$client->api('issue')->create($data);
+$client->getApi('issue')->create($data);
```

### getResponseCode() to getLastResponseStatusCode()

Replace every call for `$client->getResponseCode()` with `$client->getLastResponseStatusCode()`.

```diff
-if ($client->getResponseCode() === 500)
+if ($client->getLastResponseStatusCode() === 500)
{
    throw new \Exception('Redmine call failed');
}
```

### get() to requestGet()

If you are using `$client->get()`, `$client->post()`, `$client->put()` or `$client->delete()` directly you will have to change your code. This methods parse a possible JSON or XML response but in future the parsing of the raw response body will be up to you.

To help you with the parsing of the raw response the client interface introduces two new methods: `getLastResponseContentType()` and `getLastResponseBody`.

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
    $dataAsString = $body;
}
```

### post() to requestPost()

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
    $dataAsString = $body;
}
```

### put() to requestPut()

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
    $dataAsString = $body;
}
```

### delete() to requestDelete()

This example shows how you can parse the response body of a DELETE request.

```diff
-$dataAsString = $this->client->delete($path);
+$this->client->requestDelte($path);
+$dataAsString = $this->client->getLastResponseBody();
```

### setImpersonateUser() to startImpersonateUser()

If you are using the [Redmine user impersonation](https://www.redmine.org/projects/redmine/wiki/Rest_api#User-Impersonation) you have to change your code.

```php
// impersonate the user `robin`
-$client->setImpersonateUser('robin');
+$client->startImpersonateUser('robin');

$userData = $client->getApi('user')->getCurrentUser();

// Now stop impersonation
-$client->setImpersonateUser(null);
+$client->stopImpersonateUser();
```

After this changes you should be able to test your code without

## 2. Switch to `Psr18Client`

## 3. Set `cURL` options
