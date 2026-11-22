# Migrate from v2 to v3

v3.0.0 removes everything that v2.11 has deprecated and makes the "future mode" the only behaviour. This guide shows how to get there step by step. The [changelog](../CHANGELOG.md) lists every change with its issue number.

## Upgrade path

1. **Update to v2.11** (`composer require kbsali/redmine-api:^2.11`). It is the last v2 minor, it carries all deprecations and the complete future mode.
2. **Call `enableFutureMode()`** on your client. Your code now behaves like it will in v3.0.0: unexpected responses throw exceptions, and empty values in params are rejected. Fix the exceptions your code runs into, see [Handling unexpected responses](usage.md#handling-unexpected-responses).
3. **Fix the deprecations.** Run your tests with deprecation notices visible (e.g. `error_reporting(E_ALL)` and PHPUnit's deprecation report) and replace every deprecated call, see [Removed methods](#removed-methods).
4. **Update to v3.0** (`composer require kbsali/redmine-api:^3.0`). Remove the call to `enableFutureMode()`, it does nothing anymore.

v3.0.0 keeps `"php": "^8.2"`, you do not need a PHP update.

## Future mode is the only behaviour

```php
// v2.11
$client = new \Redmine\Client\NativeCurlClient($url, $apiKey);
$client->enableFutureMode();

// v3.0
$client = new \Redmine\Client\NativeCurlClient($url, $apiKey);
```

`enableFutureMode()` stays as a method that does nothing, so code that already calls it keeps working. It is deprecated in v3.1.0 and removed in v4.

What changes for your code:

- **Unexpected status codes throw.** All `list*()`, `show()`, `create()`, `update()`, `remove()` and similar methods throw a `Redmine\Exception\UnexpectedResponseException` instead of returning `false`, an empty string, an error string or the body of the error response. The `show()` methods also throw for an empty body or a body that is not a JSON object or array. Their return types no longer include `false` or an error string. Replace checks like `if ($result === false)` with `try`/`catch`:

  ```php
  try {
      $issue = $client->getApi('issue')->show(123);
  } catch (\Redmine\Exception\UnexpectedResponseException $e) {
      $statusCode = $e->getResponse()->getStatusCode();
  }
  ```

- **Empty values in params throw.** A top-level param with the value `''`, `[]` or `false` in `create()` and `update()` throws an `\InvalidArgumentException` instead of being dropped. Use `null` to skip a param, `Param::Clear` to clear a field and `0` to switch off a boolean. See [Empty values](usage.md#empty-values-in-create-and-update).

  ```php
  use Redmine\Api\Param;

  $client->getApi('issue')->update($issueId, [
      'description' => Param::Clear, // not ''
      'is_private' => 0,             // not false
      'due_date' => null,            // skipped
  ]);
  ```

- **`Issue` names must exist.** An unknown name in `project`, `category`, `status`, `tracker`, `assigned_to` or `author` throws an `InvalidParameterException` instead of being dropped from the request. See [Name aliases](usage.md#name-aliases-in-issuecreate-and-issueupdate).
- **Requires Redmine 4.1 or newer.** Older Redmine versions respond with `200 OK` instead of `204 No Content` on successful updates and deletions, which is now an unexpected status code.

## Removed methods

Each removed method was deprecated in v2.x, so your v2.11 code reports it at runtime.

### `Redmine\Api\...` classes

| Removed | Use instead |
| --- | --- |
| `all()` | `list()`; `listByProject()` for `IssueCategory`, `Membership`, `Version` and `Wiki`; `listByIssueId()` for `IssueRelation` |
| `listing()` | `listNames()`; `listNamesByProject()` for `IssueCategory` and `Version`; `listLogins()` for `User` |
| `getIdByName()`, `User::getIdByUsername()` | search the result of `listNames()` / `listNamesByProject()` / `listLogins()` yourself |
| `Search::search()` | `Search::listByQuery()` |
| `Issue::PRIO_*` constants | `IssuePriority::list()`, search the `issue_priorities` for the `name` |

The replacements behave differently in details:

- `all()` returned `false` or an error string on an error, `list*()` throws.
- `listNames()` returns `[id => name]`, `listing()` returned `[name => id]`. `listNames()` has no `$forceUpdate`, `$reverse` or `$params` arguments and fetches the list once per API instance, so create a new instance for fresh data.
- Replace `getIdByName()` with a search in the list. The name is not always unique, `getIdByName()` returned the ID of the last match, `array_search()` the first one.

  ```php
  // v2
  $id = $client->getApi('project')->getIdByName('My project');

  // v3
  $id = array_search('My project', $client->getApi('project')->listNames(), true); // false if not found
  ```

- `getIdByName()` of `CustomField`, `Project` and `Version` accepted `$params` (e.g. `offset`, `limit`) and searched only the requested page. If you rely on that, call `list($params)` (`listByProject($project, $params)` for `Version`) and search the returned records. For locked or registered users, or any other filter, call `User::list()` with that filter and search the `login`.

### Subclasses of `Redmine\Api\AbstractApi`

| Removed | Use instead |
| --- | --- |
| `retrieveAll()` | `retrieveData()`, it throws on an invalid response body |
| `lastCallFailed()` | `getLastResponse()->getStatusCode()` |
| `sanitizeParams()`, `isNotNull()` | filter empty values (`null`, `false`, `''`, `[]`) yourself, e.g. with `array_filter()` |
| `Project::prepareParamsXml()`, `attachCustomFieldXML()` | `\Redmine\Serializer\XmlSerializer::createFromArray()` |
| property `$client` | `getHttpClient()` |
| `get()`, `post()`, `put()`, `delete()` | `$this->getHttpClient()->request()` with a request from `\Redmine\Http\HttpFactory::makeRequest()` (or `makeJsonRequest()` / `makeXmlRequest()`) |

Good to know:

- `lastCallFailed()` treated every 2xx code except `200` and `201` as failed. An API instance that made no request reports the status code `0`.
- `createFromArray()` returns an `XmlSerializer` and not a `SimpleXMLElement`. Use `new SimpleXMLElement($serializer->getEncoded())` if you need one.
  ```php
  $serializer = \Redmine\Serializer\XmlSerializer::createFromArray(['project' => $params]); // the root key is required
  $xml = new SimpleXMLElement($serializer->getEncoded());
  ```
- `request()` returns a `Redmine\Http\Response` with `getStatusCode()`, `getContentType()` and `getContent()`. The removed `get()` returned a `SimpleXMLElement` or an array depending on the content type, `post()` and `put()` a `SimpleXMLElement` or the raw body, `delete()` the raw body. Do the decoding yourself. `getLastResponse()` of the API only reflects calls made through `retrieveData()` or the API methods, not through `HttpClient::request()`.
- `Redmine\Api\AbstractApi` only accepts a `Redmine\Http\HttpClient` in its constructor. A custom `Redmine\Client\Client` must also implement `HttpClient`.
- `Redmine\Api\Issue` no longer asks a custom `Client` for the APIs it needs to resolve names, it creates them with its own `HttpClient`.

### Clients

The methods `requestGet()`, `requestPost()`, `requestPut()`, `requestDelete()`, `getLastResponseStatusCode()`, `getLastResponseContentType()` and `getLastResponseBody()` are removed from the interface `Redmine\Client\Client`, from `NativeCurlClient` and from `Psr18Client`. Call `request()` of `Redmine\Http\HttpClient` instead:

```php
use Redmine\Http\HttpFactory;

$response = $client->request(HttpFactory::makeJsonRequest('GET', '/issues.json'));

$response->getStatusCode();
$response->getContent();
```

The removed `request...()` methods returned `true` for a status code below `400` and `false` otherwise. To get the last response of an API call use `getLastResponse()` of the API instance:

```php
$api = $client->getApi('issue');
$api->list();

$response = $api->getLastResponse();
```

## Other removals

- **Upload by file path.** `Attachment::upload()` always sends its argument as is. Read the file yourself. If you passed a string that is a path of an existing file, the path itself is now uploaded.

  ```php
  // v2
  $client->getApi('attachment')->upload('/path/to/file.txt');

  // v3
  $client->getApi('attachment')->upload(file_get_contents('/path/to/file.txt'));
  ```

- **`ServerRequestFactoryInterface`.** `Psr18Client::__construct()` accepts a `Psr\Http\Message\RequestFactoryInterface` as Argument #2 (`$requestFactory`), a `Psr\Http\Message\ServerRequestFactoryInterface` throws an `Exception`.
- **Migration guides.** `docs/migrate-to-psr18client.md` and `docs/migrate-to-nativecurlclient.md` are removed. They described the upgrade from the old `Redmine\Client` of v1.x.

## Final classes

The 20 API classes `Redmine\Api\Attachment`, `CustomField`, `Group`, `Issue`, `IssueCategory`, `IssuePriority`, `IssueRelation`, `IssueStatus`, `Membership`, `News`, `Project`, `Query`, `Role`, `Search`, `TimeEntry`, `TimeEntryActivity`, `Tracker`, `User`, `Version` and `Wiki` are `final` and their constructors are `private`:

- Stop extending them. For a custom API class extend `Redmine\Api\AbstractApi`.
- Create them with `$client->getApi('issue')` or `Issue::fromHttpClient($client)` instead of `new Issue($client)`.
- Their public methods declare return types now (e.g. `Issue::show(): array`, `Issue::update(): string`). The types are the ones the `@return` tags already documented.

The exceptions `ClientException`, `InvalidApiNameException`, `InvalidParameterException`, `MissingParameterException` and `SerializerException` in `Redmine\Exception` are `final` as well. Catch them, or catch the interface `Redmine\Exception`, instead of extending them.

## Checklist

- [ ] Running on v2.11 with `enableFutureMode()` and no deprecation notice left
- [ ] Every API call that can fail is wrapped in a `try`/`catch` for `UnexpectedResponseException`
- [ ] No `''`, `[]` or `false` as a top-level param in `create()` and `update()`
- [ ] No class extends an API class or one of the five exceptions, no `new` for an API class
- [ ] Custom `Redmine\Api\AbstractApi` subclasses use no removed method and no `$client` property
- [ ] Calls to `enableFutureMode()` removed
