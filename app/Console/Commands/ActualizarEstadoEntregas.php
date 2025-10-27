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
        $hoy = Carbon::now('America/Lima')->startOfMinute();

        $programaciones = EntregaDocenteAdmin::all();

        foreach ($programaciones as $programacion) {

            $estadoAnterior = $programacion->status;

            $inicio = Carbon::parse($programacion->fecha_inicio)->timezone('America/Lima')->startOfMinute();
            $fin = Carbon::parse($programacion->fecha_fin)->timezone('America/Lima')->endOfMinute();

            if ($hoy->lt($inicio)) {
                $nuevoEstado = EntregaDocenteAdmin::STATUS_PENDIENTE;
            } elseif ($hoy->between($inicio, $fin)) {
                $nuevoEstado = EntregaDocenteAdmin::STATUS_ACTIVO;
            } else {
                $nuevoEstado = EntregaDocenteAdmin::STATUS_FINALIZADO;
            }

            if ($nuevoEstado !== $estadoAnterior) {
                $programacion->status = $nuevoEstado;
                $programacion->save();

                EntregaDocente::where('id_admin', $programacion->id)
                    ->update(['estado' => $nuevoEstado]);
            }

            // DEBUG
            $this->info('-----------------------');
            $this->info('Hora actual: ' . $hoy);
            $this->info('Inicio: ' . $inicio);
            $this->info('Fin: ' . $fin);
            $this->info('Comparación: ');
            $this->info('hoy < inicio ? ' . ($hoy->lt($inicio) ? 'SI' : 'NO'));
            $this->info('hoy between ? ' . ($hoy->between($inicio, $fin) ? 'SI' : 'NO'));
            $this->info('hoy > fin ? ' . ($hoy->gt($fin) ? 'SI' : 'NO'));
            $this->info('-----------------------');
        }

        $this->info('Estados actualizados correctamente.');
    }
}
