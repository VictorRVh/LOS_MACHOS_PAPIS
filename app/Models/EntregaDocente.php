<?php

namespace App\Models;

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
}
