<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EntregaDocenteAdmin;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;

class SyncGoogleCalendarEvents extends Command
{
    protected $signature = 'app:sync-google-calendar';
    protected $description = 'Crea eventos en Google Calendar para todas las programaciones existentes que no lo tengan.';

    public function handle()
    {
        $this->info('Iniciando sincronización con Google Calendar...');

        $client = new Google_Client();
        $client->setAuthConfig(config_path('google-credentials.json'));
        $client->setScopes([Google_Service_Calendar::CALENDAR]);
        $calendarService = new Google_Service_Calendar($client);
        $calendarId = env('GOOGLE_CALENDAR_ID');

        if (!$calendarId) {
            $this->error('El ID de Google Calendar no está configurado en .env. Abortando.');
            return 1;
        }

        $entregasSinEvento = EntregaDocenteAdmin::whereNull('google_calendar_event_id')->get();

        if ($entregasSinEvento->isEmpty()) {
            $this->info('No hay nuevas programaciones para sincronizar. ¡Todo al día!');
            return 0;
        }

        foreach ($entregasSinEvento as $entrega) {
            $this->line("Procesando entrega: '{$entrega->tipo_entrega}'...");
            try {
                $event = new Google_Service_Calendar_Event([
                    'summary' => 'CIERRE: ' . $entrega->tipo_entrega,
                    'start' => ['dateTime' => (new \DateTime($entrega->fecha_fin))->format(\DateTime::RFC3339)],
                    'end' => ['dateTime' => (new \DateTime($entrega->fecha_fin))->modify('+15 minutes')->format(\DateTime::RFC3339)],
                ]);
                
                $createdEvent = $calendarService->events->insert($calendarId, $event);
                
                $entrega->google_calendar_event_id = $createdEvent->getId();
                $entrega->save();

                $this->info(" -> ¡Éxito! Evento creado con ID: {$createdEvent->getId()}");
            } catch (\Exception $e) {
                $this->error(" -> ERROR al crear evento para la entrega ID {$entrega->id}: " . $e->getMessage());
            }
        }
        $this->info('Sincronización completada.');
        return 0;
    }
}