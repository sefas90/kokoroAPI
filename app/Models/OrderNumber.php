<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderNumber extends Model {
    protected $table = 'order_numbers';
    public $timestamps = false;
    protected $fillable = [
        'id',
        'order_number',
        'version',
        'observation',
        'guide_id'
    ];
}
