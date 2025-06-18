<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Convenios extends Model
{
    use HasFactory;

    protected $table = 'convenios';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'nombre_institucion', 'descripcion'
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

    public function convenio()
    {
        return $this->hasMany(Grupo::class, 'id_convenio');
    }
}
