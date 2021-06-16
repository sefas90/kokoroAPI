<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlaningAuspiceMaterial extends Model {
    protected $table = 'material_auspice_planing';
    public $timestamps = false;
    protected $fillable = [
        'broadcast_day',
        'times_per_day',
        'material_auspice_id',
    ];
    protected $dates = [
        'broadcast_day',
    ];
}
