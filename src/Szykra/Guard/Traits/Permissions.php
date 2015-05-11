<?php namespace Szykra\Guard\Traits;

use Szykra\Guard\Exceptions\MissingRoleRelationException;

trait Permissions
{

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
        $adapter = \App::make("Szykra\\Guard\\Contracts\\PermissionChecker");

        return $adapter->roleHasPermission($this->role->tag, $permission);

        // if (!$this->permissions) {
        //     $this->permissions = array_pluck($this->getPermissions(), 'tag');
        // }
        // 
        // return in_array($permission, $this->permissions);
    }

    /**
     * Permissions collection
     *
     * @return mixed
     * @throws RoleNotFoundException
     */
    private function getPermissions()
    {
        return $this->role->permissions;
    }

    /**
     * Check if relation exists in model
     *
     * @throws RoleNotFoundException
     */
    private function checkIfRelationToRoleExist()
    {
        if (!method_exists($this, 'role')) {
            throw new MissingRoleRelationException('Please set relation to role as public role() method.');
        }
    }

}
