<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EntregaDocenteAdmin;
use App\Models\EntregaDocente;
use App\Models\Periodo;
use Carbon\Carbon;

class ActualizarEstadoEntregas extends Command
{
    protected $signature = 'entregas:actualizar-estado';
    protected $description = 'Actualiza el estado de las entregas según la fecha actual y fechas aplazadas';

    public function handle()
    {
        $hoy = Carbon::now('America/Lima')->startOfMinute();

        // 1. Obtener periodos activos
        $periodos = Periodo::where('status', 1)->get();

        if ($periodos->isEmpty()) {
            $this->error('No hay un periodo académico activo. No se actualizó ningún registro.');
            return Command::FAILURE;
        }

        $this->info('📅 Total de periodos activos: ' . $periodos->count());

        $totalActualizados = 0;

        // 2. Iterar sobre cada periodo
        foreach ($periodos as $periodo) {
            $this->newLine();
            $this->info('═══════════════════════════════════════');
            $this->info('📚 Periodo: ' . ($periodo->nombre ?? $periodo->id));

            // 3. Obtener programaciones del periodo
            $programaciones = EntregaDocenteAdmin::where('id_periodo', $periodo->id)->get();

            $this->info('📊 Total de entregas administrativas: ' . $programaciones->count());

            if ($programaciones->isEmpty()) {
                $this->warn('No se encontraron entregas para este periodo.');
                continue;
            }

            // 4. Procesar cada programación administrativa
            foreach ($programaciones as $programacion) {
                $actualizados = $this->procesarProgramacion($programacion, $hoy);
                $totalActualizados += $actualizados;
            }
        }

        $this->newLine();
        $this->info("Total de entregas actualizadas: {$totalActualizados}");
        $this->info('Estados de entregas actualizados correctamente.');

        return Command::SUCCESS;
    }

    /**
     * Procesa una programación administrativa y sus entregas asociadas
     */
    protected function procesarProgramacion(EntregaDocenteAdmin $programacion, Carbon $hoy): int
    {
        $estadoAnterior = $programacion->status;
        $inicio = Carbon::parse($programacion->fecha_inicio)->timezone('America/Lima')->startOfMinute();
        $fin = Carbon::parse($programacion->fecha_fin)->timezone('America/Lima')->endOfMinute();

        // Determinar estado del admin
        $nuevoEstadoAdmin = $this->calcularEstadoAdmin($hoy, $inicio, $fin);

        // Actualizar admin si cambió
        if ($nuevoEstadoAdmin !== $estadoAnterior) {
            $programacion->update(['status' => $nuevoEstadoAdmin]);
            $this->info("Admin: {$estadoAnterior} → {$nuevoEstadoAdmin}");
        }

        // Actualizar entregas asociadas
        return $this->actualizarEntregasAsociadas($programacion, $nuevoEstadoAdmin);
    }

    /**
     * Calcula el estado administrativo según fechas
     */
    protected function calcularEstadoAdmin(Carbon $hoy, Carbon $inicio, Carbon $fin): int
    {
        if ($hoy->lt($inicio)) {
            return EntregaDocenteAdmin::STATUS_PENDIENTE;
        } elseif ($hoy->gt($fin)) {
            return EntregaDocenteAdmin::STATUS_FINALIZADO;
        } else {
            return EntregaDocenteAdmin::STATUS_ACTIVO;
        }
    }

    /**
     * Actualiza las entregas docentes según el estado del admin
     */
    protected function actualizarEntregasAsociadas(EntregaDocenteAdmin $programacion, int $nuevoEstadoAdmin): int
    {
        $totalActualizados = 0;

        // Caso 1: Admin PENDIENTE o ACTIVO → todas siguen al admin
        if (in_array($nuevoEstadoAdmin, [EntregaDocenteAdmin::STATUS_PENDIENTE, EntregaDocenteAdmin::STATUS_ACTIVO])) {
            $actualizados = EntregaDocente::where('id_admin', $programacion->id)
                ->where('estado', '!=', $nuevoEstadoAdmin)
                ->update(['estado' => $nuevoEstadoAdmin]);

            if ($actualizados > 0) {
                $this->info("{$actualizados} entregas → estado {$nuevoEstadoAdmin}");
                $totalActualizados += $actualizados;
            }
        }

        // Caso 2: Admin FINALIZADO → aplicar lógica con aplazamientos
        elseif ($nuevoEstadoAdmin === EntregaDocenteAdmin::STATUS_FINALIZADO) {
            $totalActualizados += $this->procesarEntregasFinalizadas($programacion);
        }

        return $totalActualizados;
    }

    /**
     * Procesa entregas cuando el admin está finalizado
     */
    protected function procesarEntregasFinalizadas(EntregaDocenteAdmin $programacion): int
    {
        $totalActualizados = 0;

        // 1. Entregas sin aplazamiento → FINALIZADAS
        $sinAplazamiento = EntregaDocente::where('id_admin', $programacion->id)
            ->whereNull('fecha_aplazada')
            ->where('estado', '!=', EntregaDocente::STATUS_FINALIZADO)
            ->update(['estado' => EntregaDocente::STATUS_FINALIZADO]);

        if ($sinAplazamiento > 0) {
            $this->info("{$sinAplazamiento} entregas sin aplazamiento → FINALIZADAS");
            $totalActualizados += $sinAplazamiento;
        }

        // 2. Entregas con aplazamiento → evaluar individualmente usando métodos del MODELO
        $entregasConAplazamiento = EntregaDocente::where('id_admin', $programacion->id)
            ->whereNotNull('fecha_aplazada')
            ->get();

        if ($entregasConAplazamiento->isNotEmpty()) {
            $this->info("Procesando {$entregasConAplazamiento->count()} entregas con aplazamiento:");

            foreach ($entregasConAplazamiento as $entrega) {
                // ✅ Usar el método del modelo
                if ($entrega->sincronizarEstado()) {
                    $fechaEfectiva = $entrega->obtenerFechaFinEfectiva();
                    $fechaStr = $fechaEfectiva->format('Y-m-d H:i');

                    $this->info("Grupo {$entrega->id_grupo}: {$entrega->estado_texto} (fin: {$fechaStr})");
                    $totalActualizados++;
                }
            }
        }

        return $totalActualizados;
    }
}
