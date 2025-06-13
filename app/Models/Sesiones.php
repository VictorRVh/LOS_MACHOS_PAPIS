<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Sesiones extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'sesiones';

    protected $fillable = [
        'id',
        'nombre_sesion',
        'fecha_inicio',
        'fecha_fin',
        'descripcion',
        'archivo_sesion',
        'id_calendario',
        'id_capacidad',
        'id_entrega',
        'status'
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

    public function calendarioAdmin()
    {
        return $this->belongsTo(CalendarioAdmin::class, 'id_calendario');
    }

    public function capacidadTerminal()
    {
        return $this->belongsTo(CapacidadTerminal::class, 'id_capacidad');
    }

    public function entregaDocente()
    {
        return $this->belongsTo(EntregaDocente::class, 'id_entrega');
    }
}
