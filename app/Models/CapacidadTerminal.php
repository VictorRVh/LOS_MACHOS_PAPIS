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
        'fecha_aplazada',
        'creditos_teoricos',
        'creditos_practicos',
        'horas',
        'id_grupo',
        'status',
        'status_nota'
    ];

    protected $casts = [
        'fecha_inicio' => 'date:Y-m-d',
        'fecha_fin' => 'date:Y-m-d',
        'fecha_aplazada' => 'date:Y-m-d',
    ];

    protected $appends = ['puede_reactivar', 'puede_aplazar', 'puede_rectificar', 'accion_disponible', 'estado_visual'];
    protected $hidden = [
        'grupo'
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

    // SOBRE EL CASO DE SUBIDA DE NOTAS DE DOCENTES
    // 0 = sin registrar
    // 1 = nota registrada
    // 2 = aplazamiento aprobado

    public function getStatusTextoAttribute()
    {
        return self::STATUS[$this->status] ?? 'Desconocido';
    }

    // public function puedeSubirNotas(): bool
    // {
    //     // Solo permitir subida si está ACTIVO
    //     if ($this->status !== self::STATUS_ACTIVO) {
    //         return false;
    //     }

    //     $ahora = Carbon::now('America/Lima');
    //     $fechaLimite = $this->fecha_limite_subida;

    //     // Permitir hasta la fecha límite (fecha_fin + 1 día a las 23:59)
    //     return $ahora->lte($fechaLimite);
    // }

    public function puedeSubirNotas(): bool
    {
        $ahora = Carbon::now('America/Lima');

        // No permitir antes de que inicie
        if ($ahora->lt(Carbon::parse($this->fecha_inicio))) {
            return false;
        }

        // Permitir hasta fecha límite (fecha_fin + 1 día)
        return $ahora->lte($this->fecha_limite_subida);
    }

    // NUEVO: Obtener fecha límite de subida
    public function getFechaLimiteSubidaAttribute()
    {
        // Fecha límite original (fecha_fin + 1 día a las 23:59)
        $limiteNormal = Carbon::parse($this->fecha_fin)
            ->timezone('America/Lima')
            ->addDay()
            ->setTime(23, 59, 59);

        // Si NO hay fecha aplazada, retornar límite normal
        if (!$this->fecha_aplazada) {
            return $limiteNormal;
        }

        $fechaAplazada = Carbon::parse($this->fecha_aplazada)
            ->timezone('America/Lima')
            ->setTime(23, 59, 59);

        // Retornar cuál fecha es mayor
        return $fechaAplazada->greaterThan($limiteNormal)
            ? $fechaAplazada
            : $limiteNormal;
    }

    // NUEVO: Obtener mensaje de estado de subida
    public function getMensajeSubidaNotasAttribute(): string
    {

        $ahora = Carbon::now('America/Lima');

        // if ($this->status === self::STATUS_PENDIENTE) {
        //     return 'La subida de notas aún no está habilitada. Inicia el ' .
        //         Carbon::parse($this->fecha_inicio)->format('d/m/Y');
        // }

        // if ($this->status === self::STATUS_FINALIZADO || $this->status === self::STATUS_COMPLETADO) {
        //     return 'El plazo para subir notas finalizó el ' .
        //         $this->fecha_limite_subida->format('d/m/Y H:i');
        // }

        // if (!$this->puedeSubirNotas()) {
        //     return 'El plazo para subir notas ha expirado.';
        // }

        // 🔹 Antes de inicio
        if ($ahora->lt(Carbon::parse($this->fecha_inicio))) {
            return 'La subida de notas aún no está habilitada. Inicia el ' .
                Carbon::parse($this->fecha_inicio)->format('d/m/Y');
        }

        // 🔹 Fuera de plazo
        if (!$this->puedeSubirNotas()) {
            return 'El plazo para subir notas finalizó el ' .
                $this->fecha_limite_subida->format('d/m/Y H:i');
        }

        return 'Puede subir notas hasta el ' .
            $this->fecha_limite_subida->format('d/m/Y H:i');
    }

    public function getPuedeReactivarAttribute()
    {
        $ahora = Carbon::now('America/Lima');
        $fechaLimite = $this->fecha_limite_subida;

        return
            $this->status_nota == 1 &&
            $ahora->lte($fechaLimite); // solo fecha, sin || status
    }

    // Puede solicitar APLAZAMIENTO (fuera del límite, sin nota)
    // public function getPuedeAplazarAttribute()
    // {
    //     $ahora = Carbon::now('America/Lima');
    //     $fechaLimite = $this->fecha_limite_subida;

    //     return
    //         $this->status_nota == 0 &&
    //         (
    //             $ahora->gt($fechaLimite)
    //             || $this->status == 4
    //         );
    // }

    public function getPuedeAplazarAttribute()
    {
        $ahora = Carbon::now('America/Lima');
        $fechaLimite = $this->fecha_limite_subida;

        return
            $this->status_nota == 0 &&
            $ahora->gt($fechaLimite); // solo fecha, sin || status
    }

    public function getPuedeRectificarAttribute()
    {
        $ahora = Carbon::now('America/Lima');
        $fechaLimite = $this->fecha_limite_subida;

        return
            $this->status_nota == 1 &&
            $ahora->gt($fechaLimite);
    }

    public function getAccionDisponibleAttribute()
    {
        if ($this->puede_aplazar) {
            return 'aplazar';
        }

        if ($this->puede_rectificar) {
            return 'rectificar';
        }

        if ($this->puede_reactivar) {
            return 'reactivar';
        }

        return null;
    }

    public static function validarRangoFechasGrupo(array $data): ?string
    {
        $grupo = Grupo::find($data['id_grupo']);

        if (!$grupo) {
            return 'El grupo no existe.';
        }

        $inicioGrupo = Carbon::parse($grupo->fecha_inicio)->startOfDay();
        $finGrupo    = Carbon::parse($grupo->fecha_entrega_acta)->endOfDay();

        $inicioCap = Carbon::parse($data['fecha_inicio']);
        $finCap    = Carbon::parse($data['fecha_fin']);

        if ($inicioCap->lt($inicioGrupo)) {
            return 'La fecha de inicio de la capacidad no puede ser menor a la fecha de inicio del grupo.';
        }

        if ($finCap->gt($finGrupo)) {
            // Aquí se agrega la fecha de entrega del acta en el mensaje de error
            return 'La fecha fin de la capacidad no puede superar la fecha de entrega de acta del grupo. La fecha de entrega de acta es: ' . $finGrupo->toDateString();
        }

        if ($finCap->lt($inicioCap)) {
            return 'La fecha fin no puede ser menor a la fecha de inicio.';
        }

        return null;
    }

    public function canEdit(): bool
    {
        $entrega = $this->grupo?->entregaDocenteActiva;

        if (!$entrega) {
            return false;
        }

        if ($entrega->estado !== EntregaDocente::STATUS_ACTIVO) {
            return false;
        }

        $now = now('America/Lima');

        $fechaInicio = $entrega->fecha_inicio;

        $fechaLimite = $entrega->fecha_aplazada
            ? $entrega->fecha_aplazada
            : $entrega->fecha_fin;

        return $now->between($fechaInicio, $fechaLimite);
    }

    public function getEstadoVisualAttribute()
    {
        if ($this->puedeSubirNotas()) {
            return [
                'texto' => 'Disponible para notas',
                'color' => 'blue'
            ];
        }

        switch ($this->status) {
            case self::STATUS_ACTIVO:
                return [
                    'texto' => 'En curso',
                    'color' => 'green'
                ];
            case self::STATUS_PENDIENTE:
                return [
                    'texto' => 'Pendiente',
                    'color' => 'yellow'
                ];
            case self::STATUS_FINALIZADO:
                return [
                    'texto' => 'Finalizado',
                    'color' => 'red'
                ];
            default:
                return [
                    'texto' => 'Desconocido',
                    'color' => 'gray'
                ];
        }
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

    public function capacidadCompetencia()
    {
        return $this->hasMany(CapacidadCompetencia::class, 'id_capacidad_terminal');
    }
}
