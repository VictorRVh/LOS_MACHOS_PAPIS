<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class Periodo extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'periodo';

    protected $fillable = [
        'id',
        'nombre_periodo',
        'status',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    const STATUS = [
        0 => 'Pendiente',    // 00
        1 => 'Activo',       // 01
        2 => 'Desactivo',    // 10
        3 => 'Anulado',      // 11
    ];

    public function getStatusTextoAttribute()
    {
        return self::STATUS[$this->status] ?? 'Desconocido';
    }

    public function modulo()
    {
        return $this->hasMany(Modulo::class, 'id_periodo');
    }

    public function grupo()
    {
        return $this->hasMany(Grupo::class, 'id_periodo');
    }
}
