<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class EntregaDocenteAdmin extends Model
{
    use HasFactory;

    protected $table = 'entrega_docente_admin';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'tipo_entrega', 'fecha_inicio', 'fecha_fin', 'estado'
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

    public function entregaDocente()
    {
        return $this->hasMany(entregaDocente::class, 'id_admin');
    }
}

