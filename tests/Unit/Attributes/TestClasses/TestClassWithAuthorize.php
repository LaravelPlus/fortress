<?php

declare(strict_types=1);

namespace Tests\Unit\Attributes\TestClasses;

use Laravelplus\Fortress\Attributes\Authorize;

final class TestClassWithAuthorize
{
    #[Authorize(
        public: true,
        roles: 'admin',
        permissions: ['create', 'delete'],
        gates: 'access-gate',
        owner: 'App\\Models\\Post',
        overrideKey: 'creator_id'
    )]
    public function testMethod(): void
    {
        // Method intentionally left blank
    }
}
