<?php

namespace Szykra\Guard\Adapters;

use Szykra\Guard\Contracts\PermissionChecker;
use Szykra\Guard\Models\Role;

class EloquentAdapter implements PermissionChecker {

    protected static $permissions = [];

    protected $roleObj;

    public function __construct($className)
    {
        $this->roleObj = new $className;
    }

    /**
     * Checks if role has permission
     *
     * @param $role
     * @param $permission
     * @return mixed
     */
    public function roleHasPermission($role, $permission)
    {
        if($this->permissionsAreLoaded($role)) {
            $roleModel = static::$permissions[$role];
        } else {
            $roleModel = $this->getRole($role);

            static::$permissions[$role] = $roleModel;
        }

        return $this->permissionExists($roleModel, $permission);
    }

    /**
     * Checks if role is currently loaded
     *
     * @param $role
     * @return bool
     */
    private function permissionsAreLoaded($role)
    {
        return array_key_exists($role, static::$permissions);
    }

    /**
     * Retrieve role from database
     *
     * @param $roleName
     * @return
     */
    private function getRole($roleName)
    {
        return $this->roleObj->with("permissions")->where("tag", "=", $roleName)->firstOrFail();
    }

    /**
     * Check if permission belongs to role
     * @param $role
     * @param $permission
     * @return mixed
     */
    private function permissionExists($role, $permission)
    {
        $requiredPermission = $role->permissions->filter(function($element) use($permission) {
            return $element->tag === $permission;
        });

        return (bool) $requiredPermission->count();
    }

}
