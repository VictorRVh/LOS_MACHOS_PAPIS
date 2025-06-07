<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class EspecialidadMadre extends Model
{
    use HasFactory;

    protected $table = 'especialidad_madre';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'nombre_especialidad', 'id_ciclo',
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

    public function cicloAcademico()
    {
        return $this->belongsTo(CicloAcademico::class, 'id_ciclo');
    }

    public function especialidadPrograma()
    {
        return $this->hasMany(EspecialidadPrograma::class, 'id_especialidad');
    }
}
