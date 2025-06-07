<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProgramaEstudio extends Model
{
    use HasFactory;

    public $incrementing = false; // porque usas UUID
    protected $keyType = 'string'; // para UUIDs

    protected $table = 'programa_estudio';

    protected $fillable = [
        'id',
        'id_ciclo',
        'año',
        'numero_rd',
        'estado',
        'descripcion',
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

    public function ciclo()
    {
        return $this->belongsTo(CicloAcademico::class, 'id_ciclo');
    }

    public function grupo()
    {
        return $this->hasMany(Grupo::class, 'id_programa');
    }

    public function especialidadPrograma()
    {
        return $this->hasMany(EspecialidadPrograma::class, 'id_programa');
    }
}
