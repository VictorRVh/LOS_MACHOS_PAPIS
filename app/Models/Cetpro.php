<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cetpro extends Model
{
    protected $table = 'cetpros';

    protected $fillable = [
        'cetpro',
        'director',
        'anio',
        'rd_autorizacion',
        'rd_conversion',
        'ugel',
        'dre',
        'tipo_gestion',
        'region',
        'provincia',
        'distrito',
        'lugar',
        'direccion',
        'numero',
        'is_deleted',
    ];
}
