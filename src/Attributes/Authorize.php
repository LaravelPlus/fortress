<?php

declare(strict_types=1);

namespace Laravelplus\Fortress\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class Authorize
{
    /**
     * Create a new Authorize attribute.
     *
     * @param  bool  $public  Whether the method is publicly accessible without authorization.
     * @param  string|array|null  $roles  Roles required to access the method.
     * @param  string|array|null  $permissions  Permissions required to access the method.
     * @param  string|null  $gates  Laravel gates required to pass.
     * @param  string|null  $owner  Model class for ownership validation.
     * @param  string|null  $overrideKey  Key to use for ownership validation (default: 'user_id').
     */
    public function __construct(
        public bool $public = false,
        public string|array|null $roles = null,
        public string|array|null $permissions = null,
        public ?string $gates = null,
        public ?string $owner = null,
        public ?string $overrideKey = null
    ) {}
}
