<?php

namespace App\Console\Commands;

use App\Models\CapacidadTerminal;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ActulizarEstadoNotas extends Command
{
    protected $signature = 'notas:actualizar-estado';
    protected $description = 'Actualiza el estado de subida de notas según fechas de capacidades terminales';

    public function handle()
    {
        $ahora = Carbon::now('America/Lima');

        $this->info('🕐 Hora actual: ' . $ahora->format('Y-m-d H:i:s'));
        $this->info('');

        // Obtener todas las capacidades terminales activas
        // $capacidades = CapacidadTerminal::where('status', 1)->get();
        $capacidades = CapacidadTerminal::whereIn('status', [
            CapacidadTerminal::STATUS_PENDIENTE,
            CapacidadTerminal::STATUS_ACTIVO
        ])->get();


        if ($capacidades->isEmpty()) {
            $this->warn('⚠️  No hay capacidades terminales activas.');
            return Command::SUCCESS;
        }

        $this->info("📚 Total de capacidades terminales: {$capacidades->count()}");
        $this->info('═══════════════════════════════════════════════');

        $actualizados = 0;

        foreach ($capacidades as $capacidad) {
            $estadoAnterior = $capacidad->status;

            // Fechas clave
            $fechaInicio = Carbon::parse($capacidad->fecha_inicio)->timezone('America/Lima')->startOfDay();
            $fechaFin = Carbon::parse($capacidad->fecha_fin)->timezone('America/Lima')->endOfDay();

            // Fecha límite: fecha_fin + 1 día a las 23:59:59
            $fechaLimite = Carbon::parse($capacidad->fecha_fin)
                ->timezone('America/Lima')
                ->addDay()
                ->setTime(23, 59, 59);

            // Determinar nuevo estado
            if ($ahora->lt($fechaInicio)) {
                $nuevoEstado = CapacidadTerminal::STATUS_PENDIENTE;
                $estadoTexto = '🔵 PENDIENTE';
            } elseif ($ahora->lte($fechaLimite)) {
                $nuevoEstado = CapacidadTerminal::STATUS_ACTIVO;
                $estadoTexto = '🟢 ACTIVO';
            } else {
                $nuevoEstado = CapacidadTerminal::STATUS_FINALIZADO;
                $estadoTexto = '🔴 FINALIZADO';
            }

            // Actualizar solo si cambió
            if ($nuevoEstado !== $estadoAnterior) {
                $capacidad->update(['status' => $nuevoEstado]);
                $actualizados++;

                $estadosTexto = CapacidadTerminal::STATUS; // ya tienes este array en el modelo

                $estadoAnteriorTexto = $estadosTexto[$estadoAnterior] ?? 'Desconocido';
                $nuevoEstadoTexto = $estadosTexto[$nuevoEstado] ?? 'Desconocido';

                $this->info("📝 Capacidad: {$capacidad->nombre_capacidad}");
                $this->info("   Grupo: {$capacidad->id_grupo}");
                $this->info("   📅 Inicio: {$fechaInicio->format('Y-m-d')}");
                $this->info("   📅 Fin: {$fechaFin->format('Y-m-d')}");
                $this->info("   ⏰ Límite subida: {$fechaLimite->format('Y-m-d H:i:s')}");
                $this->info("   Estado: {$estadoAnteriorTexto} → {$nuevoEstadoTexto} {$estadoTexto}");
                $this->info('');
            }
        }

        $this->info('═══════════════════════════════════════════════');

        if ($actualizados > 0) {
            $this->info("✅ {$actualizados} capacidades actualizadas correctamente.");
        } else {
            $this->info('✓ No hubo cambios de estado.');
        }

        return Command::SUCCESS;
    }
}
