<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'condicion',
        'nro_recibo',
        'aporte',
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
        return $this->hasMany(Matricula::class, 'id_pago');
    }
}
