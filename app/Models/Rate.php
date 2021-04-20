<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rate extends Model {
    use SoftDeletes;

    protected $fillable = [
        'show',
        'media_id',
        'hour_ini',
        'hour_end',
        'brod_mo',
        'brod_tu',
        'brod_we',
        'brod_th',
        'brod_fr',
        'brod_sa',
        'brod_su',
        'cost',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $dates = [
        'date_ini',
        'date_end',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
