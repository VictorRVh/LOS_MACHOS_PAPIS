<?php

namespace App\Http\Controllers;

use App\Models\Notificaciones;
use Illuminate\Http\Request;

class NotificacionesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userId = auth()->id();

        $notificaciones = Notificaciones::where('id_usuario', $userId)
            ->where('leido', 0)
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        return response()->json($notificaciones);
    }

    public function marcarTodo()
    {
        Notificaciones::where('id_usuario', auth()->id())
            ->update(['leido' => 1]);

        return response()->json(['message' => 'Notificaciones marcadas como leídas']);
    }

    public function marcarLeido($id)
    {
        Notificaciones::where('id', $id)
            ->where('id_usuario', auth()->id())
            ->update(['leido' => 1]);

        return response()->json(['message' => 'Notificación marcada']);
    }

    public function countUnread()
    {
        $count = Notificaciones::where('id_usuario', auth()->id())
            ->where('leido', 0)
            ->count();

        return response()->json(['unread' => $count]);
    }

    // GET /api/notificaciones/{id}
    public function show($id)
    {
        $notificacion = Notificaciones::with('usuario')->find($id);

        if (!$notificacion) {
            return response()->json(['message' => 'Notificación no encontrada'], 404);
        }

        return response()->json($notificacion);
    }

    // POST /api/notificaciones
    public function store(Request $request)
    {
        $request->validate([
            'id_usuario' => 'required|exists:users,id',
            'titulo'     => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'link'       => 'nullable|url'
        ]);

        $notificacion = Notificaciones::create($request->all());

        return response()->json([
            'message' => 'Notificación creada exitosamente',
            'data'    => $notificacion
        ], 201);
    }

    // PATCH /api/notificaciones/{id}
    public function update(Request $request, $id)
    {
        $notificacion = Notificaciones::find($id);

        if (!$notificacion) {
            return response()->json(['message' => 'Notificación no encontrada'], 404);
        }

        $request->validate([
            'id_usuario' => 'sometimes|exists:users,id',
            'titulo'     => 'sometimes|string|max:150',
            'descripcion' => 'nullable|string',
            'link'       => 'nullable|url'
        ]);

        $notificacion->update($request->all());

        return response()->json([
            'message' => 'Notificación actualizada correctamente',
            'data'    => $notificacion
        ]);
    }

    // DELETE /api/notificaciones/{id}
    public function destroy($id)
    {
        $notificacion = Notificaciones::find($id);

        if (!$notificacion) {
            return response()->json(['message' => 'Notificación no encontrada'], 404);
        }

        $notificacion->delete();

        return response()->json(['message' => 'Notificación eliminada correctamente']);
    }
}
