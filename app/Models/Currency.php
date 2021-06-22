<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Currency extends Model {
    use SoftDeletes;

    protected $fillable = [
        'currency_name',
        'currency_value',
        'symbol',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
}
