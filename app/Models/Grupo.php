<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Grupo extends Model
{

    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'grupo';

    protected $fillable = [
        'id',
        'id_programa',
        'id_especialidad',
        'id_modulo',
        'id_periodo',
        'id_convenio',
        'fecha_inicio',
        'fecha_fin',
        'fecha_entrega_acta',
        'seccion',
        'turno',
        'id_docente',
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

    public function programaEstudio()
    {
        return $this->belongsTo(ProgramaEstudio::class, 'id_programa');
    }

    public function especialidad()
    {
        return $this->belongsTo(EspecialidadPrograma::class, 'id_especialidad');
    }

    public function modulo()
    {
        return $this->belongsTo(Modulo::class, 'id_modulo');
    }

    public function periodo()
    {
        return $this->belongsTo(Periodo::class, 'id_periodo');
    }

    public function convenio()
    {
        return $this->belongsTo(Convenios::class, 'id_convenio');
    }

    public function docente()
    {
        return $this->belongsTo(Docente::class, 'id_docente');
    }

    public function matricula()
    {
        return $this->hasMany(Matricula::class, 'id_grupo');
    }

    public function experienciaFormativa()
    {
        return $this->hasMany(ExperienciaFormativa::class, 'id_grupo');
    }

    public function asistencia()
    {
        return $this->hasMany(Asistencia::class, 'id_grupo');
    }

    public function notaCapacidadTerminal()
    {
        return $this->hasMany(NotaCapacidadTerminal::class, 'id_grupo');
    }

    public function notaExperienciaFormativa()
    {
        return $this->hasMany(NotaExperienciaFormativa::class, 'id_grupo');
    }

    public function egresado()
    {
        return $this->hasMany(Egresados::class, 'id_grupo');
    }
}
