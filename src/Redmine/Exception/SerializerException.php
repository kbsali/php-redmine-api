<?php

declare(strict_types=1);

namespace Redmine\Exception;

use Redmine\Exception as RedmineException;
use RuntimeException;

class SerializerException extends RuntimeException implements RedmineException {}
