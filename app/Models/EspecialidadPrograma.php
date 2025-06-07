<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class EspecialidadPrograma extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'especialidad_programa';

    protected $fillable = [
        'id',
        'id_especialidad',
        'id_programa'
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


    public function especialidadMadre()
    {
        return $this->belongsTo(EspecialidadMadre::class, 'id_especialidad');
    }

    public function programaEstudio()
    {
        return $this->belongsTo(ProgramaEstudio::class, 'id_programa');
    }

    public function modulo()
    {
        return $this->hasMany(Modulo::class, 'id_especialidad');
    }

    public function grupo()
    {
        return $this->hasMany(Grupo::class, 'id_especialidad');
    }
}
