<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CarpetasEntregaDrive extends Model
{
    use HasFactory;

    protected $table = 'carpetas_entrega_drive';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_entrega_admin',
        'id_grupo',
        'drive_folder_id',
        'nombre_carpeta',
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
        return $this->belongsTo(Grupo::class, 'id_grupo');
    }

    public function entregaAdmin()
    {
        return $this->belongsTo(EntregaDocenteAdmin::class, 'id_entrega_admin');
    }
}
