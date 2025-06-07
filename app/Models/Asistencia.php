<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Asistencia extends Model
{
    use HasFactory;

    protected $table = 'asistencia';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'fecha_actual', 'asistencia', 'observacion', 'id_grupo', 'id_estudiante', 'id_calendario' 
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

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'id_estudiante');
    }

    public function calendarioAdmin()
    {
        return $this->belongsTo(CalendarioAdmin::class, 'id_calendario');
    }

}
