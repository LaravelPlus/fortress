<?php

declare(strict_types=1);

namespace Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Contracts\Permission as PermissionContract;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class Permission extends Model implements PermissionContract
{
    protected $fillable = ['name', 'guard_name'];

    public function getTable()
    {
        return 'permissions';
    }

    public static function findByName(string $name, $guardName = null): PermissionContract
    {
        $guardName = $guardName ?? config('auth.defaults.guard');
        $permission = static::where('name', $name)->where('guard_name', $guardName)->first();

        if (! $permission) {
            throw PermissionDoesNotExist::create($name, $guardName);
        }

        return $permission;
    }

    public static function findById(int $id, $guardName = null): PermissionContract
    {
        $guardName = $guardName ?? config('auth.defaults.guard');
        $permission = static::where('id', $id)->where('guard_name', $guardName)->first();

        if (! $permission) {
            throw PermissionDoesNotExist::withId($id, $guardName);
        }

        return $permission;
    }

    public static function findOrCreate(string $name, $guardName = null): PermissionContract
    {
        $guardName = $guardName ?? config('auth.defaults.guard');
        $permission = static::where('name', $name)->where('guard_name', $guardName)->first();

        if (! $permission) {
            return static::create(['name' => $name, 'guard_name' => $guardName]);
        }

        return $permission;
    }
} 