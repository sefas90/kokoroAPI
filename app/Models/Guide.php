<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guide extends Model {
    use SoftDeletes;

    protected $fillable = [
        'guide_name',
        'date_ini',
        'date_end',
        'media_id',
        'campaign_id',
        'editable',
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

    const STATUS_ACTIVE = 0;
    const STATUS_FINALIZED = 1;
    const STATUS_CANCELED = 2;
}
