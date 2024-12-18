<?php

declare(strict_types=1);

namespace Tests\Feature\Middleware;

use Laravelplus\Fortress\Attributes\Authorize;

final class TestControllerWithAuthorize
{
    #[Authorize]
    public function testMethod(): void
    {
        // Method intentionally left blank
    }
}
