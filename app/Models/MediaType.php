<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaType extends Model {
    protected $fillable = [
        'id',
        'media_type',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
}
