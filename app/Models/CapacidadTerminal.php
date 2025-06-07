<?php

namespace App\Models;

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
