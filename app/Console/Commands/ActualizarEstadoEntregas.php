<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EntregaDocenteAdmin;
use App\Models\EntregaDocente;
use App\Models\Periodo;
use Carbon\Carbon;

class ActualizarEstadoEntregas extends Command
{
    // Nombre con el que lo usaremos en el scheduler
    protected $signature = 'entregas:actualizar-estado';
    protected $description = 'Actualiza el estado de las entregas según la fecha actual y fechas aplazadas';

    public function handle()
    {
        $hoy = Carbon::now('America/Lima')->startOfMinute();

        // 🔹 Obtener TODOS los periodos activos
        $periodos = Periodo::where('status', 1)->get();

        if ($periodos->isEmpty()) {
            $this->error('No hay un periodo académico activo. No se actualizó ningún registro.');
            return Command::FAILURE;
        }

        $this->info('📅 Total de periodos activos: ' . $periodos->count());

        // 🔁 Iterar sobre cada periodo
        foreach ($periodos as $periodo) {
            $this->info('');
            $this->info('═══════════════════════════════════════');
            $this->info('📚 Periodo: ' . ($periodo->nombre ?? $periodo->id));

            // 🔹 Buscar todas las programaciones de ESTE periodo
            $programaciones = EntregaDocenteAdmin::where('id_periodo', $periodo->id)->get();

            $this->info('📊 Total de entregas encontradas: ' . $programaciones->count());

            if ($programaciones->isEmpty()) {
                $this->warn('No se encontraron entregas para este periodo.');
                continue; // Pasar al siguiente periodo
            }

            foreach ($programaciones as $programacion) {
                $estadoAnterior = $programacion->status;
                $inicio = Carbon::parse($programacion->fecha_inicio)->timezone('America/Lima');
                $fin = Carbon::parse($programacion->fecha_fin)->timezone('America/Lima');

                // Determinar estado del admin
                if ($hoy->lt($inicio)) {
                    $nuevoEstado = EntregaDocenteAdmin::STATUS_PENDIENTE;
                } elseif ($hoy->gt($fin)) {
                    $nuevoEstado = EntregaDocenteAdmin::STATUS_FINALIZADO;
                } else {
                    $nuevoEstado = EntregaDocenteAdmin::STATUS_ACTIVO;
                }

                // Actualizar admin si cambió
                if ($nuevoEstado !== $estadoAnterior) {
                    $programacion->update(['status' => $nuevoEstado]);
                    $this->info("      📝 Admin: {$estadoAnterior} → {$nuevoEstado}");
                }

                // 🎯 LÓGICA MEJORADA: Actualizar entregas según el estado actual

                // 1️⃣ Si está PENDIENTE o ACTIVO: todas las entregas siguen al admin
                if (
                    $nuevoEstado === EntregaDocenteAdmin::STATUS_PENDIENTE ||
                    $nuevoEstado === EntregaDocenteAdmin::STATUS_ACTIVO
                ) {

                    $actualizados = EntregaDocente::where('id_admin', $programacion->id)
                        ->update(['estado' => $nuevoEstado]);

                    if ($actualizados > 0) {
                        $this->info("      ✅ {$actualizados} entregas actualizadas → estado {$nuevoEstado}");
                    }
                }

                // 2️⃣ Si está FINALIZADO: aplicar lógica híbrida con aplazamientos
                elseif ($nuevoEstado === EntregaDocenteAdmin::STATUS_FINALIZADO) {

                    // Entregas SIN aplazamiento → FINALIZADAS
                    $sinAplazamiento = EntregaDocente::where('id_admin', $programacion->id)
                        ->whereNull('fecha_aplazada')
                        ->update(['estado' => EntregaDocente::STATUS_FINALIZADO]);

                    if ($sinAplazamiento > 0) {
                        $this->info("      ✅ {$sinAplazamiento} entregas sin aplazamiento → FINALIZADAS");
                    }

                    // Entregas CON aplazamiento → evaluar según fecha
                    $entregasConAplazamiento = EntregaDocente::where('id_admin', $programacion->id)
                        ->whereNotNull('fecha_aplazada')
                        ->get();

                    if ($entregasConAplazamiento->isNotEmpty()) {
                        $this->info("      📌 Procesando {$entregasConAplazamiento->count()} entregas con aplazamiento:");

                        foreach ($entregasConAplazamiento as $entrega) {
                            $estadoAnteriorEntrega = $entrega->estado;
                            $fechaAplazada = Carbon::parse($entrega->fecha_aplazada)->timezone('America/Lima');

                            // Usar la fecha que sea MAYOR
                            $fechaEfectiva = $fechaAplazada->gt($fin) ? $fechaAplazada : $fin;

                            // Si la fecha efectiva ya pasó → FINALIZADA, sino → ACTIVA
                            $nuevoEstadoEntrega = $hoy->gt($fechaEfectiva)
                                ? EntregaDocente::STATUS_FINALIZADO
                                : EntregaDocente::STATUS_ACTIVO;

                            if ($nuevoEstadoEntrega !== $estadoAnteriorEntrega) {
                                $entrega->estado = $nuevoEstadoEntrega;
                                $entrega->save();

                                $tipoFecha = $fechaAplazada->gt($fin) ? "aplazada" : "general";
                                $fechaStr = $fechaEfectiva->format('Y-m-d H:i');

                                $this->info("         🔄 Grupo {$entrega->id_grupo}: {$estadoAnteriorEntrega} → {$nuevoEstadoEntrega} (fecha {$tipoFecha}: {$fechaStr})");
                            }
                        }
                    }
                }
            }
        }

        $this->info('');
        $this->info('🎯 Estados de entregas actualizados correctamente.');
        return Command::SUCCESS;
    }
}
