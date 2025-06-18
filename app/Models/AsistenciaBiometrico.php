<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsistenciaBiometrico extends Model
{
    use HasFactory;

    protected $table = 'asistencia_biometrico';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'fecha_actual',
        'hora',
        'tipo_registro',
        'asistencia',
        'observacion',
        'id_estudiante',
        'id_calendario',
    ];

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'id_estudiante');
    }

    public function calendario()
    {
        return $this->belongsTo(CalendarioAdmin::class, 'id_calendario');
    }
}
