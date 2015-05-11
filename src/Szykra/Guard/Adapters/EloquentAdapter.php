<?php

namespace Szykra\Guard\Adapters;

use Szykra\Guard\Contracts\PermissionChecker;
use Szykra\Guard\Models\Role;

class EloquentAdapter implements PermissionChecker {

    protected static $permissions = [];

    public function roleHasPermission($role, $permission)
    {
        if(array_key_exists($role, static::$permissions)) {
            $roleModel = static::$permissions[$role];
        } else {
            $roleModel = Role::with("permissions")->where("tag", "=", $role)->firstOrFail();
            static::$permissions[$role] = $roleModel;
        }

        return $roleModel->permissions->filter(function($element) use($permission) {
            return $element->tag === $permission;
        });
    }

}
