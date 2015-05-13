<?php

namespace Szykra\Guard\Adapters;

use Symfony\Component\Yaml\Yaml;
use Szykra\Guard\Contracts\PermissionChecker;
use Szykra\Guard\Exceptions\MissingConfigFileException;

class YamlAdapter implements PermissionChecker {

    protected static $roles = [];

    public function roleHasPermission($role, $permission)
    {
        if(empty(static::$roles)) {
            $configFile = base_path() . '/config/' . config('guard.adapter_config_file');

            if(!is_file($configFile)) {
                throw new MissingConfigFileException("Missing configuration file.");
            }

            static::$roles = Yaml::parse(file_get_contents($configFile));

        }

        return in_array($permission, static::$roles[$role]['permissions']);
    }
}