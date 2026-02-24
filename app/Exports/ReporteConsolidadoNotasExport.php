<?php

namespace App\Exports;

use App\Models\Grupo;
use App\Models\Matricula;
use App\Models\CapacidadTerminal;
use App\Models\Modulo;
use App\Models\NotaCapacidadTerminal;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ReporteConsolidadoNotasExport
{
    protected string $idGrupo;

    public function __construct(string $idGrupo)
    {
        $this->idGrupo = $idGrupo;
    }

    public function build(): Spreadsheet
    {
        $spreadsheet = IOFactory::load(
            storage_path('app/templates/consolidado-notas-ultimo.xlsx')
        );

        $grupo = Grupo::with([
            'especialidad',
            'modulo',
            'periodo',
        ])->findOrFail($this->idGrupo);

        $this->llenarDatosGenerales($spreadsheet, $grupo);
        $this->llenarNombreModulo($spreadsheet, $grupo);
        $this->llenarEstudiantes($spreadsheet, $grupo);

        return $spreadsheet;
    }

    private function llenarDatosGenerales(
        Spreadsheet $spreadsheet,
        Grupo $grupo
    ): void {
        $sheet = $spreadsheet->getSheetByName('CONSOLIDADO');

        if (!$sheet) {
            throw new \Exception('La hoja CONSOLIDADO no existe');
        }
        // PROGRAMA DE ESTUDIOS: (nombre de la especialidad)
        $nombreEspecialidad = $grupo->especialidad->especialidadMadre->nombre_especialidad ?? '';

        $sheet->setCellValue(
            'S4',
            'PROGRAMA DE ESTUDIOS: ' . $nombreEspecialidad
        );

        $nombreCiclo = $grupo->programaEstudio->ciclo->nombre_ciclo ?? '';

        // Ciclo
        $sheet->setCellValue(
            'S7',
            'NIVEL FORMATIVO: ' . $nombreCiclo
        );
        // datos de la intitucion 
        /////////////////////////////////////////////////////
        $institucion = DB::table('cetpros')->first();
        $nombreInstitucion = $institucion->cetpro ?? '';
        // $dre = $institucion->dre ?? '';
        // $ugel = $institucion->ugel ?? '';
        $codigoModular = $institucion->codigo_modular ?? '240069';
        $codigoAutorizacion = $institucion->rd_autorizacion ?? 'R.D.00000';
        $codigoConversion = $institucion->rd_conversion ?? 'R.C.00000';
        $tipoGestion = $institucion->tipo_gestion ?? '';
        $departamento = $institucion->region ?? '';
        $provincia = $institucion->provincia ?? '';
        $distrito = $institucion->distrito ?? '';
        $direccion = $institucion->direccion ?? '';



        $sheet->setCellValue('D4', $nombreInstitucion);
        // $sheet->setCellValue('M4', $dre);
        $sheet->setCellValue('D5', $codigoModular);
        //  $sheet->setCellValue('M5', $ugel);
        $sheet->setCellValue('H8', $codigoAutorizacion);
        // $sheet->setCellValue('M6', $codigoConversion);

        $sheet->setCellValue('D6', $departamento);
        $sheet->setCellValue('F6', $provincia);

        $sheet->setCellValue('I6', $distrito);
        $sheet->setCellValue('I5', $tipoGestion);

        $sheet->setCellValue('D7', $direccion);
    }

    private function llenarEstudiantes(
        Spreadsheet $spreadsheet,
        Grupo $grupo
    ): void {

        $sheet = $spreadsheet->getSheetByName('CONSOLIDADO');

        $filaInicio = 12;
        $filasPlantilla = 20; // número real de filas de estudiantes en plantilla

        /////////////////////////////////////////////////////
        // OBTENER ESTUDIANTES
        /////////////////////////////////////////////////////

        $matriculas = Matricula::with('estudiante')
            ->where('id_grupo', $grupo->id)
            ->where('reserva', 0)
            ->join('estudiante as e', 'matricula.id_estudiante', '=', 'e.id')
            ->orderBy('e.apellido_paterno')
            ->orderBy('e.apellido_materno')
            ->orderBy('e.nombre')
            ->select('matricula.*')
            ->get();

        $total = $matriculas->count();

        /////////////////////////////////////////////////////
        // EXTENDER PLANTILLA (MISMO MÉTODO QUE TU EJEMPLO)
        /////////////////////////////////////////////////////

        if ($total > $filasPlantilla) {

            $filasAInsertar = $total - $filasPlantilla;

            $filaModelo = $filaInicio + $filasPlantilla - 1;

            // 1. GUARDAR merges
            $merges = $sheet->getMergeCells();

            // 2. DESHACER merges
            foreach ($merges as $merge) {
                $sheet->unmergeCells($merge);
            }

            // 3. INSERTAR filas
            $sheet->insertNewRowBefore($filaModelo + 1, $filasAInsertar);

            // 4. REAPLICAR merges desplazados
            foreach ($merges as $merge) {

                if (preg_match("/([A-Z]+)(\d+):([A-Z]+)(\d+)/", $merge, $m)) {

                    $col1 = $m[1];
                    $row1 = (int)$m[2];
                    $col2 = $m[3];
                    $row2 = (int)$m[4];

                    if ($row1 > $filaModelo) {

                        $row1 += $filasAInsertar;
                        $row2 += $filasAInsertar;
                    }

                    $sheet->mergeCells("{$col1}{$row1}:{$col2}{$row2}");
                }
            }

            // 5. COPIAR estilos
            $columnas = range('B', 'Z');

            $filaModelo = $filaInicio + $filasPlantilla - 1;

            for ($i = 0; $i < $filasAInsertar; $i++) {

                $filaNueva = $filaModelo + 1 + $i;

                /////////////////////////////////////////////////////
                // 1. COPIAR TODO EL ESTILO DE LA FILA COMPLETA
                /////////////////////////////////////////////////////

                $sheet->duplicateStyle(
                    $sheet->getStyle("B{$filaModelo}:U{$filaModelo}"),
                    "B{$filaNueva}:U{$filaNueva}"
                );
                // copiar borde derecho exacto desde la fila modelo
                $sheet->duplicateStyle(
                    $sheet->getStyle("U{$filaModelo}"),
                    "U{$filaNueva}"
                );
                /////////////////////////////////////////////////////
                // 2. COMBINAR CELDAS IGUAL QUE EL MODELO
                /////////////////////////////////////////////////////

                $sheet->mergeCells("C{$filaNueva}:J{$filaNueva}");

                /////////////////////////////////////////////////////
                // 3. COPIAR ALTURA
                /////////////////////////////////////////////////////

                $altura = $sheet->getRowDimension($filaModelo)->getRowHeight();

                if ($altura !== null) {
                    $sheet->getRowDimension($filaNueva)->setRowHeight($altura);
                }

                /////////////////////////////////////////////////////
                // 4. OPCIONAL: copiar alineación exacta
                /////////////////////////////////////////////////////

                $sheet->getStyle("C{$filaNueva}:J{$filaNueva}")
                    ->getAlignment()
                    ->setHorizontal(
                        $sheet->getStyle("C{$filaModelo}")
                            ->getAlignment()
                            ->getHorizontal()
                    );

                $sheet->getStyle("C{$filaNueva}:J{$filaNueva}")
                    ->getAlignment()
                    ->setVertical(
                        $sheet->getStyle("C{$filaModelo}")
                            ->getAlignment()
                            ->getVertical()
                    );
            }
        }

        /////////////////////////////////////////////////////
        // LLENAR DATOS
        /////////////////////////////////////////////////////
        $indice = 26;
        foreach ($matriculas as $index => $matricula) {

            $fila = $filaInicio + $index;

            $est = $matricula->estudiante;

            $sheet->setCellValue(
                "A{$fila}",
                $index + 1 ?? ''
            );

            $sheet->setCellValue(
                "B{$fila}",
                $est->nro_documento ?? ''
            );

            $sheet->setCellValue(
                "C{$fila}",
                "{$est->apellido_paterno} {$est->apellido_materno}, {$est->nombre}"
            );
        }
    }

    private function llenarNombreModulo(
        Spreadsheet $spreadsheet,
        Grupo $grupo
    ): void {
        $sheet = $spreadsheet->getSheetByName('CONSOLIDADO');

        if (!$sheet) {
            throw new \Exception('La hoja CONSOLIDADO no existe');
        }

        // Mostrar solo el módulo del grupo actual
        $fila = 5;
        $col = 'K'; // Columna donde va el nombre del módulo

        $sheet->setCellValue(
            "{$col}{$fila}",
            $grupo->modulo->descripcion
        );
    }
}
