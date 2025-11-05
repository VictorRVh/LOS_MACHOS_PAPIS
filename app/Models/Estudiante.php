<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Estudiante extends Model
{
    use HasFactory;

    protected $table = 'estudiante';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'tipo_documento',
        'nro_documento',
        'apellido_paterno',
        'apellido_materno',
        'nombre',
        'sexo',
        'pais_nacimiento',
        'departamento_nacimiento',
        'provincia_nacimiento',
        'distrito_nacimiento',
        'lugar_nacimiento',
        'direccion_residencia',
        'fecha_nacimiento',
        'estado_civil',
        'grado_instruccion',
        'trabaja',
        'detalle_trabajo',
        'carga_familiar',
        'detalle_carga_familiar',
        'correo_electronico',
        'celular_personal',
        'internet_casa',
        'tipo_internet',
        // 'tipo_operador',
        'equipos_virtuales',
        'discapacidad',
        'tipo_discapacidad',
        'celular_referencia',
        'parentesco_referencia',
        'lengua_materna',
        'anio_egreso'
    ];

    protected $casts = [
        'equipo_clases' => 'array',
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

    // Un grupo puede tener muchos estudiantes.
    // Un estudiante puede estar en muchos grupos (por ejemplo, distintos módulos o ciclos).

    public function grupos()
    {
        return $this->belongsToMany(
            Grupo::class,
            'matricula',
            'id_estudiante',
            'id_grupo'
        );
    }

    public function matricula()
    {
        return $this->hasMany(Matricula::class, 'id_estudiante');
    }

    public function asistencia()
    {
        return $this->hasMany(Asistencia::class, 'id_estudiante');
    }

    public function notaCapacidadTerminal()
    {
        return $this->hasMany(NotaCapacidadTerminal::class, 'id_estudiante');
    }

    public function notaExperienciaFormativa()
    {
        return $this->hasMany(notaExperienciaFormativa::class, 'id_estudiante');
    }

    public function egresados()
    {
        return $this->hasMany(egresados::class, 'id_estudiante');
    }
}
