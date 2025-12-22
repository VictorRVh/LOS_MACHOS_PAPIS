<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Matricula extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'matricula';

    protected $fillable = [
        'id',
        'id_grupo',
        'turno',
        'id_estudiante',
        'id_pago',
        'reserva',
        'matriculado',
        'fecha_reserva'
    ];

    const STATUS_PENDIENTE              = 0;
    const STATUS_MATRICULADO            = 1;
    const STATUS_RETIRADO               = 2;
    const STATUS_RETIRADO_JUSTIFICADO   = 3;

    const STATUS = [
        self::STATUS_PENDIENTE              => 'Pendiente',
        self::STATUS_MATRICULADO            => 'Matriculado',
        self::STATUS_RETIRADO               => 'Retirado',
        self::STATUS_RETIRADO_JUSTIFICADO   => 'Retirado Justificado',
    ];

    protected $appends = ['status_texto'];

    public function getStatusTextoAttribute()
    {
        return self::STATUS[$this->matriculado] ?? 'Desconocido';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'id_grupo');
    }

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'id_estudiante');
    }

    public function pago()
    {
        return $this->belongsTo(Pago::class, 'id_pago');
    }

    public function historial()
    {
        return $this->hasMany(MatriculaHistorial::class, 'id_matricula');
    }

    public function notasCapacidades()
    {
        return $this->hasMany(
            NotaCapacidadTerminal::class,
            'id_estudiante',
            'id_estudiante'
        );
    }

    public function estudianteDocumento()
    {
        return $this->hasMany(EstudianteDocumento::class, 'id_matricula');
    }


    // FUNCION PARA REALIZAR CAMBIOS DE ESTADO Y CONECTAR CON LA NUEVA TABLA DE HISTORIAL_MATRICULA

    public function registrarCambioEstado($nuevoEstado, $motivo = null)
    {
        MatriculaHistorial::create([
            'id_matricula'   => $this->id,
            'estado_anterior' => $this->matriculado,
            'estado_nuevo'   => $nuevoEstado,
            'motivo'         => $motivo,
            // 'id_usuario'     => auth()->id(), // opcional
        ]);

        $this->update([
            'matriculado' => $nuevoEstado,
        ]);
    }
}
