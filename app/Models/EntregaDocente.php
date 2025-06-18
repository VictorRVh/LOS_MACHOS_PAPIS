<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EntregaDocente extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'entrega_docente';

    protected $fillable = [
        'id',
        'id_grupo',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'id_admin',
        'documento_admin'
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

    public function entregaDocenteAdmin()
    {
        return $this->belongsTo(entregaDocenteAdmin::class, 'id_admin');
    }

    public function entregaRealizada()
    {
        return $this->hasMany(EntregasRealizadas::class, 'id_entrega');
    }

    public function sesiones()
    {
        return $this->hasMany(Sesiones::class, 'id_entrega');
    }
}
