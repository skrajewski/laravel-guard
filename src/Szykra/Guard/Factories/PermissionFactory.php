<?php

namespace Szykra\Guard\Factories;

use Illuminate\Support\Str;

class PermissionFactory
{

    protected $class;

    public function __construct($class)
    {
        $this->class = $class;
    }

    public function make($tag, $name = '', $description = '')
    {
        $permission = new $this->class;
        $permission->tag = $tag;
        $permission->name = $name ?: Str::title($tag);
        $permission->description = $description;
        $permission->save();

        return $permission;
    }

}
