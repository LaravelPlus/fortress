<?php

declare(strict_types=1);

namespace Tests\Unit;

use Illuminate\Support\Facades\Auth;
use Laravelplus\Fortress\Fortress;
use Mockery;
use Tests\TestCase;

final class FortressTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_unauthenticated_user(): void
    {
        Auth::shouldReceive('user')->andReturn(null);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionMessage('User not authenticated.');

        Fortress::authorize();
    }

    public function test_unauthorized_role(): void
    {
        $user = Mockery::mock();
        $user->shouldReceive('hasAnyRole')->with(['admin'])->andReturn(false);
        Auth::shouldReceive('user')->andReturn($user);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionMessage('Unauthorized role.');

        Fortress::authorize(roles: ['admin']);
    }

    public function test_unauthorized_permission(): void
    {
        $user = Mockery::mock();
        $user->shouldReceive('hasAnyRole')->andReturn(true); // Assume role check passes
        $user->shouldReceive('hasPermissionTo')->with('edit-posts')->andReturn(false);
        Auth::shouldReceive('user')->andReturn($user);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionMessage('Unauthorized permission.');

        Fortress::authorize(permissions: 'edit-posts');
    }

    public function test_unauthorized_ownership(): void
    {
        $user = Mockery::mock();
        $user->shouldReceive('hasAnyRole')->andReturn(true); // Assume role check passes
        $user->shouldReceive('hasPermissionTo')->andReturn(true); // Assume permission check passes
        $user->id = 1;

        $ownerMock = Mockery::mock();
        $ownerMock->author_id = 2; // Simulate the mismatch key

        Auth::shouldReceive('user')->andReturn($user);
        app()->instance('App\\Models\\Post', $ownerMock);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionMessage('Unauthorized ownership.');

        Fortress::authorize(
            owner: 'App\\Models\\Post'
        );
    }

    public function test_authorization_success(): void
    {
        $user = Mockery::mock();
        $user->shouldReceive('hasAnyRole')->with(['admin'])->andReturn(true);
        $user->shouldReceive('hasPermissionTo')->with('edit-posts')->andReturn(true);
        $user->id = 1;

        $ownerMock = Mockery::mock();
        $ownerMock->author_id = 1; // Ensure the key matches the user ID

        Auth::shouldReceive('user')->andReturn($user);
        app()->instance('App\\Models\\Post', $ownerMock);

        $result = Fortress::authorize(
            roles: ['admin'],
            permissions: 'edit-posts',
            owner: 'App\\Models\\Post'
        );

        $this->assertTrue($result);
    }
}
