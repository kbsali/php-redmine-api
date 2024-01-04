<?php

namespace Redmine\Exception;

use Redmine\Exception as RedmineException;
use RuntimeException;

class SerializerException extends RuntimeException implements RedmineException {}
