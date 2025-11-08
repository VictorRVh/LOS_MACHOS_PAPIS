<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class CalendarioAdmin extends Model
{
    use HasFactory;

    protected $table = 'calendario_admin';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'id_sesion',
        'fecha',
        'laborable',
        'descripcion',
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
        0 => 'Pendiente',
        1 => 'Laborado',
        2 => 'NoLaborado',
    ];

    public function getStatusTextoAttribute()
    {
        return self::STATUS[$this->laborable] ?? 'Desconocido';
    }

    // 🔹 Una fecha pertenece a una sesión
    public function sesion()
    {
        return $this->belongsTo(Sesiones::class, 'id_sesion');
    }

    public function asistencia()
    {
        return $this->hasMany(Asistencia::class, 'id_calendario');
    }
}
