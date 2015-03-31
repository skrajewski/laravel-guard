<?php namespace Szykra\Guard\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model {

    public $fillable = ['tag', 'name'];

    public $timestamps = false;
    
    public function permissions()
    {
        return $this->belongsToMany('\Szykra\Guard\Models\Permission');
    }

}