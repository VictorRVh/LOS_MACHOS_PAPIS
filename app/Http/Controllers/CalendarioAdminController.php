<?php

namespace App\Http\Controllers;

use App\Models\CalendarioAdmin;
use Illuminate\Http\Request;

class CalendarioAdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(CalendarioAdmin::all(), 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
            'laborable' => 'required|boolean',
            'descripcion' => 'nullable|string|max:255',
        ]);

        $calendario = CalendarioAdmin::create($request->all());

        return response()->json($calendario, 201);
    }

    public function show(string $id)
    {
        $calendario = CalendarioAdmin::findOrFail($id);
        return response()->json($calendario, 200);
    }

    public function update(Request $request, string $id)
    {
        $calendario = CalendarioAdmin::findOrFail($id);

        $request->validate([
            'fecha_nacimiento' => 'required|date',
            'laborable' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
        ]);

        $calendario->update($request->all());

        return response()->json($calendario, 200);
    }

    public function destroy(string $id)
    {
        $calendario = CalendarioAdmin::findOrFail($id);
        $calendario->delete();

        return response()->json(null, 204);
    }
}
