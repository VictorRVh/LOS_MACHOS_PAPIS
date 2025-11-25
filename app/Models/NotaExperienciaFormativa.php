<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NotaExperienciaFormativa extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'nota_experiencia_formativa';

    protected $fillable = [
        'id',
        'id_experiencia',
        'tipo_practicas',
        'documento',
        'observacion',
        'id_estudiante',
        'id_grupo',
        'status',
    ];
    
    protected $hidden = ['created_at', 'updated_at'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    const TIPO_INTERNA = 1;
    const TIPO_EXTERNA = 2;
    const TIPO_NO_HIZO = 3;

    const TIPOS = [
        self::TIPO_INTERNA => 'PPP INTERNAS',
        self::TIPO_EXTERNA => 'PPP EXTERNAS',
        self::TIPO_NO_HIZO => 'NO HIZO PRACTICAS',
    ];

    // IMPORTANTE PARA DEVOLVER EL NOMBRE DE LOS ESTADOS MEDIANTE LAS CONSULTAS AL FRONT
    public function getTipoPracticasTextoAttribute()
    {
        return self::TIPOS[$this->tipo_practicas] ?? 'DESCONOCIDO';
    }

    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'id_grupo');
    }

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'id_estudiante');
    }

    public function experiencia()
    {
        return $this->belongsTo(ExperienciaFormativa::class, 'id_experiencia');
    }
}
