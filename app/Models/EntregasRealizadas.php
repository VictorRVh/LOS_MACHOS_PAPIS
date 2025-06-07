<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EntregasRealizadas extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'entregas_realizadas';

    protected $fillable = [
        'id',
        'id_entrega',
        'id_docente',
        'archivo',
        'fecha_entrega',
        'observacion'
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
        return $this->belongsTo(EntregaDocente::class, 'id_entrega');
    }

    public function docente()
    {
        return $this->belongsTo(Docente::class, 'id_docente');
    }

}
