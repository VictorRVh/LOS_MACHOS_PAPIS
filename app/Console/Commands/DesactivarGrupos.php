<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Grupo;
use Carbon\Carbon;

class DesactivarGrupos extends Command
{
    /**
     * El nombre y firma del comando.
     */
    protected $signature = 'grupos:desactivar';

    /**
     * La descripción del comando.
     */
    protected $description = 'Desactiva grupos cuyo fecha_fin ya terminó (status = 2)';

    /**
     * Lógica del comando.
     */
    public function handle()
    {
        $hoy = Carbon::today();

        // Buscar grupos activos cuya fecha_fin ya terminó
        $grupos = Grupo::where('status', 1) // solo los activos
                        ->whereDate('fecha_fin', '<', $hoy)
                        ->get();

        if ($grupos->isEmpty()) {
            $this->info('No hay grupos para desactivar.');
            return;
        }

        foreach ($grupos as $grupo) {
            $grupo->status = 2; // desactivo
            $grupo->save();
        }

        $this->info('Se desactivaron ' . $grupos->count() . ' grupos.');
    }
}
