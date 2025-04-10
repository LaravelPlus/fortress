<?php

declare(strict_types=1);

namespace Laravelplus\Fortress\Exceptions;

use Exception;

final class UnauthorizedException extends Exception
{
    public function __construct(string $message = 'Unauthorized action', int $code = 403)
    {
        parent::__construct($message, $code);
    }
} 