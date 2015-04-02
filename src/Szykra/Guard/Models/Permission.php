<?php namespace Szykra\Guard\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model {

    public $fillable = ['tag', 'name', 'description'];

    public $timestamps = false;

    public function roles()
    {
        return $this->belongsToMany('\Szykra\Guard\Models\Role');
    }

}