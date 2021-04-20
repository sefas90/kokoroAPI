<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model {
    use SoftDeletes;

    protected $fillable = [
        'campaign_name',
        'date_ini',
        'date_end',
        'plan_id',
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
