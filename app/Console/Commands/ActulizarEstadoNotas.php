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

        // Solo capacidades que pueden cambiar de estado
        $capacidades = CapacidadTerminal::whereIn('status', [
            CapacidadTerminal::STATUS_PENDIENTE,
            CapacidadTerminal::STATUS_ACTIVO,
            CapacidadTerminal::STATUS_FINALIZADO
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
            $fechaInicio = Carbon::parse($capacidad->fecha_inicio)->timezone('America/Lima')->endOfMinute();

            // 🔥 AHORA usamos el atributo virtual del modelo
            $fechaLimite = $capacidad->fecha_limite_subida;

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

            // Si no cambió, no registrar nada
            if ($nuevoEstado === $estadoAnterior) {
                continue;
            }

            // Actualizar estado
            $capacidad->update(['status' => $nuevoEstado]);
            $actualizados++;

            // Texto del estado
            $estadosTexto = CapacidadTerminal::STATUS;
            $estadoAnteriorTexto = $estadosTexto[$estadoAnterior] ?? 'Desconocido';
            $nuevoEstadoTexto    = $estadosTexto[$nuevoEstado] ?? 'Desconocido';

            // Log de consola
            $this->info("📝 Capacidad: {$capacidad->nombre_capacidad}");
            $this->info("   Grupo: {$capacidad->id_grupo}");
            $this->info("   📅 Inicio: {$fechaInicio->format('Y-m-d')}");
            $this->info("   ⏰ Límite subida: {$fechaLimite->format('Y-m-d H:i:s')}");
            $this->info("   Estado: {$estadoAnteriorTexto} → {$nuevoEstadoTexto} {$estadoTexto}");
            $this->info('');
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
