<?php

namespace App\Models;

use App\Http\Controllers\NotaExperienciaFormativaController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CarpetasPracticasDrive extends Model
{
    use HasFactory;

    protected $table = 'carpetas_practicas_drive';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_nota_experiencia',
        'id_estudiante',
        'drive_file_id',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function grupo()
    {
        return $this->belongsTo(NotaExperienciaFormativa::class, 'id_nota_experiencia');
    }
}
