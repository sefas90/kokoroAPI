<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model {
    use SoftDeletes;
    protected $table = 'materials';

    protected $fillable = [
        'material_name',
        'rate_id',
        'total_cost',
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
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function planingMaterial() {
        return $this->hasMany(PlaningMaterial::class);
    }
}
