<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CapacidadTerminal extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'capacidad_terminal';

    protected $fillable = [
        'id',
        'numero_capacidad',
        'nombre_capacidad',
        'fecha_inicio',
        'fecha_fin',
        'id_grupo',
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

    const STATUS_PENDIENTE   = 0;
    const STATUS_ACTIVO      = 1;
    const STATUS_DESACTIVO   = 2;
    const STATUS_ANULADO     = 3;
    const STATUS_FINALIZADO  = 4;
    const STATUS_COMPLETADO  = 5;

    const STATUS = [
        self::STATUS_PENDIENTE   => 'Pendiente',
        self::STATUS_ACTIVO      => 'Activo',
        self::STATUS_DESACTIVO   => 'Desactivo',
        self::STATUS_ANULADO     => 'Anulado',
        self::STATUS_FINALIZADO  => 'Finalizado',
        self::STATUS_COMPLETADO  => 'Completado',
    ];

    public function getStatusTextoAttribute()
    {
        return self::STATUS[$this->status] ?? 'Desconocido';
    }

    public function puedeSubirNotas(): bool
    {
        // Solo permitir subida si está ACTIVO
        if ($this->status !== self::STATUS_ACTIVO) {
            return false;
        }

        $ahora = Carbon::now('America/Lima');
        $fechaLimite = $this->fecha_limite_subida;

        // Permitir hasta la fecha límite (fecha_fin + 1 día a las 23:59)
        return $ahora->lte($fechaLimite);
    }

    // ✅ NUEVO: Obtener fecha límite de subida
    public function getFechaLimiteSubidaAttribute()
    {
        return Carbon::parse($this->fecha_fin)
            ->timezone('America/Lima')
            ->addDay()
            ->setTime(23, 59, 59);
    }

    // ✅ NUEVO: Obtener mensaje de estado de subida
    public function getMensajeSubidaNotasAttribute(): string
    {
        if ($this->status === self::STATUS_PENDIENTE) {
            return 'La subida de notas aún no está habilitada. Inicia el ' .
                Carbon::parse($this->fecha_inicio)->format('d/m/Y');
        }

        if ($this->status === self::STATUS_FINALIZADO || $this->status === self::STATUS_COMPLETADO) {
            return 'El plazo para subir notas finalizó el ' .
                $this->fecha_limite_subida->format('d/m/Y H:i');
        }

        if (!$this->puedeSubirNotas()) {
            return 'El plazo para subir notas ha expirado.';
        }

        return 'Puede subir notas hasta el ' .
            $this->fecha_limite_subida->format('d/m/Y H:i');
    }

    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'id_grupo');
    }

    public function sesiones()
    {
        return $this->hasMany(Sesiones::class, 'id_capacidad');
    }

    public function notaCapacidadTerminal()
    {
        return $this->hasMany(NotaCapacidadTerminal::class, 'id_capacidad');
    }
}
