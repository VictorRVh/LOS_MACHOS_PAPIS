<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class EntregaDocenteAdmin extends Model
{
    use HasFactory;

    protected $table = 'entrega_docente_admin';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'tipo_entrega',
        'nombre_entrega',
        'fecha_inicio',
        'fecha_fin',
        'status',
        'id_periodo',
        'mostrar',
        'sub_grupos',
        'is_deleted'
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'status' => 'integer' // PARA RETORNAR LA COMPARACION DE FECHA DE INICIO
    ];

    protected $appends = ['status_texto'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });

        static::updating(function ($model) {

            if ($model->isDirty('fecha_fin')) {

                $fechaNueva = $model->fecha_fin;
                $fechaOriginal = $model->getOriginal('fecha_fin');
                $ahora = now('America/Lima');

                // ---- 1. Si amplió la fecha -> activar todo normal ----
                if ($fechaNueva->gt($fechaOriginal)) {

                    $model->status = self::STATUS_ACTIVO;

                    EntregaDocente::where('id_admin', $model->id)->update([
                        'fecha_fin' => $fechaNueva,
                        'estado'    => EntregaDocente::STATUS_ACTIVO,
                    ]);

                    return;
                }

                // ---- 2. Si redujo la fecha -> manejo especial con aplazamientos ----
                if ($fechaNueva->lt($fechaOriginal)) {

                    // Actualiza la fecha global
                    $model->status = $ahora->gt($fechaNueva)
                        ? self::STATUS_FINALIZADO
                        : self::STATUS_ACTIVO;

                    // Procesar docente por docente
                    EntregaDocente::where('id_admin', $model->id)->get()->each(function ($entrega) use ($fechaNueva, $ahora) {

                        // CASO 1: Tiene aplazamiento
                        if ($entrega->fecha_aplazada) {

                            $fechaAplazada = $entrega->fecha_aplazada;

                            if ($fechaAplazada->gt($ahora)) {
                                // Aplazamiento aún activo
                                $entrega->update([
                                    'fecha_fin' => $fechaNueva,
                                    'estado' => EntregaDocente::STATUS_ACTIVO,
                                ]);
                            } else {
                                // Aplazamiento vencido
                                $entrega->update([
                                    'fecha_fin' => $fechaNueva,
                                    'estado' => EntregaDocente::STATUS_FINALIZADO,
                                ]);
                            }
                        } else {
                            // CASO 2: No tiene aplazamiento → usar fecha_fin_admin
                            $entrega->update([
                                'fecha_fin' => $fechaNueva,
                                'estado' => $ahora->gt($fechaNueva)
                                    ? EntregaDocente::STATUS_FINALIZADO
                                    : EntregaDocente::STATUS_ACTIVO,
                            ]);
                        }
                    }); // end each

                } // end if fechaNueva < original
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

    /*
    |--------------------------------------------------------------------------
    | HELPERS: Verificar estado (lectura)
    |--------------------------------------------------------------------------
    */
    public function isPendiente(): bool
    {
        return $this->status === self::STATUS_PENDIENTE;
    }
    public function isActivo(): bool
    {
        return $this->status === self::STATUS_ACTIVO;
    }
    public function isDesactivado(): bool
    {
        return $this->status === self::STATUS_DESACTIVO;
    }
    public function isAnulado(): bool
    {
        return $this->status === self::STATUS_ANULADO;
    }
    public function isFinalizado(): bool
    {
        return $this->status === self::STATUS_FINALIZADO;
    }
    public function isCompletado(): bool
    {
        return $this->status === self::STATUS_COMPLETADO;
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS: Cambiar estado (escritura)
    |--------------------------------------------------------------------------
    */
    public function setPendiente()
    {
        return $this->update(['status' => self::STATUS_PENDIENTE]);
    }
    public function setActivo()
    {
        return $this->update(['status' => self::STATUS_ACTIVO]);
    }
    public function setDesactivado()
    {
        return $this->update(['status' => self::STATUS_DESACTIVO]);
    }
    public function setAnulado()
    {
        return $this->update(['status' => self::STATUS_ANULADO]);
    }
    public function setFinalizado()
    {
        return $this->update(['status' => self::STATUS_FINALIZADO]);
    }
    public function setCompletado()
    {
        return $this->update(['status' => self::STATUS_COMPLETADO]);
    }


    public function actualizarEstado()
    {
        $hoy = now();

        if ($hoy->lt($this->fecha_inicio)) {
            $this->status = self::STATUS_PENDIENTE;  // Antes de iniciar
        } elseif ($hoy->between($this->fecha_inicio, $this->fecha_fin)) {
            $this->status = self::STATUS_ACTIVO;     // En curso
        } elseif ($hoy->gt($this->fecha_fin)) {
            $this->status = self::STATUS_FINALIZADO; // Terminó el plazo
        }

        // Guardar solo si cambió el estado
        if ($this->isDirty('status')) {
            $this->save();
        }
    }
}
