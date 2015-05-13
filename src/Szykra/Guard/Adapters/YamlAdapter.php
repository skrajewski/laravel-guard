<?php

namespace Szykra\Guard\Adapters;

use Symfony\Component\Yaml\Yaml;
use Szykra\Guard\Contracts\PermissionChecker;

class YamlAdapter implements PermissionChecker {

    protected static $roles = [];

    public function roleHasPermission($role, $permission)
    {
        if(empty(static::$roles)) {
            static::$roles = Yaml::parse(file_get_contents(base_path() . '/config/' . config('guard.adapter_config_file')));
        }

        return in_array($permission, static::$roles[$role]['permissions']);
    }
}