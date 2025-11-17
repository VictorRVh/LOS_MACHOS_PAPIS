<?php

namespace App\Services;

use App\Models\Notificaciones;
use Illuminate\Support\Str;

class NotificationService
{
    public static function enviar($idUsuario, $titulo, $descripcion, $link = null)
    {
        return Notificaciones::create([
            'id' => Str::uuid(),
            'id_usuario' => $idUsuario,
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'link' => $link,
        ]);
    }
}
