<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Modulo extends Model
{
    use HasFactory;

    public $incrementing = false; 
    protected $keyType = 'string'; 

    protected $table = 'modulos';

    protected $fillable = [
        'id',
        'numero_modulo',
        'descripcion',
        'creditos',
        'horas',
        'id_especialidad',
        'id_periodo'
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


    public function periodo()
    {
        return $this->belongsTo(Periodo::class, 'id_periodo');
    }

    public function especialidadPrograma()
    {
        return $this->belongsTo(EspecialidadPrograma::class, 'id_especialidad');
    }

    public function grupo()
    {
        return $this->hasMany(Grupo::class, 'id_modulo');
    }
}
