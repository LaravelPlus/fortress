# LaravelPlus Fortress

[![Latest Version on Packagist](https://img.shields.io/packagist/v/laravelplus/fortress.svg?style=flat-square)](https://packagist.org/packages/laravelplus/fortress)
[![Total Downloads](https://img.shields.io/packagist/dt/laravelplus/fortress.svg?style=flat-square)](https://packagist.org/packages/laravelplus/fortress)
![GitHub Actions](https://github.com/laravelplus/fortress/actions/workflows/main.yml/badge.svg)

**Fortress** is a powerful Laravel package designed to streamline attribute-based authorization. By leveraging the `#[Authorize]` attribute, it provides a declarative and clean approach to securing your Laravel application. Whether managing roles, permissions, gates, or ownership rules, Fortress ensures security is flexible, robust, and easy to implement.

---

## Key Features

- **Attribute-Based Authorization**: Use `#[Authorize]` attributes for roles, permissions, gates, and ownership checks.
- **Simplifies Middleware Logic**: Declarative syntax removes clutter from middleware, keeping it clean and readable.
- **Ownership Validation**: Validate ownership with configurable keys and default behaviors.
- **Laravel 11 Support**: Fully compatible with Laravel 11 and follows PSR standards.
- **Customizable Configuration**: Flexible configuration for roles, permissions, gates, and ownership rules.

---

## Installation

You can install the package via Composer:

```bash
composer require laravelplus/fortress
```

### Configuration


Append Middleware where you need it:
```
$middleware->web(append: [
    ...
    Laravelplus\Fortress\Middleware\AttributeAuthorizationMiddleware::class,
]);
```

To publish the configuration file, run:

```bash
php artisan vendor:publish --provider="Laravelplus\\Fortress\\FortressServiceProvider"
```

The configuration file will be published at `config/fortress.php`. Customize default values for ownership keys, gates, and more.

---

## Usage

### Applying the `#[Authorize]` Attribute

Add the `#[Authorize]` attribute to your controller methods to enforce authorization:

```php
use Laravelplus\Fortress\Attributes\Authorize;

class PostController
{
    #[Authorize(
        public: false,
        roles: ['admin', 'editor'],
        permissions: ['create', 'update'],
        owner: 'App\\Models\\Post',
        overrideKey: 'author_id'
    )]
    public function update(Request $request, $id)
    {
        // Update logic
    }
}
```

### How It Works

- **Roles**: Ensures the user has one of the specified roles (`admin` or `editor`).
- **Permissions**: Validates the user has `create` or `update` permissions.
- **Ownership**: Checks if the authenticated user is the owner of the `Post` model by comparing `author_id` with the user's `id`.

### Example Scenarios

#### Example 1: Public Endpoint

Allow unauthenticated users to access a method:

```php
#[Authorize(public: true)]
public function show($id)
{
    // This method is accessible by everyone
}
```

#### Example 2: Role and Permission Validation

Restrict access based on roles and permissions:

```php
#[Authorize(roles: ['manager'], permissions: ['approve-leave'])]
public function approveLeave(Request $request)
{
    // This method is accessible only by managers with approve-leave permission
}
```

#### Example 3: Ownership Validation

Restrict access to resources owned by the authenticated user:

```php
#[Authorize(owner: 'App\\Models\\Comment', overrideKey: 'user_id')]
public function editComment(Request $request, $id)
{
    // Accessible only if the comment belongs to the authenticated user
}
```

#### Example 4: Gate Validation

Use Laravel gates to control access:

```php
#[Authorize(gates: 'edit-settings')]
public function settings()
{
    // This method is accessible if the "edit-settings" gate returns true
}
```

---

## Testing

To run the package's test suite:

```bash
composer test
```

Example output:

```bash
PHPUnit 11.0.0 by Sebastian Bergmann and contributors.

.............                                                    22 / 22 (100%)

Time: 00:00.410, Memory: 26.00 MB
OK (22 tests, 60 assertions)
```

---

## Changelog

See the [CHANGELOG](CHANGELOG.md) for details about recent changes.

---

## Contributing

Contributions are welcome! Please see the [CONTRIBUTING](CONTRIBUTING.md) file for details on how to contribute.

---

## Security

If you discover any security-related issues, please email [info@after.si](mailto:info@after.si) instead of using the issue tracker.

---

## Credits

- **Author**: [Nejcc](https://github.com/nejcc)
- **Contributors**: [All Contributors](../../contributors)

---

## License

This package is licensed under the MIT License. See the [LICENSE](LICENSE.md) file for details.

---

## Download

You can download the package here:  
[Packagist - Laravel Fortress](https://packagist.org/packages/laravelplus/fortress)
