<?php

declare(strict_types=1);

namespace Laravelplus\Fortress\Exceptions;

use Exception;

final class InvalidConfigurationException extends Exception
{
    public function __construct(string $message = 'Invalid configuration', int $code = 500)
    {
        parent::__construct($message, $code);
    }
} 