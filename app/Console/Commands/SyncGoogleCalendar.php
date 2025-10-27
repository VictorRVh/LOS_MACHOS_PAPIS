<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ActividadesRecientes;
use Carbon\Carbon;
use Google_Client;
use Google_Service_Calendar;
use Illuminate\Support\Facades\Log;

class SyncGoogleCalendar extends Command
{
    protected $signature = 'calendar:sync';
    protected $description = 'Sincroniza los eventos de Google Calendar con la base de datos local';

    private function getCalendarService()
    {
        try {
            $client = new Google_Client();
            $client->setAuthConfig(config_path('google-credentials.json'));
            $client->setScopes([Google_Service_Calendar::CALENDAR]);
            return new Google_Service_Calendar($client);
        } catch (\Exception $e) {
            Log::error("Fallo al inicializar Google Calendar Service: " . $e->getMessage());
            return null;
        }
    }

    public function handle()
    {
        $this->info('Iniciando sincronización con Google Calendar...');
        $calendarService = $this->getCalendarService();
        if (!$calendarService) {
            $this->error('No se pudo conectar con el servicio de Google Calendar. Revisa las credenciales.');
            return 1;
        }

        $calendarId = env('GOOGLE_CALENDAR_ID');
        try {
            $events = $calendarService->events->listEvents($calendarId);
            $count = 0;

            foreach ($events->getItems() as $event) {
                $activity = ActividadesRecientes::firstOrNew(['google_event_id' => $event->getId()]);
                
                if (!$activity->exists) {
                    $activity->fill([
                        'usuario_id' => null,
                        'id_role' => null,
                        'accion' => 'Evento de Calendario',
                        'descripcion' => $event->getSummary(),
                        'fecha' => Carbon::parse($event->getStart()->getDateTime()),
                        'google_event_id' => $event->getId()
                    ]);
                    $activity->save();
                    $count++;
                }
            }

            $this->info("Sincronización completada. Se agregaron {$count} nuevos eventos.");
            return 0;

        } catch (\Exception $e) {
            $this->error('Error al obtener eventos del calendario: ' . $e->getMessage());
            return 1;
        }
    }
}