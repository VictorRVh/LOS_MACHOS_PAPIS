<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Competencia extends Model
{
    use HasFactory;

    protected $table = 'competencias';

    // UUID settings
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'id_modulo',
        'tipo',
        'descripcion',
    ];

    /**
     * Generar UUID automáticamente
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    protected $hidden = ['created_at', 'updated_at'];

    /**
     * Relación: Competencia pertenece a un Módulo
     */
    public function modulo()
    {
        return $this->belongsTo(Modulo::class, 'id_modulo');
    }

    public function capacidadTerminalCompetencia()
    {
        return $this->hasMany(CapacidadTerminalCompetencia::class, 'id_competencia');
    }
}
