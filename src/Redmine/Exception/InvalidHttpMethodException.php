<?php

declare(strict_types=1);

namespace Redmine\Exception;

use InvalidArgumentException;
use Redmine\Exception as RedmineException;

final class InvalidHttpMethodException extends InvalidArgumentException implements RedmineException {}
