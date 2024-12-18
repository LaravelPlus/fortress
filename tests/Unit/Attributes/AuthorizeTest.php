<?php

declare(strict_types=1);

namespace Tests\Unit\Attributes;

use Laravelplus\Fortress\Attributes\Authorize;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Tests\Unit\Attributes\TestClasses\TestClassWithAuthorize;

final class AuthorizeTest extends TestCase
{
    private function getAttributeInstance(string $methodName): Authorize
    {
        $reflectionMethod = new ReflectionMethod(TestClassWithAuthorize::class, $methodName);
        $attributes = $reflectionMethod->getAttributes(Authorize::class);
        $this->assertCount(1, $attributes, "The method '{$methodName}' should have exactly one Authorize attribute.");

        return $attributes[0]->newInstance();
    }

    public function test_public_attribute(): void
    {
        $attribute = $this->getAttributeInstance('adminCreateDelete');
        $this->assertTrue($attribute->public);
    }

    public function test_roles_attribute(): void
    {
        $attribute = $this->getAttributeInstance('adminCreateDelete');
        $this->assertEquals('admin', $attribute->roles);
    }

    public function test_permissions_attribute(): void
    {
        $attribute = $this->getAttributeInstance('adminCreateDelete');
        $this->assertEquals(['create', 'delete'], $attribute->permissions);
    }

    public function test_gates_attribute(): void
    {
        $attribute = $this->getAttributeInstance('adminCreateDelete');
        $this->assertEquals('access-gate', $attribute->gates);
    }

    public function test_owner_attribute(): void
    {
        $attribute = $this->getAttributeInstance('adminCreateDelete');
        $this->assertEquals('App\\Models\\Post', $attribute->owner);
    }

    public function test_override_key_attribute(): void
    {
        $attribute = $this->getAttributeInstance('adminCreateDelete');
        $this->assertEquals('creator_id', $attribute->overrideKey);
    }

    public function test_roles_and_permissions_combination(): void
    {
        $attribute = $this->getAttributeInstance('adminCreateDelete');
        $this->assertEquals('admin', $attribute->roles);
        $this->assertEquals(['create', 'delete'], $attribute->permissions);
    }

    public function test_roles_and_owner_combination(): void
    {
        $attribute = $this->getAttributeInstance('adminCreateDelete');
        $this->assertEquals('admin', $attribute->roles);
        $this->assertEquals('App\\Models\\Post', $attribute->owner);
    }

    public function test_permissions_and_gates_combination(): void
    {
        $attribute = $this->getAttributeInstance('adminCreateDelete');
        $this->assertEquals(['create', 'delete'], $attribute->permissions);
        $this->assertEquals('access-gate', $attribute->gates);
    }

    public function test_all_combined(): void
    {
        $attribute = $this->getAttributeInstance('adminCreateDelete');
        $this->assertTrue($attribute->public);
        $this->assertEquals('admin', $attribute->roles);
        $this->assertEquals(['create', 'delete'], $attribute->permissions);
        $this->assertEquals('access-gate', $attribute->gates);
        $this->assertEquals('App\\Models\\Post', $attribute->owner);
        $this->assertEquals('creator_id', $attribute->overrideKey);
    }

    public function test_no_roles_and_permissions(): void
    {
        $attribute = $this->getAttributeInstance('guestView');
        $this->assertTrue($attribute->public);
        $this->assertEquals('guest', $attribute->roles);
        $this->assertNull($attribute->permissions);
    }

    public function test_only_gates(): void
    {
        $attribute = $this->getAttributeInstance('guestView');
        $this->assertEquals('view-only', $attribute->gates);
        $this->assertNull($attribute->owner);
        $this->assertNull($attribute->overrideKey);
    }

    public function test_only_owner(): void
    {
        $attribute = $this->getAttributeInstance('userComment');
        $this->assertFalse($attribute->public);
        $this->assertEquals('App\\Models\\Comment', $attribute->owner);
        $this->assertEquals('user_id', $attribute->overrideKey);
        $this->assertNull($attribute->permissions);
        $this->assertNull($attribute->gates);
    }

    public function test_combined_roles_permissions_and_owner(): void
    {
        $attribute = $this->getAttributeInstance('adminSettings');
        $this->assertFalse($attribute->public);
        $this->assertEquals(['admin', 'super-admin'], $attribute->roles);
        $this->assertEquals(['manage-settings'], $attribute->permissions);
        $this->assertEquals('App\\Models\\Settings', $attribute->owner);
        $this->assertEquals('admin_id', $attribute->overrideKey);
    }

    public function test_empty_attributes(): void
    {
        $reflectionMethod = new ReflectionMethod(TestClassWithAuthorize::class, 'emptyMethod');
        $attributes = $reflectionMethod->getAttributes(Authorize::class);
        $this->assertEmpty($attributes, 'The emptyMethod should not have any Authorize attribute.');
    }
}
