<?php

declare(strict_types=1);

namespace Redmine\Exception;

use InvalidArgumentException;
use Redmine\Exception as RedmineException;

final class InvalidParameterException extends InvalidArgumentException implements RedmineException {}
