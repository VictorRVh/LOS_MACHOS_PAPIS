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
        'fecha_inicio',
        'fecha_fin',
        'status',
        'id_periodo',
        'mostrar'
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

    public function entregaDocente()
    {
        return $this->hasMany(EntregaDocente::class, 'id_admin');
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS: Verificar estado (lectura)
    |--------------------------------------------------------------------------
    */
    public function isPendiente(): bool     { return $this->status === self::STATUS_PENDIENTE; }
    public function isActivo(): bool        { return $this->status === self::STATUS_ACTIVO; }
    public function isDesactivado(): bool   { return $this->status === self::STATUS_DESACTIVO; }
    public function isAnulado(): bool       { return $this->status === self::STATUS_ANULADO; }
    public function isFinalizado(): bool    { return $this->status === self::STATUS_FINALIZADO; }
    public function isCompletado(): bool    { return $this->status === self::STATUS_COMPLETADO; }

    /*
    |--------------------------------------------------------------------------
    | HELPERS: Cambiar estado (escritura)
    |--------------------------------------------------------------------------
    */
    public function setPendiente()   { return $this->update(['status' => self::STATUS_PENDIENTE]); }
    public function setActivo()      { return $this->update(['status' => self::STATUS_ACTIVO]); }
    public function setDesactivado() { return $this->update(['status' => self::STATUS_DESACTIVO]); }
    public function setAnulado()     { return $this->update(['status' => self::STATUS_ANULADO]); }
    public function setFinalizado()  { return $this->update(['status' => self::STATUS_FINALIZADO]); }
    public function setCompletado()  { return $this->update(['status' => self::STATUS_COMPLETADO]); }


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
