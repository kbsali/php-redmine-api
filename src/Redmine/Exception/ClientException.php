<?php

namespace Redmine\Exception;

use Exception;
use Redmine\Exception as RedmineException;

/**
 * Client exception.
 *
 * Will be thrown if anything goes wrong on creating or sending a HTTP request
 */
class ClientException extends Exception implements RedmineException {}
