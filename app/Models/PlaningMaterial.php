<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlaningMaterial extends Model {
    protected $table = 'material_planing';
    public $timestamps = false;
    protected $fillable = [
        'broadcast_day',
        'times_per_day',
        'material_id',
    ];
    protected $dates = [
        'broadcast_day',
    ];

    public function material() {
        return $this->belongsTo(Material::class);
    }
}
