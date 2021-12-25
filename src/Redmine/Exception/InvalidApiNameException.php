<?php

namespace Redmine\Exception;

use InvalidArgumentException;
use Redmine\Exception as RedmineException;

class InvalidApiNameException extends InvalidArgumentException implements RedmineException
{
}
