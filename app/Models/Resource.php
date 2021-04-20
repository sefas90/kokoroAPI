<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model {

    public $timestamps = false;

    protected $fillable = [
        'name',
        'level',
        'url',
        'read_visible',
        'write_visible',
        'create_visible',
        'delete_visible',
        'execute_visible',
        'parent_id'
    ];

    public function permission () {
        return $this->hasMany(Permission::class);
    }
}
