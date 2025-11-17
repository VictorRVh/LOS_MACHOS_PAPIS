<?php

namespace App\Console\Commands;

use App\Models\EntregaDocente;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class NotificarEntregasIncumplidas extends Command
{
    protected $signature = 'entregas:notificar-incumplidos';
    protected $description = 'Notifica a los docentes que no cumplieron con sus entregas finalizadas';

    public function handle()
    {
        $incumplidos = EntregaDocente::where('estado', 4)
            ->where('cumplio', 0)
            ->get();

        if ($incumplidos->isEmpty()) {
            $this->info("No hay entregas incumplidas.");
            return;
        }

        foreach ($incumplidos as $entrega) {

            $docente = $entrega->grupo->docente?->user;
            $grupo = $entrega->grupo;

            // Notificar al docente
            NotificationService::enviar(
                $docente->id,
                'Entrega no realizada',
                "No completaste la entrega del grupo {$grupo->nombre}.",
                "/entregas-docente/{$entrega->id}"
            );

            // Notificar a los coordinadores también (opcional)
            // $coordinadores = User::whereHas('roles', function ($q) {
            //     $q->where('name', 'coordinador');
            // })->get();

            //PA LA DIRECTORA
            $coordinadores = User::whereHas('roles', function ($q) {
                $q->where('name', 'directora');
            })->get();

            foreach ($coordinadores as $admin) {
                NotificationService::enviar(
                    $admin->id,
                    'Docente incumplió entrega',
                    "{$docente->name} {$docente->apellido_paterno} {$docente->apellido_materno} no cumplió la entrega del grupo {$grupo->especialidad->especialidadMadre->nombre_especialidad} {$grupo->seccion}.",
                    "/entregas-admin/{$entrega->id}"
                );
            }
        }

        $this->info("Notificaciones enviadas correctamente.");
    }
}
