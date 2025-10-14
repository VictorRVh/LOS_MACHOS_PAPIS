<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Google_Client;
use Google_Service_Drive;
use Exception;

class GoogleDriveController extends Controller
{
    private function getClient()
    {
        $client = new Google_Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect_uri'));
        $client->setAccessType('offline');
        $client->setScopes([Google_Service_Drive::DRIVE_READONLY]);

        $user = Auth::user();

        if ($user && $user->google_access_token) {
            $client->setAccessToken($user->google_access_token);

            if ($client->isAccessTokenExpired()) {
                try {
                    $newAccessToken = $client->fetchAccessTokenWithRefreshToken($user->google_refresh_token);
                    $user->update([
                        'google_access_token' => json_encode($newAccessToken),
                    ]);
                    $client->setAccessToken($newAccessToken);
                } catch (Exception $e) {
                    return null;
                }
            }
        }
        return $client;
    }

    public function redirectToGoogle()
    {
        session(['google_redirect_url' => url()->previous()]);
        
        $client = $this->getClient();
        $authUrl = $client->createAuthUrl();
        return redirect()->away($authUrl);
    }

    public function handleGoogleCallback(Request $request)
    {
        $redirectUrl = session()->pull('google_redirect_url', '/');

        if (!$request->has('code')) {
            return redirect($redirectUrl)->with('error', 'Fallo en la autorización de Google.');
        }

        $client = new Google_Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect_uri'));
        $client->setAccessType('offline');

        try {
            $accessToken = $client->fetchAccessTokenWithAuthCode($request->code);
        } catch (Exception $e) {
            return redirect($redirectUrl)->with('error', 'No se pudo obtener el token de acceso de Google.');
        }

        if (isset($accessToken['error'])) {
            return redirect($redirectUrl)->with('error', 'No se pudo obtener el token de acceso: ' . $accessToken['error_description']);
        }
        
        Auth::user()->update([
            'google_access_token' => json_encode($accessToken),
            'google_refresh_token' => $client->getRefreshToken(),
        ]);

        return redirect($redirectUrl)->with('success', '¡Conectado a Google Drive con éxito!');
    }

    public function listFiles(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'No autenticado.'], 401);
        }

        $user = Auth::user();
        if (!$user->google_access_token) {
             return response()->json(['error' => 'No has conectado tu cuenta de Google Drive.'], 401);
        }

        $client = $this->getClient();

        if (!$client || !$client->getAccessToken()) {
            $user->update(['google_access_token' => null, 'google_refresh_token' => null]);
            return response()->json(['error' => 'El token de Google ha expirado o es inválido. Por favor, vuelve a conectar tu cuenta.'], 401);
        }

        try {
            $driveService = new Google_Service_Drive($client);
            $files = $driveService->files->listFiles([
                'pageSize' => 50,
                'fields' => 'files(id, name, mimeType, webViewLink)',
            ]);

            return response()->json($files->getFiles());

        } catch (Exception $e) {
            return response()->json(['error' => 'Error al comunicarse con la API de Google Drive: ' . $e->getMessage()], 500);
        }
    }
}