<?php namespace Szykra\Guard\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model {

    public $timestamps = false;

    public function roles()
    {
        return $this->belongsToMany('\Szykra\Guard\Models\Permission');
    }

}