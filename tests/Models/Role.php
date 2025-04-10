<?php

declare(strict_types=1);

namespace Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Contracts\Role as RoleContract;
use Spatie\Permission\Exceptions\RoleDoesNotExist;

class Role extends Model implements RoleContract
{
    protected $fillable = ['name', 'guard_name'];

    public function getTable()
    {
        return 'roles';
    }

    public static function findByName(string $name, $guardName = null): RoleContract
    {
        $guardName = $guardName ?? config('auth.defaults.guard');
        $role = static::where('name', $name)->where('guard_name', $guardName)->first();

        if (! $role) {
            throw RoleDoesNotExist::create($name, $guardName);
        }

        return $role;
    }

    public static function findById(int $id, $guardName = null): RoleContract
    {
        $guardName = $guardName ?? config('auth.defaults.guard');
        $role = static::where('id', $id)->where('guard_name', $guardName)->first();

        if (! $role) {
            throw RoleDoesNotExist::withId($id, $guardName);
        }

        return $role;
    }

    public static function findOrCreate(string $name, $guardName = null): RoleContract
    {
        $guardName = $guardName ?? config('auth.defaults.guard');
        $role = static::where('name', $name)->where('guard_name', $guardName)->first();

        if (! $role) {
            return static::create(['name' => $name, 'guard_name' => $guardName]);
        }

        return $role;
    }
} 