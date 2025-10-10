<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EntregaDocenteAdmin;
use App\Models\EntregaDocente;
use Carbon\Carbon;

class ActualizarEstadoEntregas extends Command
{
    // Nombre con el que lo usaremos en el scheduler
    protected $signature = 'entregas:actualizar-estado';

    protected $description = 'Actualiza el estado de las entregas según la fecha actual';

    public function handle()
    {
        $hoy = Carbon::today();

        // Obtener todas las programaciones
        $programaciones = EntregaDocenteAdmin::all();

        foreach ($programaciones as $programacion) {

            $estadoAnterior = $programacion->status;

            if ($hoy->lt($programacion->fecha_inicio)) {
                $programacion->status = EntregaDocenteAdmin::STATUS_PENDIENTE;
            } elseif ($hoy->between($programacion->fecha_inicio, $programacion->fecha_fin)) {
                $programacion->status = EntregaDocenteAdmin::STATUS_ACTIVO;
            } elseif ($hoy->gt($programacion->fecha_fin)) {
                $programacion->status = EntregaDocenteAdmin::STATUS_FINALIZADO;
            }

            // Solo guardar si cambió
            if ($programacion->status !== $estadoAnterior) {
                $programacion->save();

                // REPERCUTIR CAMBIO EN LOS HIJOS
                EntregaDocente::where('id_admin', $programacion->id)
                    ->update(['estado' => $programacion->status]);
            }
        }

        $this->info('Estados actualizados correctamente.');
    }
}
