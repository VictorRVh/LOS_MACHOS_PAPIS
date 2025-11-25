// src/composables/useExportExcel.js
import ExcelJS from "exceljs";
import { saveAs } from "file-saver";
import useModalToast from "../../composables/useModalToast";
import useGrupoStore from '../../store/Grupo/useGrupoStore';

const grupoStore = useGrupoStore();

const { showConfirmModal, showToast } = useModalToast();

export default function useExportExcel() {

    const exportarCalendarioExcel = async (sesionStore, programacionSesion) => {

        //console.log("sessioens grupos: ", grupoStore?.infoGrupo);

        if (!sesionStore.sesion || !programacionSesion.sesiones) {
            showToast("No hay datos suficientes para generar el reporte.", "error");
            return;
        }

        const workbook = new ExcelJS.Workbook();
        const worksheet = workbook.addWorksheet('Programacion de Sesiones');

        const grupoInfo = sesionStore.grupo;

        // ================================================================
        // 1) OBTENER RANGO REAL DE FECHAS DE LA API (sin usar fecha_inicio/fin)
        // ================================================================
        const todasLasFechas = [];

        programacionSesion.sesiones.forEach(cap => {
            cap.sesiones.forEach(s => {
                (s.calendario_admin || []).forEach(dia => {
                    todasLasFechas.push(new Date(dia.fecha));
                });
            });
        });

        if (todasLasFechas.length === 0) {
            showToast("No existen sesiones para exportar.", "error");
            return;
        }

        const startDate = new Date(Math.min(...todasLasFechas.map(f => f.getTime())));
        const endDate = new Date(Math.max(...todasLasFechas.map(f => f.getTime())));

        const rangoTitulo = `${startDate.toLocaleString('es-ES', { month: 'long' })} ${startDate.getFullYear()} - ${endDate.toLocaleString('es-ES', { month: 'long' })} ${endDate.getFullYear()}`;

        // ================================================================
        // 2) TÍTULO GLOBAL
        // ================================================================
        worksheet.mergeCells('B2:F2');
        const titleCell = worksheet.getCell('B2');
        titleCell.value = `REPORTE DE PROGRAMACIÓN DE SESIONES (${rangoTitulo.toUpperCase()})`;
        titleCell.font = { name: 'Calibri', size: 16, bold: true, color: { argb: 'FFFFFFFF' } };
        titleCell.alignment = { vertical: 'middle', horizontal: 'center' };
        titleCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF007B8C' } };

        // ================================================================
        // 3) ENCABEZADO DE INFORMACIÓN DEL GRUPO
        // ================================================================
        const headerData = [
            ['Módulo:', grupoStore?.infoGrupo?.modulo || 'N/A', 'Docente:', grupoStore?.infoGrupo?.docente || 'N/A'],
            ['Especialidad:', grupoStore?.infoGrupo?.especialidad || 'N/A', 'Turno:', grupoStore?.infoGrupo?.turno || 'N/A'],
            ['Sección:', grupoStore?.infoGrupo?.seccion || 'N/A', '', ''],
        ];

        let currentRowNum = 4;

        headerData.forEach(rowData => {
            const row = worksheet.getRow(currentRowNum);
            row.height = 30; // altura mayor para textos largos

            row.getCell(2).value = { richText: [{ font: { bold: true }, text: rowData[0] }] };
            row.getCell(3).value = rowData[1];
            row.getCell(5).value = { richText: [{ font: { bold: true }, text: rowData[2] }] };
            row.getCell(6).value = rowData[3];

            // Recorre columnas B, C, E, F
            ['B', 'C', 'E', 'F'].forEach(col => {
                const cell = worksheet.getCell(`${col}${currentRowNum}`);

                cell.alignment = {
                    vertical: 'middle',
                    horizontal: 'center',
                    wrapText: true // ⬅⭐⭐ IMPORTANTE PARA QUE BAJE A LA SIGUIENTE LÍNEA
                };

                cell.fill = {
                    type: 'pattern',
                    pattern: 'solid',
                    fgColor: { argb: 'FFFFFF00' }
                };

                cell.border = {
                    top: { style: 'thin' },
                    left: { style: 'thin' },
                    bottom: { style: 'thin' },
                    right: { style: 'thin' }
                };
            });

            currentRowNum++;
        });


        // ================================================================
        // 4) GENERAR CALENDARIO POR MESES REALES
        // ================================================================
        const dateToCellMap = new Map();

        let currentDate = new Date(startDate.getFullYear(), startDate.getMonth(), 1);
        currentRowNum += 2;

        while (currentDate <= endDate) {
            const anio = currentDate.getFullYear();
            const mes = currentDate.getMonth();
            const nombreMes = currentDate.toLocaleString('es-ES', { month: 'long' }).toUpperCase();

            // Título del mes
            worksheet.mergeCells(`A${currentRowNum}:G${currentRowNum}`);
            const monthTitleCell = worksheet.getCell(`A${currentRowNum}`);
            monthTitleCell.value = `${nombreMes} ${anio}`;
            monthTitleCell.font = { bold: true, size: 14, color: { argb: 'FF007B8C' } };
            monthTitleCell.alignment = { vertical: 'middle', horizontal: 'center' };
            currentRowNum++;

            // Cabecera días
            const diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
            const headerRow = worksheet.getRow(currentRowNum);

            diasSemana.forEach((dia, index) => {
                const cell = headerRow.getCell(index + 1);
                cell.value = dia;
                cell.font = { bold: true, color: { argb: 'FFFFFFFF' } };
                cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF007B8C' } };
                cell.alignment = { horizontal: 'center' };
            });
            currentRowNum++;

            // Dibujo del calendario
            const primerDiaDelMes = new Date(anio, mes, 1);
            let diaInicial = primerDiaDelMes.getDay();
            if (diaInicial === 0) diaInicial = 7;

            let fechaIterador = new Date(primerDiaDelMes);
            fechaIterador.setDate(primerDiaDelMes.getDate() - (diaInicial - 1));

            for (let semana = 0; semana < 6; semana++) {
                const row = worksheet.getRow(currentRowNum + semana);
                row.height = 60;

                for (let dia = 0; dia < 7; dia++) {
                    const cell = row.getCell(dia + 1);
                    const fechaActual = fechaIterador.toISOString().split('T')[0];

                    dateToCellMap.set(fechaActual, cell);

                    if (fechaIterador.getMonth() === mes) {
                        cell.value = fechaIterador.getDate();
                        cell.font = { color: { argb: 'FF000000' } };
                    } else {
                        cell.font = { color: { argb: 'FFB0B0B0' } };
                    }

                    cell.alignment = { vertical: 'top', horizontal: 'right', wrapText: true };
                    cell.border = { top: { style: 'thin' }, left: { style: 'thin' }, bottom: { style: 'thin' }, right: { style: 'thin' } };

                    fechaIterador.setDate(fechaIterador.getDate() + 1);
                }
            }

            currentRowNum += 7;
            currentDate.setMonth(currentDate.getMonth() + 1);
        }

        // ================================================================
        // 5) RELLENAR FECHAS CON SESIONES
        // ================================================================
        // ================================================================
        // 5) RELLENAR CALENDARIO CON SESIONES SIN REPETIR TÍTULO
        // ================================================================

        // Función para detectar rangos consecutivos
        function obtenerRangosConsecutivos(fechas) {
            fechas.sort();
            const rangos = [];
            let inicio = fechas[0];
            let anterior = fechas[0];

            for (let i = 1; i < fechas.length; i++) {
                const actual = fechas[i];
                const diff = (new Date(actual) - new Date(anterior)) / (1000 * 60 * 60 * 24);

                if (diff !== 1) {
                    rangos.push([inicio, anterior]);
                    inicio = actual;
                }

                anterior = actual;
            }

            rangos.push([inicio, anterior]);
            return rangos;
        }

        programacionSesion.sesiones.forEach(cap => {
            cap.sesiones.forEach(sesion => {

                const color = sesion.status === 0 ? 'FFFACС15' :
                    sesion.status === 1 ? 'FF22C55E' :
                        'FF3B82F6';

                const fechas = sesion.calendario_admin?.map(x => x.fecha) || [];

                if (fechas.length === 0) return;

                // Obtener los rangos de días continuos
                const rangos = obtenerRangosConsecutivos(fechas);

                rangos.forEach(([inicio, fin]) => {

                    // Tomar celdas del mapa
                    const cellInicio = dateToCellMap.get(inicio);
                    const cellFin = dateToCellMap.get(fin);

                    if (!cellInicio || !cellFin) return;

                    const row = cellInicio.row;
                    const colInicio = cellInicio.col;
                    const colFin = cellFin.col;

                    // Fusionar celdas correctamente
                    worksheet.mergeCells(row, colInicio, row, colFin);

                    // Obtener la celda resultante
                    const cell = worksheet.getCell(row, colInicio);

                    cell.value = sesion.nombre_sesion.toUpperCase();
                    cell.font = { bold: true, color: { argb: 'FFFFFFFF' } };
                    cell.alignment = { vertical: 'middle', horizontal: 'center', wrapText: true };
                    cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: color } };

                });
            });
        });


        // ================================================================
        // Leyenda de estados
        // ================================================================
        currentRowNum++;
        worksheet.getCell(`B${currentRowNum}`).value = { richText: [{ font: { bold: true, size: 12 }, text: "Leyenda de Estados:" }] };

        currentRowNum++;

        const leyendaData = [
            { text: 'Pendiente', color: 'FFFACС15' },
            { text: 'Activo / En Curso', color: 'FF22C55E' },
            { text: 'Finalizado', color: 'FF3B82F6' },
        ];

        leyendaData.forEach(item => {
            const cellColor = worksheet.getCell(`B${currentRowNum}`);
            cellColor.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: item.color } };
            cellColor.border = { top: { style: 'thin' }, left: { style: 'thin' }, bottom: { style: 'thin' }, right: { style: 'thin' } };

            const cellText = worksheet.getCell(`C${currentRowNum}`);
            cellText.value = item.text;

            currentRowNum++;
        });

        worksheet.columns.forEach(column => {
            column.width = 20;
        });

        // ================================================================
        // Generar archivo
        // ================================================================
        workbook.xlsx.writeBuffer().then(buffer => {
            const blob = new Blob([buffer], {
                type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            });
            showToast("Reporte generado correctamente!!.", "success");
            const fileName = `Programacion ${grupoStore?.infoGrupo?.modulo || 'General'}.xlsx`;
            saveAs(blob, fileName);

        }).catch(err => {
            console.error("Error al generar el Excel:", err);
            showToast("Hubo un error al generar el reporte.", "error");
        });
    };

    return { exportarCalendarioExcel };
}
