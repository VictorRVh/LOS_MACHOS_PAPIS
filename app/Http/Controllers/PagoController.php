<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use Illuminate\Http\Request;

class PagoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Pago::with('matricula')->get(), 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'condicion' => 'required|string|max:255',
            'nro_recibo' => 'nullable|string|max:255',
            'aporte' => 'nullable|string|max:255',
        ]);

        $pago = Pago::create([
            'condicion' => $request->condicion,
            'nro_recibo' => $request->nro_recibo,
            'aporte' => $request->aporte,
        ]);

        return response()->json($pago, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $pago = Pago::with('')->findOrFail($id);
        return response()->json($pago, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $pago = Pago::findOrFail($id);

        $request->validate([
            'condicion' => 'required|string|max:255',
            'nro_recibo' => 'nullable|string|max:255',
            'aporte' => 'nullable|string|max:255',
        ]);

        $pago->update($request->all());

        return response()->json($pago, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $pago = Pago::findOrFail($id);
        $pago->delete();

        return response()->json(null, 204);
    }
}
