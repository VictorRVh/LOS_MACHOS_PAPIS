<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Asistencia extends Model
{
    use HasFactory;

    protected $table = 'asistencia';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'fecha_actual',
        'asistencia',
        'observacion',
        'id_grupo',
        'id_estudiante',
        'id_calendario'
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
        1 => 'Asistió',
        2 => 'Inasistencia',
        3 => 'Tardanza',
        4 => 'Permiso',
        5 => 'Retirado',
    ];

    public function getStatusTextoAttribute()
    {
        return self::STATUS[$this->asistencia] ?? 'Desconocido';
    }

    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'id_grupo');
    }

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'id_estudiante');
    }

    public function calendarioAdmin()
    {
        return $this->belongsTo(CalendarioAdmin::class, 'id_calendario');
    }
}
