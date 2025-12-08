<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sesiones;
use App\Models\CalendarioAdmin;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;

class NotificarSesionesNoTrabajadas extends Command
{
    protected $signature = 'sesiones:notificar-no-trabajadas';
    protected $description = 'Notifica a los docentes que no trabajaron sus sesiones finalizadas';

    public function handle()
    {
        // Buscar sesiones que ya pasaron su fecha_fin
        $hoy = Carbon::now()->format('Y-m-d');

        $sesionesVencidas = Sesiones::where('fecha_fin', '<', $hoy)
            ->whereIn('status', [0, 1, 3]) // Pendiente o En curso (no Trabajada ni Anulada)
            ->with(['calendarioAdmin', 'entregaDocente.grupo.docente.user', 'capacidadTerminal'])
            ->get();

        if ($sesionesVencidas->isEmpty()) {
            $this->info("No hay sesiones sin trabajar.");
            return;
        }

        $notificacionesEnviadas = 0;

        foreach ($sesionesVencidas as $sesion) {
            // Verificar si tiene al menos una fecha laborada
            $tieneFechaLaborada = $sesion->calendarioAdmin()
                ->where('laborable', 1) // Laborado
                ->exists();

            // Si tiene al menos una fecha laborada, actualizar status a "Trabajada"
            if ($tieneFechaLaborada) {
                $sesion->update(['status' => 2]); // Trabajada
                continue; // No notificar, ya fue trabajada
            }

            // Si NO tiene ninguna fecha laborada, notificar
            $docente = $sesion->entregaDocente?->grupo?->docente?->user;

            if (!$docente) {
                continue; // Si no hay docente asignado, continuar
            }

            $grupo = $sesion->entregaDocente?->grupo;
            $capacidad = $sesion->capacidadTerminal;

            // Notificar al docente
            NotificationService::enviar(
                $docente->id,
                'Sesión no trabajada',
                "No completaste ninguna fecha de la sesión '{$sesion->nombre_sesion}' del grupo {$grupo->nombre}.",
                "/sesiones/{$sesion->id}"
            );

            // Notificar a la directora
            $directores = User::whereHas('roles', function ($q) {
                $q->where('name', 'directora');
            })->get();

            foreach ($directores as $director) {
                $especialidad = $grupo->especialidad?->especialidadMadre?->nombre_especialidad ?? 'Sin especialidad';

                NotificationService::enviar(
                    $director->id,
                    'Docente no trabajó sesión',
                    "{$docente->name} {$docente->apellido_paterno} {$docente->apellido_materno} no trabajó ninguna fecha de la sesión '{$sesion->nombre_sesion}' del grupo {$especialidad} {$grupo->seccion}.",
                    "/sesiones-admin/{$sesion->id}"
                );
            }

            $notificacionesEnviadas++;
        }

        $this->info("Notificaciones enviadas: {$notificacionesEnviadas}");
    }
}
