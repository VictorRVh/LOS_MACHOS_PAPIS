<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class CicloAcademico extends Model
{
    use HasFactory;

    protected $table = 'ciclo_academico';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'nombre_ciclo', 'descripcion'
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


    public function programaEstudio()
    {
        return $this->hasMany(ProgramaEstudio::class, 'id_ciclo');
    }
}

