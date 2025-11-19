// src/composables/useExportExcel.js
import ExcelJS from "exceljs";
import { saveAs } from "file-saver";

export default function useExportExcel() {

    const exportarCalendarioExcel = async (sesionStore, programacionSesion, showToast) => {

        if (!sesionStore.sesion || !programacionSesion.sesiones) {
            showToast("No hay datos suficientes para generar el reporte.", "error");
            return;
        }

        const workbook = new ExcelJS.Workbook();
        const worksheet = workbook.addWorksheet('Programacion de Sesiones');

        const grupoInfo = sesionStore.grupo;
        const sesionInfo = sesionStore.sesion;

        worksheet.mergeCells('B2:E2');
        const titleCell = worksheet.getCell('B2');
        titleCell.value = 'REPORTE DE PROGRAMACIÓN DE SESIONES';
        titleCell.font = { name: 'Calibri', size: 16, bold: true, color: { argb: 'FFFFFFFF' } };
        titleCell.alignment = { vertical: 'middle', horizontal: 'center' };
        titleCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF007B8C' } };

        const headerData = [
            ['Módulo:', grupoInfo?.modulo || 'N/A', 'Docente:', grupoInfo?.docente || 'N/A'],
            ['Especialidad:', grupoInfo?.especialidad || 'N/A', 'Turno:', grupoInfo?.turno || 'N/A'],
            ['Periodo:', `${new Date(sesionInfo.fecha_inicio).toLocaleDateString()} - ${new Date(sesionInfo.fecha_fin).toLocaleDateString()}`, 'Sección:', grupoInfo?.seccion || 'N/A'],
        ];

        let currentRowNum = 4;
        headerData.forEach(rowData => {
            const row = worksheet.getRow(currentRowNum);
            row.getCell(2).value = { richText: [{ font: { bold: true }, text: rowData[0] }] };
            row.getCell(3).value = rowData[1];
            row.getCell(5).value = { richText: [{ font: { bold: true }, text: rowData[2] }] };
            row.getCell(6).value = rowData[3];

            ['B', 'C', 'E', 'F'].forEach(col => {
                const cell = worksheet.getCell(`${col}${currentRowNum}`);
                cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFFFFF00' } };
                cell.border = { top: { style: 'thin' }, left: { style: 'thin' }, bottom: { style: 'thin' }, right: { style: 'thin' } };
            });
            currentRowNum++;
        });

        const startDate = new Date(sesionInfo.fecha_inicio);
        const endDate = new Date(sesionInfo.fecha_fin);
        const dateToCellMap = new Map();

        let currentDate = new Date(startDate.getFullYear(), startDate.getMonth(), 1);
        currentRowNum += 2;

        while (currentDate <= endDate) {
            const anio = currentDate.getFullYear();
            const mes = currentDate.getMonth();
            const nombreMes = currentDate.toLocaleString('es-ES', { month: 'long' }).toUpperCase();

            worksheet.mergeCells(`A${currentRowNum}:G${currentRowNum}`);
            const monthTitleCell = worksheet.getCell(`A${currentRowNum}`);
            monthTitleCell.value = `${nombreMes} ${anio}`;
            monthTitleCell.font = { bold: true, size: 14, color: { argb: 'FF007B8C' } };
            monthTitleCell.alignment = { vertical: 'middle', horizontal: 'center' };
            currentRowNum++;

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

            const primerDiaDelMes = new Date(anio, mes, 1);
            let diaDeSemanaInicial = primerDiaDelMes.getDay();
            if (diaDeSemanaInicial === 0) diaDeSemanaInicial = 7;

            let fechaIterador = new Date(primerDiaDelMes);
            fechaIterador.setDate(primerDiaDelMes.getDate() - (diaDeSemanaInicial - 1));

            for (let semana = 0; semana < 6; semana++) {
                const row = worksheet.getRow(currentRowNum + semana);
                row.height = 60;
                for (let dia = 0; dia < 7; dia++) {
                    const cell = row.getCell(dia + 1);
                    const fechaActualStr = fechaIterador.toISOString().split('T')[0];

                    dateToCellMap.set(fechaActualStr, cell);

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

        programacionSesion.sesiones.forEach(capacidad => {
            capacidad.sesiones.forEach(sesion => {
                const color = sesion.status === 0 ? 'FFFACС15' : (sesion.status === 1 ? 'FF22C55E' : 'FF3B82F6');
                (sesion.calendario_admin || []).forEach(dia => {
                    const cell = dateToCellMap.get(dia.fecha);
                    if (cell) {
                        const numeroDia = cell.value || '';
                        cell.value = `${numeroDia}\n\n${sesion.nombre_sesion}`;
                        cell.alignment = { vertical: 'middle', horizontal: 'center', wrapText: true };
                        cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: color } };
                        cell.font = { bold: true, color: { argb: 'FFFFFFFF' } };
                    }
                });
            });
        });

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

        workbook.xlsx.writeBuffer().then(buffer => {
            const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
            const fileName = `Programacion_${grupoInfo?.modulo || 'General'}.xlsx`;
            saveAs(blob, fileName);
        }).catch(err => {
            console.error("Error al generar el Excel:", err);
            showToast("Hubo un error al generar el reporte.", "error");
        });
    };

    return {
        exportarCalendarioExcel
    };
}
