<?php

namespace Redmine\Exception;

use InvalidArgumentException;
use Redmine\Exception as RedmineException;

/**
 * InvalidApiNameException.
 */
class InvalidApiNameException extends InvalidArgumentException implements RedmineException
{
}
