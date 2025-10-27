<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EntregaDocenteAdmin;
use App\Models\EntregaDocente;
<<<<<<< HEAD
=======
use App\Models\Periodo;
>>>>>>> f2878b34cbce9301735378d1394f4c2bf1f1243e
use Carbon\Carbon;

class ActualizarEstadoEntregas extends Command
{
    // Nombre con el que lo usaremos en el scheduler
    protected $signature = 'entregas:actualizar-estado';

    protected $description = 'Actualiza el estado de las entregas según la fecha actual';

    public function handle()
    {
        $hoy = Carbon::now('America/Lima')->startOfMinute();

<<<<<<< HEAD
        $programaciones = EntregaDocenteAdmin::all();
=======
        $periodo = Periodo::where('status', 1)->first();

        if (!$periodo) {
            $this->error('No hay un periodo académico activo. No se actualizó ningún registro.');
            return Command::FAILURE;
        }

        $this->info('Actualizando entregas del periodo: ' . $periodo->nombre ?? $periodo->id);

        $programaciones = EntregaDocenteAdmin::where('id_periodo', $periodo->id)
            ->get();

        if ($programaciones->isEmpty()) {
            $this->warn('No se encontraron entregas para el periodo actual.');
            return Command::SUCCESS;
        }
>>>>>>> f2878b34cbce9301735378d1394f4c2bf1f1243e

        foreach ($programaciones as $programacion) {

            $estadoAnterior = $programacion->status;

            $inicio = Carbon::parse($programacion->fecha_inicio)->timezone('America/Lima')->startOfMinute();
            $fin = Carbon::parse($programacion->fecha_fin)->timezone('America/Lima')->endOfMinute();

<<<<<<< HEAD
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
=======
            $nuevoEstado = match (true) {
                $hoy->lt($inicio) => EntregaDocenteAdmin::STATUS_PENDIENTE,
                $hoy->between($inicio, $fin) => EntregaDocenteAdmin::STATUS_ACTIVO,
                default => EntregaDocenteAdmin::STATUS_FINALIZADO,
            };

            if ($nuevoEstado !== $estadoAnterior) {
                $programacion->timestamps = false;
                $programacion->update(['status' => $nuevoEstado]);
                $programacion->timestamps = true;

                EntregaDocente::where('id_admin', $programacion->id)
                    ->update(['estado' => $nuevoEstado]);

                $this->info("{$programacion->tipo_entrega} → Estado actualizado a {$nuevoEstado}");
>>>>>>> f2878b34cbce9301735378d1394f4c2bf1f1243e
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

<<<<<<< HEAD
        $this->info('Estados actualizados correctamente.');
=======
        $this->info('🎯 Estados de entregas actualizados correctamente.');
        return Command::SUCCESS;
>>>>>>> f2878b34cbce9301735378d1394f4c2bf1f1243e
    }
}
