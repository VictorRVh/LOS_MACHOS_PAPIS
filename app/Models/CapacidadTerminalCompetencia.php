<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CapacidadTerminalCompetencia extends Model
{
    use HasFactory;

    protected $table = 'capacidades_terminales_competencia';

    // UUID settings
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'id_competencia',
        'sigla',
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

    public function competencia()
    {
        return $this->belongsTo(Competencia::class, 'id_competencia');
    }
}
