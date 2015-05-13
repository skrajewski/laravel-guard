<?php namespace Szykra\Guard\Traits;

trait Permissions
{

    static protected $permissionCheckerAdapter;

    /**
     * Buffer for permissions
     *
     * @var array
     */
    protected $permissions = [];

    /**
     * Check if user has specific role
     *
     * @param $role
     * @return bool
     */
    public function is($role)
    {
        return $this->role->tag == $role;
    }

    /**
     * Check if user has specific permission
     *
     * @param $permission
     * @return bool
     */
    public function can($permission)
    {
        if (!static::$permissionCheckerAdapter) {
            static::$permissionCheckerAdapter = \App::make("Szykra\\Guard\\Contracts\\PermissionChecker");
        }

        return static::$permissionCheckerAdapter->roleHasPermission($this->role->tag, $permission);
    }

}
