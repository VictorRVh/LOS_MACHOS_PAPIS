<?php

namespace App\Http\Controllers;

use App\Models\ProgramaEstudio;
use App\Models\EspecialidadPrograma;
use App\Models\Periodo;
use Illuminate\Http\Request;
use App\Traits\Helpers;

class EspecialidadProgramaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    use Helpers;

    public function index()
    {
        $data = EspecialidadPrograma::with(['especialidadMadre', 'programaEstudio'])->get();
        return response()->json($data);
    }

    // Crear uno nuevo
    public function store(Request $request)
    {

        $request->validate([
            'id_especialidad' => 'required|exists:especialidad_madre,id',
            'id_programa' => 'required|exists:programa_estudio,id',
            'nro_modulos' => 'required|integer|min:0'
        ]);

        $nuevo = EspecialidadPrograma::create($request->all());
        $this->registrarActividad(
            "Asignó la especialidad '{$nuevo->especialidadMadre->nombre_especialidad}' al programa de estudios '{$nuevo->programaEstudio->año}'",
            "Creado"
        );

        return response()->json($nuevo, 201);
    }

    // Mostrar uno específico
    public function show($id)
    {
        $programa = ProgramaEstudio::with([
            'ciclo:id,nombre_ciclo',
            'especialidadPrograma' => function ($q) {
                $q->orderBy('created_at', 'asc'); // 👈 ordena las especialidades por fecha de creación
            },
            'especialidadPrograma.especialidadMadre:id,nombre_especialidad'
        ])->find($id);

        if (!$programa) {
            return response()->json(['message' => 'Programa no encontrado'], 404);
        }

        if ($programa->especialidadPrograma->isEmpty()) {
            return response()->json([
                'ciclo' => $programa->ciclo,
                'message' => 'No se encontraron especialidades asociadas a este programa'
            ]);
        }

        return response()->json([
            'ciclo' => $programa->ciclo,
            'especialidad_programas' => $programa->especialidadPrograma->map(function ($item) {
                return [
                    'id' => $item->id,
                    'id_especialidad' => $item->id_especialidad,
                    'id_programa' => $item->id_programa,
                    'nro_modulos' => $item->nro_modulos,
                    'especialidad_madre' => $item->especialidadMadre,
                ];
            })
        ]);
    }


    // Actualizar
public function update(Request $request, $id)
{
    $registro = EspecialidadPrograma::find($id);

    if (!$registro) {
        return response()->json(['message' => 'No encontrado'], 404);
    }

    $request->validate([
        'id_especialidad' => 'sometimes|exists:especialidad_madre,id',
        'id_programa' => 'sometimes|exists:programa_estudio,id',
        'nro_modulos' => 'sometimes|integer|min:0'
    ]);

    // Guardar valores antes del cambio
    $antes_nro_modulos = $registro->nro_modulos;

    // Actualizar
    $registro->update($request->all());

    // Guardar después del cambio
    $despues_nro_modulos = $registro->nro_modulos;

    // Detectar solo cambio en n° de módulos
    $mensajeCambio = ($antes_nro_modulos != $despues_nro_modulos)
        ? "y módulos de {$antes_nro_modulos} a {$despues_nro_modulos}"
        : " ";

    // ACTIVIDAD (se mantiene lo que ya tenías)
    $this->registrarActividad(
        "Actualizó la especialidad '{$registro->especialidadMadre->nombre_especialidad}' del programa '{$registro->programaEstudio->año}' ({$mensajeCambio})",
        "Actualizado"
    );

    return response()->json($registro);
}


    // Eliminar
    public function destroy($id)
    {
        $registro = EspecialidadPrograma::find($id);

        if (!$registro) {
            return response()->json(['message' => 'No encontrado'], 404);
        }
        $nombreEspecialidad = $registro->especialidadMadre->nombre_especialidad;
        $nombrePrograma = $registro->programaEstudio->nombre_programa;
        $registro->delete();
        $this->registrarActividad(
            "Eliminó la especialidad '{$nombreEspecialidad}' del programa '{$nombrePrograma}'",
            "Eliminado"
        );

        return response()->json(['message' => 'Eliminado correctamente'], 204);
    }

    public function getRelacionadosPorEspecialidadPrograma($id)
    {
        $especialidadesPrograma = EspecialidadPrograma::with([
            'programaEstudio',
            'modulo',
        ])->where('id_especialidad', $id)->get();

        return response()->json([
            'relaciones' => $especialidadesPrograma,
        ]);
    }
}
