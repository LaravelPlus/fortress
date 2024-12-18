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
    public function adminCreateDelete(): void
    {
        // Method for an admin with create/delete permissions
    }

    #[Authorize(
        public: false,
        roles: ['editor', 'moderator'],
        permissions: ['update'],
        gates: null,
        owner: null
    )]
    public function editorUpdate(): void
    {
        // Method for an editor or moderator with update permission
    }

    #[Authorize(
        public: true,
        roles: 'guest',
        permissions: null,
        gates: 'view-only',
        owner: null
    )]
    public function guestView(): void
    {
        // Method for a guest with view-only gate access
    }

    #[Authorize(
        public: false,
        roles: ['admin', 'super-admin'],
        permissions: ['manage-settings'],
        gates: 'settings-gate',
        owner: 'App\\Models\\Settings',
        overrideKey: 'admin_id'
    )]
    public function adminSettings(): void
    {
        // Method for an admin managing settings
    }

    #[Authorize(
        public: false,
        roles: [],
        permissions: null,
        gates: null,
        owner: 'App\\Models\\Comment',
        overrideKey: 'user_id'
    )]
    public function userComment(): void
    {
        // Method for checking ownership of a comment
    }

    public function emptyMethod(): void
    {
        // Method with no Authorize attributes
    }
}
