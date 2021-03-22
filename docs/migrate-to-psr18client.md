# Migrate from `Redmine\Client` to `Redmine\Client\Psr18Client`

Since `php-redmine-api` v1.7.0 there is a new PSR-18 based client `Redmine\Client\Psr18Client`. This guide will help you to migrate your code if you want to use an app-wide PSR-18 HTTP client.

**TOC**

1. Use `getApi()` instead of `api()` and magic getter
2. Switch to `Psr18Client`
3. How to set `cURL` options
