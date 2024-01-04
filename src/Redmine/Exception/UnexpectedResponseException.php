<?php

namespace Redmine\Exception;

use Redmine\Exception as RedmineException;
use RuntimeException;

/**
 * Exception if the Redmine server delivers an unexpected response.
 *
 * Use the following methods to investigate the response:
 *
 * - Redmine\Client\Client::getLastResponseStatusCode()
 * - Redmine\Client\Client::getLastResponseContentType()
 * - Redmine\Client\Client::getLastResponseBody()
 */
final class UnexpectedResponseException extends RuntimeException implements RedmineException {}
