<?php

declare(strict_types=1);

namespace Redmine\Exception;

use InvalidArgumentException;
use Redmine\Exception as RedmineException;

class InvalidParameterException extends InvalidArgumentException implements RedmineException {}
