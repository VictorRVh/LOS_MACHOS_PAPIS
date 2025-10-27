<?php

namespace App\Http\Controllers;

use App\Models\EntregaDocenteAdmin;
use App\Models\ActividadesRecientes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GoogleCalendarWebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('--- WEBHOOK DE GOOGLE CALENDAR RECIBIDO ---');
        
        $now = Carbon::now('America/Lima');

        $entregasVencidas = EntregaDocenteAdmin::where('status', '!=', 4)
                                             ->where('fecha_fin', '<=', $now)
                                             ->get();

        if ($entregasVencidas->isEmpty()) {
            return response()->json(['status' => 'ok', 'message' => 'No action needed.']);
        }

        foreach ($entregasVencidas as $entrega) {
            $entrega->status = 4;
            $entrega->save();

            ActividadesRecientes::create([
                'id_role' => 1,
                'id_usuario' => 1,
                'descripcion' => "El plazo para la entrega '{$entrega->tipo_entrega}' ha finalizado automáticamente.",
                'fecha' => Carbon::now('America/Lima'),
            ]);

            Log::info("ENTREGA FINALIZADA Y ACTIVIDAD CREADA: {$entrega->tipo_entrega}");
        }
        
        return response()->json(['status' => 'ok', 'updated' => $entregasVencidas->count()]);
    }
}