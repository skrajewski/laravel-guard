<?php

namespace Szykra\Guard\Contracts;

interface PermissionChecker {

    public function roleHasPermission($role, $permission);

}
