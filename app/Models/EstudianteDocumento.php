<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EstudianteDocumento extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'estudiante_documento';

    protected $fillable = [
        'id',
        'id_matricula',
        'tipo_documento',
        'fecha_emision',
        'id_autor',
    ];

     protected $casts = [
        'fecha_emision' => 'datetime',
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
        return $this->belongsTo(Matricula::class, 'id_matricula');
    }

    public function autor()
    {
        return $this->belongsTo(User::class, 'id_autor');
    }

}
