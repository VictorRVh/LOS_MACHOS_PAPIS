<?php

namespace App\Http\Controllers;

use App\Models\Cetpro;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CensoController extends Controller
{
    public function anios()
    {
        return response()->json([
            'anios_disponibles' => $this->getAniosDisponibles(),
        ]);
    }

    public function data(Request $request)
    {
        $aniosDisponibles = $this->getAniosDisponibles();
        $anio = $this->resolveAnio($request, $aniosDisponibles);
        $fechaInicio = $anio . '-01-01';
        $fechaFin = $anio . '-12-31';
        $cetpro = Cetpro::first();

        $matriculaBase = $this->matriculaBaseQuery($fechaInicio, $fechaFin);

        $totalesRows = (clone $matriculaBase)
            ->selectRaw('periodo.nombre_periodo as periodo, ciclo_academico.nombre_ciclo as ciclo, estudiante.sexo as sexo, COUNT(*) as total')
            ->groupBy('periodo.nombre_periodo', 'ciclo_academico.nombre_ciclo', 'estudiante.sexo')
            ->get();

        $retiradosRows = (clone $matriculaBase)
            ->where('matricula.matriculado', 2)
            ->selectRaw('periodo.nombre_periodo as periodo, ciclo_academico.nombre_ciclo as ciclo, estudiante.sexo as sexo, COUNT(*) as total')
            ->groupBy('periodo.nombre_periodo', 'ciclo_academico.nombre_ciclo', 'estudiante.sexo')
            ->get();

        $aprobadosRows = $this->queryAprobados($fechaInicio, $fechaFin)->get();
        $desaprobadosRows = $this->queryDesaprobados($fechaInicio, $fechaFin)->get();

        $aprobadosConCertRows = (clone $this->queryAprobados($fechaInicio, $fechaFin))
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('estudiante_documento')
                    ->whereColumn('estudiante_documento.id_matricula', 'matricula.id');
            })
            ->get();

        $dataByEspecialidad = (clone $matriculaBase)
            ->selectRaw('especialidad_madre.nombre_especialidad as especialidad, periodo.nombre_periodo as periodo, ciclo_academico.nombre_ciclo as ciclo, estudiante.sexo as sexo, matricula.matriculado as estado, COUNT(*) as total')
            ->groupBy(
                'especialidad_madre.nombre_especialidad',
                'periodo.nombre_periodo',
                'ciclo_academico.nombre_ciclo',
                'estudiante.sexo',
                'matricula.matriculado'
            )
            ->get();

        $egresadosRows = DB::table('egresados')
            ->join('estudiante', 'egresados.id_estudiante', '=', 'estudiante.id')
            ->join('especialidad_programa', 'egresados.id_especialidad', '=', 'especialidad_programa.id')
            ->join('especialidad_madre', 'especialidad_programa.id_especialidad', '=', 'especialidad_madre.id')
            ->join('programa_estudio', 'especialidad_programa.id_programa', '=', 'programa_estudio.id')
            ->join('ciclo_academico', 'programa_estudio.id_ciclo', '=', 'ciclo_academico.id')
            ->when($fechaInicio, fn($q) => $q->whereDate('egresados.created_at', '>=', $fechaInicio))
            ->when($fechaFin, fn($q) => $q->whereDate('egresados.created_at', '<=', $fechaFin))
            ->selectRaw("'I' as periodo, especialidad_madre.nombre_especialidad as especialidad, ciclo_academico.nombre_ciclo as ciclo, estudiante.sexo as sexo, COUNT(*) as total")
            ->groupBy('especialidad_madre.nombre_especialidad', 'ciclo_academico.nombre_ciclo', 'estudiante.sexo')
            ->get();

        $tituladosRows = DB::table('estudiante_documento')
            ->join('matricula', 'estudiante_documento.id_matricula', '=', 'matricula.id')
            ->join('estudiante', 'matricula.id_estudiante', '=', 'estudiante.id')
            ->join('grupo', 'matricula.id_grupo', '=', 'grupo.id')
            ->join('periodo', 'grupo.id_periodo', '=', 'periodo.id')
            ->join('especialidad_programa', 'grupo.id_especialidad', '=', 'especialidad_programa.id')
            ->join('especialidad_madre', 'especialidad_programa.id_especialidad', '=', 'especialidad_madre.id')
            ->join('programa_estudio', 'grupo.id_programa', '=', 'programa_estudio.id')
            ->join('ciclo_academico', 'programa_estudio.id_ciclo', '=', 'ciclo_academico.id')
            ->when($fechaInicio, fn($q) => $q->whereDate('estudiante_documento.created_at', '>=', $fechaInicio))
            ->when($fechaFin, fn($q) => $q->whereDate('estudiante_documento.created_at', '<=', $fechaFin))
            ->selectRaw('periodo.nombre_periodo as periodo, especialidad_madre.nombre_especialidad as especialidad, ciclo_academico.nombre_ciclo as ciclo, estudiante.sexo as sexo, COUNT(*) as total')
            ->groupBy('periodo.nombre_periodo', 'especialidad_madre.nombre_especialidad', 'ciclo_academico.nombre_ciclo', 'estudiante.sexo')
            ->get();

        [$t105_1, $t105_2] = $this->build105Tables($dataByEspecialidad, $egresadosRows, $tituladosRows);
        [$t106, $t107] = $this->buildDiscapacidadTables($t105_2);

        return response()->json([
            'anio_censo' => (int)$anio,
            'anios_disponibles' => $aniosDisponibles,
            'codMod' => (string)($cetpro?->numero ?: $cetpro?->rd_autorizacion ?: '0000000'),
            'codLoc' => (string)($cetpro?->rd_conversion ?: $cetpro?->rd_autorizacion ?: '000000'),
            'cetpro' => (string)('CENTRO DE EDUCACIÓN TÉCNICO PRODUCTIVA ' . ($cetpro?->cetpro ?: 'PUNO')),
            'dist' => (string)($cetpro?->distrito ?: $cetpro?->provincia ?: 'PUNO'),
            'loc' => (string)($cetpro?->lugar ?: $cetpro?->distrito ?: 'PUNO'),
            't101' => [
                $this->format101Row('TOTAL', $this->sumSemCycleSex($totalesRows)),
                $this->format101Row('Aprobados', $this->sumSemCycleSex($aprobadosRows)),
                $this->format101Row('Desaprobados', $this->sumSemCycleSex($desaprobadosRows)),
                $this->format101Row('Retirados', $this->sumSemCycleSex($retiradosRows)),
            ],
            't102' => [
                $this->format101Row('TOTAL', $this->sumSemCycleSex($retiradosRows)),
            ],
            't104' => [
                $this->format101Row('TOTAL', $this->sumSemCycleSex($aprobadosRows)),
                $this->format101Row('Con Certificación', $this->sumSemCycleSex($aprobadosConCertRows)),
                $this->format101Row(
                    'Sin Certificación',
                    $this->subtractCounts(
                        $this->sumSemCycleSex($aprobadosRows),
                        $this->sumSemCycleSex($aprobadosConCertRows)
                    )
                ),
            ],
            't105_1' => $t105_1,
            't105_2' => $t105_2,
            't106' => $t106,
            't107' => $t107,
            'programas' => $this->buildProgramasData($fechaInicio, $fechaFin),
            'sedesAT' => $this->buildSedesRows($cetpro, 'AT01'),
            'sedesTE' => $this->buildSedesRows($cetpro, 'TE01'),
            'matriculaEdad' => $this->buildMatriculaEdad($fechaInicio, $fechaFin),
            'personal' => $this->buildPersonalRows(),
        ]);
    }

    private function resolveAnio(Request $request, array $aniosDisponibles): int
    {
        $requestAnio = (int)$request->query('anio');
        if ($requestAnio > 0 && in_array($requestAnio, $aniosDisponibles, true)) {
            return $requestAnio;
        }

        $fechaInicio = $request->query('fecha_inicio');
        if ($fechaInicio) {
            $parsed = (int)date('Y', strtotime((string)$fechaInicio));
            if ($parsed > 0 && in_array($parsed, $aniosDisponibles, true)) {
                return $parsed;
            }
        }

        if (!empty($aniosDisponibles)) {
            return (int)$aniosDisponibles[0];
        }

        return (int)date('Y');
    }

    private function getAniosDisponibles(): array
    {
        $fromPeriodo = DB::table('periodo')
            ->pluck('nombre_periodo')
            ->map(function ($nombrePeriodo) {
                if (preg_match('/(19|20)\d{2}/', (string)$nombrePeriodo, $match)) {
                    return (int)$match[0];
                }
                return null;
            })
            ->filter();

        $fromMatricula = DB::table('matricula')
            ->selectRaw('DISTINCT YEAR(created_at) as anio')
            ->pluck('anio')
            ->filter(fn($anio) => (int)$anio > 0)
            ->map(fn($anio) => (int)$anio);

        $fromEgresados = DB::table('egresados')
            ->selectRaw('DISTINCT YEAR(created_at) as anio')
            ->pluck('anio')
            ->filter(fn($anio) => (int)$anio > 0)
            ->map(fn($anio) => (int)$anio);

        $anios = $fromPeriodo
            ->merge($fromMatricula)
            ->merge($fromEgresados)
            ->unique()
            ->filter(fn($anio) => $anio >= 2000 && $anio <= 2100)
            ->sortDesc()
            ->values()
            ->all();

        return array_map('intval', $anios);
    }

    private function matriculaBaseQuery(?string $fechaInicio, ?string $fechaFin)
    {
        return DB::table('matricula')
            ->join('estudiante', 'matricula.id_estudiante', '=', 'estudiante.id')
            ->join('grupo', 'matricula.id_grupo', '=', 'grupo.id')
            ->join('periodo', 'grupo.id_periodo', '=', 'periodo.id')
            ->join('especialidad_programa', 'grupo.id_especialidad', '=', 'especialidad_programa.id')
            ->join('especialidad_madre', 'especialidad_programa.id_especialidad', '=', 'especialidad_madre.id')
            ->join('programa_estudio', 'grupo.id_programa', '=', 'programa_estudio.id')
            ->join('ciclo_academico', 'programa_estudio.id_ciclo', '=', 'ciclo_academico.id')
            ->when($fechaInicio, fn($q) => $q->whereDate('matricula.created_at', '>=', $fechaInicio))
            ->when($fechaFin, fn($q) => $q->whereDate('matricula.created_at', '<=', $fechaFin))
            ->whereIn('matricula.matriculado', [1, 2]);
    }

    private function queryAprobados(?string $fechaInicio, ?string $fechaFin)
    {
        return DB::table('matricula')
            ->join('estudiante', 'matricula.id_estudiante', '=', 'estudiante.id')
            ->join('grupo', 'matricula.id_grupo', '=', 'grupo.id')
            ->join('periodo', 'grupo.id_periodo', '=', 'periodo.id')
            ->join('modulos', 'grupo.id_modulo', '=', 'modulos.id')
            ->join('programa_estudio', 'grupo.id_programa', '=', 'programa_estudio.id')
            ->join('ciclo_academico', 'programa_estudio.id_ciclo', '=', 'ciclo_academico.id')
            ->where('matricula.matriculado', 1)
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('nota_experiencia_formativa as nef')
                    ->whereColumn('nef.id_estudiante', 'matricula.id_estudiante')
                    ->whereColumn('nef.id_grupo', 'matricula.id_grupo');
            })
            ->whereRaw('
                (
                    SELECT COUNT(DISTINCT nct.id_capacidad)
                    FROM nota_capacidad_terminal nct
                    WHERE nct.id_grupo = matricula.id_grupo
                      AND nct.id_estudiante = matricula.id_estudiante
                      AND CAST(nct.nota_capacidad AS UNSIGNED) >= 11
                ) = modulos.nro_capacidades
            ')
            ->where('modulos.nro_capacidades', '>', 0)
            ->when($fechaInicio, fn($q) => $q->whereDate('matricula.created_at', '>=', $fechaInicio))
            ->when($fechaFin, fn($q) => $q->whereDate('matricula.created_at', '<=', $fechaFin))
            ->selectRaw('periodo.nombre_periodo as periodo, ciclo_academico.nombre_ciclo as ciclo, estudiante.sexo as sexo, COUNT(*) as total')
            ->groupBy('periodo.nombre_periodo', 'ciclo_academico.nombre_ciclo', 'estudiante.sexo');
    }

    private function queryDesaprobados(?string $fechaInicio, ?string $fechaFin)
    {
        return DB::table('matricula')
            ->join('estudiante', 'matricula.id_estudiante', '=', 'estudiante.id')
            ->join('grupo', 'matricula.id_grupo', '=', 'grupo.id')
            ->join('periodo', 'grupo.id_periodo', '=', 'periodo.id')
            ->join('modulos', 'grupo.id_modulo', '=', 'modulos.id')
            ->join('programa_estudio', 'grupo.id_programa', '=', 'programa_estudio.id')
            ->join('ciclo_academico', 'programa_estudio.id_ciclo', '=', 'ciclo_academico.id')
            ->where('matricula.matriculado', 1)
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('nota_experiencia_formativa as nef')
                    ->whereColumn('nef.id_estudiante', 'matricula.id_estudiante')
                    ->whereColumn('nef.id_grupo', 'matricula.id_grupo');
            })
            ->whereRaw('
                (
                    SELECT COUNT(DISTINCT nct.id_capacidad)
                    FROM nota_capacidad_terminal nct
                    WHERE nct.id_grupo = matricula.id_grupo
                      AND nct.id_estudiante = matricula.id_estudiante
                      AND CAST(nct.nota_capacidad AS UNSIGNED) >= 11
                ) < modulos.nro_capacidades
            ')
            ->where('modulos.nro_capacidades', '>', 0)
            ->when($fechaInicio, fn($q) => $q->whereDate('matricula.created_at', '>=', $fechaInicio))
            ->when($fechaFin, fn($q) => $q->whereDate('matricula.created_at', '<=', $fechaFin))
            ->selectRaw('periodo.nombre_periodo as periodo, ciclo_academico.nombre_ciclo as ciclo, estudiante.sexo as sexo, COUNT(*) as total')
            ->groupBy('periodo.nombre_periodo', 'ciclo_academico.nombre_ciclo', 'estudiante.sexo');
    }

    private function sumSemCycleSex(Collection $rows): array
    {
        $counts = array_fill(0, 10, 0);

        foreach ($rows as $row) {
            $total = (int)($row->total ?? 0);
            $sexoIndex = $this->sexoIndex($row->sexo ?? null);
            $semestre = $this->periodoSemestre($row->periodo ?? null);
            $ciclo = $this->cicloKey($row->ciclo ?? null);

            $counts[$sexoIndex] += $total;

            if ($semestre === 1 && $ciclo === 'aux') {
                $counts[2 + $sexoIndex] += $total;
            } elseif ($semestre === 1 && $ciclo === 'tec') {
                $counts[4 + $sexoIndex] += $total;
            } elseif ($semestre === 2 && $ciclo === 'aux') {
                $counts[6 + $sexoIndex] += $total;
            } else {
                $counts[8 + $sexoIndex] += $total;
            }
        }

        return $counts;
    }

    private function semCycleSex8(Collection $rows): array
    {
        $counts = array_fill(0, 8, 0);

        foreach ($rows as $row) {
            $total = (int)($row->total ?? 0);
            $sexoIndex = $this->sexoIndex($row->sexo ?? null);
            $semestre = $this->periodoSemestre($row->periodo ?? null);
            $ciclo = $this->cicloKey($row->ciclo ?? null);

            if ($semestre === 1 && $ciclo === 'aux') {
                $counts[0 + $sexoIndex] += $total;
            } elseif ($semestre === 1 && $ciclo === 'tec') {
                $counts[2 + $sexoIndex] += $total;
            } elseif ($semestre === 2 && $ciclo === 'aux') {
                $counts[4 + $sexoIndex] += $total;
            } else {
                $counts[6 + $sexoIndex] += $total;
            }
        }

        return $counts;
    }

    private function subtractCounts(array $a, array $b): array
    {
        $result = [];
        foreach ($a as $i => $value) {
            $result[] = max(0, ((int)$value - (int)($b[$i] ?? 0)));
        }
        return $result;
    }

    private function format101Row(string $label, array $counts): array
    {
        return array_merge([$label], array_map(fn($n) => (string)$n, $counts));
    }

    private function build105Tables(Collection $matriculas, Collection $egresados, Collection $titulados): array
    {
        $especialidades = $matriculas->pluck('especialidad')
            ->merge($egresados->pluck('especialidad'))
            ->merge($titulados->pluck('especialidad'))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $rows105_1 = [];
        $rows105_2 = [];

        $allMatriculados = $this->semCycleSex8($matriculas->where('estado', 1));
        $allRetirados = $this->semCycleSex8($matriculas->where('estado', 2));
        $allEgresados = $this->semCycleSex8($egresados);
        $allTitulados = $this->semCycleSex8($titulados);

        $rows105_1[] = array_merge(['TOTAL'], array_map('strval', $allMatriculados), array_map('strval', $allRetirados));
        $rows105_2[] = array_merge(['TOTAL'], array_map('strval', $allEgresados), array_map('strval', $allTitulados));

        foreach ($especialidades as $especialidad) {
            $matriculados = $this->semCycleSex8($matriculas->where('especialidad', $especialidad)->where('estado', 1));
            $retirados = $this->semCycleSex8($matriculas->where('especialidad', $especialidad)->where('estado', 2));
            $egres = $this->semCycleSex8($egresados->where('especialidad', $especialidad));
            $titu = $this->semCycleSex8($titulados->where('especialidad', $especialidad));

            $rows105_1[] = array_merge([(string)$especialidad], array_map('strval', $matriculados), array_map('strval', $retirados));
            $rows105_2[] = array_merge([(string)$especialidad], array_map('strval', $egres), array_map('strval', $titu));
        }

        return [$rows105_1, $rows105_2];
    }

    private function buildDiscapacidadTables(array $t105_2): array
    {
        $rows106 = [];
        $rows107 = [];

        foreach ($t105_2 as $index => $row) {
            $nombre = $row[0] ?? 'TOTAL';
            $egresados8 = array_map('intval', array_slice($row, 1, 8));
            $titulados8 = array_map('intval', array_slice($row, 9, 8));

            $hEgres = $egresados8[0] + $egresados8[2] + $egresados8[4] + $egresados8[6];
            $mEgres = $egresados8[1] + $egresados8[3] + $egresados8[5] + $egresados8[7];
            $hTitu = $titulados8[0] + $titulados8[2] + $titulados8[4] + $titulados8[6];
            $mTitu = $titulados8[1] + $titulados8[3] + $titulados8[5] + $titulados8[7];

            $prefix106 = [(string)$nombre, (string)$hEgres, (string)$mEgres];
            $prefix107 = [(string)$nombre, (string)$hTitu, (string)$mTitu];
            $middle = array_fill(0, 26, '0');

            $rows106[] = array_merge($prefix106, $middle, [(string)$hEgres, (string)$mEgres]);
            $rows107[] = array_merge($prefix107, $middle, [(string)$hTitu, (string)$mTitu]);
        }

        if (count($rows106) === 0) {
            $rows106[] = array_merge(['TOTAL', '0', '0'], array_fill(0, 26, '0'), ['0', '0']);
        }
        if (count($rows107) === 0) {
            $rows107[] = array_merge(['TOTAL', '0', '0'], array_fill(0, 26, '0'), ['0', '0']);
        }

        if (($rows106[0][0] ?? '') !== 'TOTAL') {
            array_unshift($rows106, array_merge(['TOTAL', '0', '0'], array_fill(0, 26, '0'), ['0', '0']));
        }
        if (($rows107[0][0] ?? '') !== 'TOTAL') {
            array_unshift($rows107, array_merge(['TOTAL', '0', '0'], array_fill(0, 26, '0'), ['0', '0']));
        }

        return [$rows106, $rows107];
    }

    private function buildProgramasData(?string $fechaInicio, ?string $fechaFin): array
    {
        $especialidades = DB::table('especialidad_programa')
            ->join('especialidad_madre', 'especialidad_programa.id_especialidad', '=', 'especialidad_madre.id')
            ->select('especialidad_programa.id', 'especialidad_madre.nombre_especialidad')
            ->orderBy('especialidad_madre.nombre_especialidad')
            ->get();

        $rows = [];
        $nro = 1;

        foreach ($especialidades as $esp) {
            $modulos = DB::table('modulos')
                ->where('id_especialidad', $esp->id)
                ->selectRaw('COALESCE(SUM(horas), 0) as horas, COALESCE(SUM(creditos), 0) as creditos')
                ->first();

            $grupos = DB::table('grupo')
                ->where('id_especialidad', $esp->id)
                ->when($fechaInicio, fn($q) => $q->whereDate('created_at', '>=', $fechaInicio))
                ->when($fechaFin, fn($q) => $q->whereDate('created_at', '<=', $fechaFin))
                ->selectRaw("
                    SUM(CASE WHEN turno = 'M' THEN 1 ELSE 0 END) as manana,
                    SUM(CASE WHEN turno = 'T' THEN 1 ELSE 0 END) as tarde,
                    SUM(CASE WHEN turno = 'N' THEN 1 ELSE 0 END) as noche
                ")
                ->first();

            $rows[] = [
                str_pad((string)$nro, 2, '0', STR_PAD_LEFT),
                'ESP' . str_pad((string)$nro, 4, '0', STR_PAD_LEFT),
                (string)$esp->nombre_especialidad,
                (string)($modulos->horas ?? 0),
                (string)($modulos->creditos ?? 0),
                (string)($grupos->manana ?? 0),
                (string)($grupos->tarde ?? 0),
                (string)($grupos->noche ?? 0),
            ];
            $nro++;
        }

        if (count($rows) === 0) {
            $rows[] = ['01', 'ESP0001', '-', '0', '0', '0', '0', '0'];
        }

        return $rows;
    }

    private function buildSedesRows(?Cetpro $cetpro, string $codigo): array
    {
        $ubigeo = '000000';
        $nombreSede = (string)($cetpro?->lugar ?: $cetpro?->distrito ?: 'SEDE PRINCIPAL');

        return [[
            '01',
            $codigo,
            $nombreSede,
            'X',
            '',
            '',
            $ubigeo,
            (string)($cetpro?->region ?: '-'),
            (string)($cetpro?->provincia ?: '-'),
            (string)($cetpro?->distrito ?: '-'),
            (string)($cetpro?->direccion ?: '-'),
        ]];
    }

    private function buildMatriculaEdad(?string $fechaInicio, ?string $fechaFin): array
    {
        $rows = DB::table('matricula')
            ->join('estudiante', 'matricula.id_estudiante', '=', 'estudiante.id')
            ->join('grupo', 'matricula.id_grupo', '=', 'grupo.id')
            ->join('programa_estudio', 'grupo.id_programa', '=', 'programa_estudio.id')
            ->join('ciclo_academico', 'programa_estudio.id_ciclo', '=', 'ciclo_academico.id')
            ->where('matricula.matriculado', 1)
            ->when($fechaInicio, fn($q) => $q->whereDate('matricula.created_at', '>=', $fechaInicio))
            ->when($fechaFin, fn($q) => $q->whereDate('matricula.created_at', '<=', $fechaFin))
            ->selectRaw('TIMESTAMPDIFF(YEAR, estudiante.fecha_nacimiento, CURDATE()) as edad, estudiante.sexo as sexo, ciclo_academico.nombre_ciclo as ciclo, COUNT(*) as total')
            ->groupBy(DB::raw('TIMESTAMPDIFF(YEAR, estudiante.fecha_nacimiento, CURDATE())'), 'estudiante.sexo', 'ciclo_academico.nombre_ciclo')
            ->get();

        $bucketOrder = ['15-18', '19-24', '25-29', '30-34', '35-39', '40-49', '50-54', '55+'];
        $buckets = [];
        foreach ($bucketOrder as $bucket) {
            $buckets[$bucket] = [
                'total' => ['H' => 0, 'M' => 0],
                'aux' => ['H' => 0, 'M' => 0],
                'tec' => ['H' => 0, 'M' => 0],
            ];
        }

        foreach ($rows as $row) {
            $bucket = $this->edadBucket((int)$row->edad);
            $sexo = $this->sexoIndex($row->sexo ?? null) === 0 ? 'H' : 'M';
            $ciclo = $this->cicloKey($row->ciclo ?? null) === 'aux' ? 'aux' : 'tec';

            $buckets[$bucket]['total'][$sexo] += (int)$row->total;
            $buckets[$bucket][$ciclo][$sexo] += (int)$row->total;
        }

        $out = [];
        $sum = ['H' => 0, 'M' => 0, 'auxH' => 0, 'auxM' => 0, 'tecH' => 0, 'tecM' => 0];
        foreach ($bucketOrder as $bucket) {
            $h = $buckets[$bucket]['total']['H'];
            $m = $buckets[$bucket]['total']['M'];
            $auxH = $buckets[$bucket]['aux']['H'];
            $auxM = $buckets[$bucket]['aux']['M'];
            $tecH = $buckets[$bucket]['tec']['H'];
            $tecM = $buckets[$bucket]['tec']['M'];

            if (($h + $m + $auxH + $auxM + $tecH + $tecM) === 0) {
                continue;
            }

            $out[] = [$bucket, (string)$h, (string)$m, (string)$auxH, (string)$auxM, (string)$tecH, (string)$tecM];
            $sum['H'] += $h;
            $sum['M'] += $m;
            $sum['auxH'] += $auxH;
            $sum['auxM'] += $auxM;
            $sum['tecH'] += $tecH;
            $sum['tecM'] += $tecM;
        }

        $out[] = ['TOTAL', (string)$sum['H'], (string)$sum['M'], (string)$sum['auxH'], (string)$sum['auxM'], (string)$sum['tecH'], (string)$sum['tecM']];

        return $out;
    }

    private function buildPersonalRows(): array
    {
        $rows = [];
        $nro = 1;

        $docentes = DB::table('docente')
            ->join('users', 'docente.user_id', '=', 'users.id')
            ->select('users.name', 'users.apellido_paterno', 'users.apellido_materno', 'users.dni', 'users.fecha_nacimiento')
            ->orderBy('users.apellido_paterno')
            ->get();

        foreach ($docentes as $docente) {
            $rows[] = $this->makePersonalRow($nro++, $docente->name, $docente->apellido_paterno, $docente->apellido_materno, $docente->dni, $docente->fecha_nacimiento, 'DOCENTE');
        }

        $administrativos = DB::table('personal_administrativo')
            ->join('users', 'personal_administrativo.id_usuario', '=', 'users.id')
            ->select('users.name', 'users.apellido_paterno', 'users.apellido_materno', 'users.dni', 'users.fecha_nacimiento')
            ->orderBy('users.apellido_paterno')
            ->get();

        foreach ($administrativos as $admin) {
            $rows[] = $this->makePersonalRow($nro++, $admin->name, $admin->apellido_paterno, $admin->apellido_materno, $admin->dni, $admin->fecha_nacimiento, 'ADMIN');
        }

        if (count($rows) === 0) {
            $rows[] = $this->makePersonalRow(1, '-', '-', '-', '-', null, 'PERSONAL');
        }

        return $rows;
    }

    private function makePersonalRow(int $nro, string $name, string $apPat, string $apMat, string $dni, ?string $fechaNacimiento, string $rol): array
    {
        $edad = 0;
        if ($fechaNacimiento) {
            $edad = (int)date_diff(date_create($fechaNacimiento), now())->y;
        }

        return [
            str_pad((string)$nro, 2, '0', STR_PAD_LEFT),
            trim($apPat . ' ' . $apMat . ' ' . $name),
            (string)$dni,
            $rol === 'DOCENTE' ? '01' : '02',
            (string)($edad > 0 ? $edad . ' años' : '-'),
            '0100',
            '',
            '01',
            '01',
            '01',
            '00',
            '00',
            '00',
            '00',
            '00',
            '00',
            '00',
            '00',
            '00',
            '',
            '40',
        ];
    }

    private function sexoIndex(?string $sexo): int
    {
        return strtoupper((string)$sexo) === 'M' ? 0 : 1;
    }

    private function cicloKey(?string $ciclo): string
    {
        $ciclo = mb_strtolower((string)$ciclo);
        return str_contains($ciclo, 'auxiliar') ? 'aux' : 'tec';
    }

    private function periodoSemestre(?string $periodo): int
    {
        $raw = mb_strtolower((string)$periodo);
        if (preg_match('/(^|[\s\-_])(ii|2)([\s\-_]|$)/i', $raw)) {
            return 2;
        }
        return 1;
    }

    private function edadBucket(int $edad): string
    {
        if ($edad <= 18) return '15-18';
        if ($edad <= 24) return '19-24';
        if ($edad <= 29) return '25-29';
        if ($edad <= 34) return '30-34';
        if ($edad <= 39) return '35-39';
        if ($edad <= 49) return '40-49';
        if ($edad <= 54) return '50-54';
        return '55+';
    }
}
