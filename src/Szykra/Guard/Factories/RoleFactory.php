<?php

namespace Szykra\Guard\Factories;

use Illuminate\Support\Str;

class RoleFactory
{

    protected $class;

    public function __construct($class)
    {
        $this->class = $class;
    }

    public function make($tag, $name = '')
    {
        $role = new $this->class;
        $role->tag = $tag;
        $role->name = $name ?: Str::title($tag);
        $role->save();

        return $role;
    }

}
