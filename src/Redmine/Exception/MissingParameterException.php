<?php

namespace Redmine\Exception;

use InvalidArgumentException;
use Redmine\Exception as RedmineException;

class MissingParameterException extends InvalidArgumentException implements RedmineException {}
