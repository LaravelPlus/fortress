<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Auth;
use Laravelplus\Fortress\Fortress;
use Symfony\Component\HttpKernel\Exception\HttpException;

beforeEach(function (): void {
    // Simulate an authenticated user
    $this->user = new class()
    {
        public int $id = 1;

        public array $roles = [];

        public array $permissions = [];

        public function hasAnyRole(array $roles): bool
        {
            return !empty(array_intersect($this->roles, $roles));
        }

        public function hasPermissionTo(string $permission): bool
        {
            return in_array($permission, $this->permissions, true);
        }
    };

    // Bind the user to the Auth facade
    Auth::shouldReceive('user')->andReturn($this->user);

    // Override abort helper globally
    if (!function_exists('abort')) {
        function abort($code, $message = null): void
        {
            throw new HttpException($code, $message);
        }
    }
});

it('passes public access without authorization', function (): void {
    expect(Fortress::authorize([], null, null, null))->toBeTrue();
});

it('fails role authorization for unauthorized roles', function (): void {
    $this->user->roles = ['editor'];

    expect(fn () => Fortress::authorize(roles: ['admin']))->toThrow(
        HttpException::class,
        'Unauthorized role.'
    );
});

it('passes role authorization for authorized roles', function (): void {
    $this->user->roles = ['admin'];

    expect(Fortress::authorize(roles: ['admin']))->toBeTrue();
});

it('fails ownership validation if not the owner', function (): void {
    // Create a fake Post model
    $post = new FakePost(2); // Simulate a Post owned by another user
    app()->instance(FakePost::class, $post);

    expect(fn () => Fortress::authorize(owner: FakePost::class, overrideKey: 'user_id'))->toThrow(
        HttpException::class,
        'Unauthorized ownership.'
    );
});

it('passes ownership validation if the user is the owner', function (): void {
    // Create a fake Post model
    $post = new FakePost(1); // Simulate a Post owned by the authenticated user
    app()->instance(FakePost::class, $post);

    expect(Fortress::authorize(owner: FakePost::class, overrideKey: 'user_id'))->toBeTrue();
});

// Define a simple fake Post class for testing
final class FakePost
{
    public int $user_id;

    public function __construct(int $user_id)
    {
        $this->user_id = $user_id;
    }

    public static function find(int $id): self
    {
        return new self($id);
    }
}
