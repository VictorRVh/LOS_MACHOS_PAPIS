<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class EgresadoDocumento extends Model
{
    use HasUuids;

    protected $table = 'egresado_documento';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_egresado',
        'tipo_documento',
        'fecha_emision',
        'id_autor',
        'codigo_institucion',
        'codigo_ugel',
        'codigo',
        'duplicado'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function egresado()
    {
        return $this->belongsTo(Egresado::class, 'id_egresado');
    }

    public function autor()
    {
        return $this->belongsTo(User::class, 'id_autor');
    }
}