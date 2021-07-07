<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model {
    use SoftDeletes;

    protected $fillable = [
        'campaign_name',
        'product',
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

    const CAMPAIGN_JAN = 'Enero';
    const CAMPAIGN_FEB = 'Febrero';
    const CAMPAIGN_MAR = 'Marzo';
    const CAMPAIGN_APR = 'Abril';
    const CAMPAIGN_MAY = 'Mayo';
    const CAMPAIGN_JUN = 'Junio';
    const CAMPAIGN_JUL = 'Julio';
    const CAMPAIGN_AUG = 'Agosto';
    const CAMPAIGN_SEP = 'Septiembre';
    const CAMPAIGN_OCT = 'Octubre';
    const CAMPAIGN_NOV = 'Noviembre';
    const CAMPAIGN_DEC = 'Diciembre';
}
