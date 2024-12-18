<?php

declare(strict_types=1);

namespace Tests\Unit\Attributes;

use Laravelplus\Fortress\Attributes\Authorize;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Tests\Unit\Attributes\TestClasses\TestClassWithAuthorize;

final class AuthorizeTest extends TestCase
{
    public function test_authorize_attribute_default_values(): void
    {
        $authorize = new Authorize();

        $this->assertFalse($authorize->public);
        $this->assertNull($authorize->roles);
        $this->assertNull($authorize->permissions);
        $this->assertNull($authorize->gates);
        $this->assertNull($authorize->owner);
        $this->assertNull($authorize->overrideKey);
    }

    public function test_authorize_attribute_custom_values(): void
    {
        $authorize = new Authorize(
            public: true,
            roles: ['admin', 'editor'],
            permissions: 'edit-articles',
            gates: 'manage-articles',
            owner: 'App\\Models\\Article',
            overrideKey: 'owner_id'
        );

        $this->assertTrue($authorize->public);
        $this->assertEquals(['admin', 'editor'], $authorize->roles);
        $this->assertEquals('edit-articles', $authorize->permissions);
        $this->assertEquals('manage-articles', $authorize->gates);
        $this->assertEquals('App\\Models\\Article', $authorize->owner);
        $this->assertEquals('owner_id', $authorize->overrideKey);
    }

    public function test_authorize_attribute_reflection(): void
    {
        $reflectionMethod = new ReflectionMethod(
            TestClassWithAuthorize::class,
            'testMethod'
        );
        $attributes = $reflectionMethod->getAttributes(Authorize::class);
        $this->assertCount(1, $attributes);

        $attributeInstance = $attributes[0]->newInstance();
        $this->assertInstanceOf(Authorize::class, $attributeInstance);
        $this->assertTrue($attributeInstance->public);
        $this->assertEquals('admin', $attributeInstance->roles);
        $this->assertEquals(['create', 'delete'], $attributeInstance->permissions);
        $this->assertEquals('access-gate', $attributeInstance->gates);
        $this->assertEquals('App\\Models\\Post', $attributeInstance->owner);
        $this->assertEquals('creator_id', $attributeInstance->overrideKey);
    }
}
