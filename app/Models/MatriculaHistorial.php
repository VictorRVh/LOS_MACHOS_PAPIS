<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MatriculaHistorial extends Model
{
    use HasFactory;

    protected $table = 'matricula_historial';

    protected $fillable = [
        'id_matricula',
        'estado_anterior',
        'estado_nuevo',
        'motivo',
        'id_usuario',
        'fecha_cambio',
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

    public function matricula()
    {
        return $this->belongsTo(Matricula::class, 'id_matricula');
    }
}
