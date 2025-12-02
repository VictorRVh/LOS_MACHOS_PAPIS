<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EntregaDocente extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'entrega_docente';

    protected $fillable = [
        'id',
        'id_grupo',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'id_admin',
        'documento_admin',
        'observacion',
        'cumplio',
        'fecha_aplazada',
        'dias_aplazados'
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'fecha_aplazada' => 'datetime',
        'estado' => 'integer'
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

    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'id_grupo');
    }

    public function entregaDocenteAdmin()
    {
        return $this->belongsTo(EntregaDocenteAdmin::class, 'id_admin');
    }

    public function entregaRealizada()
    {
        return $this->hasMany(EntregasRealizadas::class, 'id_entrega');
    }

    public function sesiones()
    {
        return $this->hasMany(Sesiones::class, 'id_entrega');
    }

    public function carpetas()
    {
        return $this->hasMany(CarpetasEntregaDrive::class, 'id_entrega_docente');
    }
    public function carpeta()
    {
        return $this->hasOne(CarpetasEntregaDrive::class, 'id_entrega_docente');
    }

    // HELPERS Y SCOPES RECOMENDADO POR MI CHERO 

    public function getEstadoTextoAttribute()
    {
        return self::STATUS[$this->estado] ?? 'Desconocido';
    }

    public function getFechaFinEfectivaAttribute()
    {
        return $this->obtenerFechaFinEfectiva()->format('Y-m-d H:i:s');
    }

    // ========== MÉTODOS DE VALIDACIÓN DE ESTADO ==========

    /**
     * Verifica si la entrega está actualmente activa para subir archivos
     * 
     * @return array ['activa' => bool, 'mensaje' => string]
     */
    public function puedeSubirArchivo(): array
    {
        $ahora = Carbon::now('America/Lima')->startOfMinute();

        // 1. Verificar estado actual
        if ($this->estado != self::STATUS_ACTIVO) {
            return [
                'activa' => false,
                'mensaje' => 'La entrega no está activa. No puede subir archivos.',
                'codigo' => 'ESTADO_INACTIVO'
            ];
        }

        // 2. Verificar si ya cumplió
        if ($this->cumplio) {
            return [
                'activa' => true,
                'mensaje' => 'Ya ha realizado esta entrega previamente.',
                'codigo' => 'YA_CUMPLIDA'
            ];
        }

        // 3. Verificar rango de fechas
        $fechaInicio = Carbon::parse($this->fecha_inicio)->timezone('America/Lima')->startOfMinute();
        $fechaFin = $this->obtenerFechaFinEfectiva();

        if ($ahora->lt($fechaInicio)) {
            return [
                'activa' => false,
                'mensaje' => 'La entrega aún no ha comenzado. Inicia el ' . $fechaInicio->format('d/m/Y H:i'),
                'codigo' => 'NO_INICIADA',
                'fecha_inicio' => $fechaInicio->toIso8601String()
            ];
        }

        if ($ahora->gt($fechaFin)) {
            return [
                'activa' => false,
                'mensaje' => 'La entrega ha finalizado. Venció el ' . $fechaFin->format('d/m/Y H:i'),
                'codigo' => 'FINALIZADA',
                'fecha_fin' => $fechaFin->toIso8601String()
            ];
        }

        return [
            'activa' => true,
            'mensaje' => 'Puede realizar la entrega hasta el ' . $fechaFin->format('d/m/Y H:i'),
            'codigo' => 'ACTIVA',
            'fecha_fin' => $fechaFin->toIso8601String(),
            'tiempo_restante' => $this->obtenerTiempoRestante()
        ];
    }

    /**
     * Obtiene la fecha de fin efectiva considerando aplazamientos
     * 
     * @return Carbon
     */
    public function obtenerFechaFinEfectiva(): Carbon
    {
        $fechaFin = Carbon::parse($this->fecha_fin)->timezone('America/Lima')->endOfMinute();

        if ($this->fecha_aplazada) {
            $fechaAplazada = Carbon::parse($this->fecha_aplazada)->timezone('America/Lima')->endOfMinute();

            // Retornar la fecha mayor
            return $fechaAplazada->gt($fechaFin) ? $fechaAplazada : $fechaFin;
        }

        return $fechaFin;
    }

    /**
     * Calcula el estado correcto según la fecha actual
     * 
     * @return int Estado calculado
     */
    public function calcularEstado(): int
    {
        $ahora = Carbon::now('America/Lima')->startOfMinute();
        $fechaInicio = Carbon::parse($this->fecha_inicio)->timezone('America/Lima')->startOfMinute();
        $fechaFin = $this->obtenerFechaFinEfectiva();

        if ($ahora->lt($fechaInicio)) {
            return self::STATUS_PENDIENTE;
        }

        if ($ahora->gt($fechaFin)) {
            return self::STATUS_FINALIZADO;
        }

        return self::STATUS_ACTIVO;
    }

    /**
     * Verifica si el estado necesita actualizarse
     * 
     * @return bool
     */
    public function necesitaActualizarEstado(): bool
    {
        return $this->calcularEstado() !== $this->estado;
    }

    /**
     * Actualiza el estado si es necesario
     * 
     * @return bool True si se actualizó
     */
    public function sincronizarEstado(): bool
    {
        if ($this->necesitaActualizarEstado()) {
            $this->estado = $this->calcularEstado();
            return $this->save();
        }

        return false;
    }

    /**
     * Obtiene información detallada del estado
     * 
     * @return array
     */
    public function obtenerInfoEstado(): array
    {
        $ahora = Carbon::now('America/Lima');
        $fechaFin = $this->obtenerFechaFinEfectiva();
        $tieneAplazamiento = !is_null($this->fecha_aplazada);

        return [
            'estado_actual' => $this->estado,
            'estado_texto' => $this->estado_texto,
            'estado_calculado' => $this->calcularEstado(),
            'fecha_inicio' => Carbon::parse($this->fecha_inicio)->format('Y-m-d H:i:s'),
            'fecha_fin_original' => Carbon::parse($this->fecha_fin)->format('Y-m-d H:i:s'),
            'fecha_fin_efectiva' => $fechaFin->format('Y-m-d H:i:s'),
            'tiene_aplazamiento' => $tieneAplazamiento,
            'fecha_aplazada' => $tieneAplazamiento ? Carbon::parse($this->fecha_aplazada)->format('Y-m-d H:i:s') : null,
            'tiempo_restante' => $this->obtenerTiempoRestante(),
            'ha_cumplido' => $this->cumplio,
        ];
    }

    /**
     * Obtiene el tiempo restante en formato legible
     * 
     * @return array
     */
    public function obtenerTiempoRestante(): array
    {
        $ahora = Carbon::now('America/Lima');
        $fechaFin = $this->obtenerFechaFinEfectiva();

        $diff = $ahora->diff($fechaFin);

        return [
            'dias' => $diff->days,
            'horas' => $diff->h,
            'minutos' => $diff->i,
            'total_horas' => $ahora->diffInHours($fechaFin, false),
            'es_pasado' => $ahora->gt($fechaFin),
            'texto' => $ahora->diffForHumans($fechaFin, [
                'syntax' => Carbon::DIFF_ABSOLUTE,
                'parts' => 2
            ])
        ];
    }

    /**
     * Marca la entrega como cumplida
     * 
     * @param int $idDocente
     * @return EntregasRealizadas
     */
    public function marcarComoCumplida(string $idDocente, string $fileId): EntregasRealizadas
    {
        // Actualizar campo cumplio
        // $this->update(['cumplio' => 1]);

        if (!$this->cumplio) {
            $this->update(['cumplio' => 1]);
        }

        // Crear registro en entregas_realizadas
        return EntregasRealizadas::create([
            'id_entrega' => $this->id,
            'id_docente' => $idDocente,
            'archivo'    => $fileId,
            'fecha_entrega' => Carbon::now('America/Lima'),
        ]);
    }

    // ========== SCOPES ==========

    /**
     * Scope para obtener entregas activas
     */
    public function scopeActivas($query)
    {
        return $query->where('estado', self::STATUS_ACTIVO);
    }

    /**
     * Scope para obtener entregas pendientes
     */
    public function scopePendientes($query)
    {
        return $query->where('estado', self::STATUS_PENDIENTE);
    }

    /**
     * Scope para obtener entregas finalizadas
     */
    public function scopeFinalizadas($query)
    {
        return $query->where('estado', self::STATUS_FINALIZADO);
    }

    /**
     * Scope para obtener entregas con aplazamiento
     */
    public function scopeConAplazamiento($query)
    {
        return $query->whereNotNull('fecha_aplazada');
    }

    /**
     * Scope para obtener entregas sin cumplir
     */
    public function scopeSinCumplir($query)
    {
        return $query->where('cumplio', false);
    }

    /**
     * Scope para obtener entregas de un periodo
     */
    public function scopeDePeriodo($query, $idPeriodo)
    {
        return $query->whereHas('entregaDocenteAdmin', function ($q) use ($idPeriodo) {
            $q->where('id_periodo', $idPeriodo);
        });
    }
}
