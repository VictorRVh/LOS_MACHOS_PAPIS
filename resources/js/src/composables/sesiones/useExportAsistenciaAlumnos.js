// src/composables/tabla/useAlumnosMatricula.js
import ExcelJS from "exceljs";
import { saveAs } from "file-saver";

import useAsistenciaGrupoStore from "@/store/Asistencia/UseAsistenciaStore";
import useModalToast from "@/composables/useModalToast";

const { showToast } = useModalToast();

export default function useExportAsistenciaAlumnos() {


    const asistenciaStore = useAsistenciaGrupoStore();

    const exportarAlumnosAsistencia = async (idGrupo) => {
        try {
            // ======================================================
            // 1) OBTENER DATOS
            // ======================================================
          
            await asistenciaStore.loadAsistenciaEstudents(idGrupo);

  
            const data = asistenciaStore.asistenciaEstudents;

            if (!data || !data?.asistenciaEstudents?.length) {
                showToast("No hay asistencias registradas", "warning");
                return;
            }

            const fechas = data?.fechas;

            // ======================================================
            // 2) CREAR EXCEL
            // ======================================================
            const workbook = new ExcelJS.Workbook();
            const worksheet = workbook.addWorksheet("Asistencias");

            // ======================================================
            // 3) TÍTULO
            // ======================================================
            const totalCols = 2 + fechas.length + 1;
            worksheet.mergeCells(1, 1, 1, totalCols);

            const titleCell = worksheet.getCell(1, 1);
            titleCell.value = "REPORTE DE HISTORIAL DE ASISTENCIAS";
            titleCell.font = { bold: true, size: 16, color: { argb: "FFFFFFFF" } };
            titleCell.alignment = { horizontal: "center", vertical: "middle" };
            titleCell.fill = {
                type: "pattern",
                pattern: "solid",
                fgColor: { argb: "FF007B8C" }
            };

            // ======================================================
            // 4) INFO DEL GRUPO
            // ======================================================
            let rowIndex = 3;

            const infoRows = [
                ["Especialidad:", data?.especialidad],
                ["Módulo:", data?.modulo],
                ["Docente:", data?.docente],
                ["Sección:", data?.seccion, "Turno:", data?.turno],
            ];

            infoRows.forEach(rowData => {
                const row = worksheet.getRow(rowIndex);

                row.getCell(2).value = rowData[0];
                row.getCell(2).font = { bold: true };
                row.getCell(3).value = rowData[1];

                if (rowdata?.length === 4) {
                    row.getCell(5).value = rowData[2];
                    row.getCell(5).font = { bold: true };
                    row.getCell(6).value = rowData[3];
                    worksheet.mergeCells(`C${rowIndex}:D${rowIndex}`);
                    worksheet.mergeCells(`F${rowIndex}:G${rowIndex}`);
                } else {
                    worksheet.mergeCells(`C${rowIndex}:G${rowIndex}`);
                }

                rowIndex++;
            });

            rowIndex++;

            // ======================================================
            // 5) CABECERA (FECHAS)
            // ======================================================
            worksheet.getRow(rowIndex).values = [
                "N°",
                "Alumno",
                ...fechas,
                "% Asist."
            ];

            worksheet.getRow(rowIndex).eachCell(cell => {
                cell.font = { bold: true, color: { argb: "FFFFFFFF" } };
                cell.fill = { type: "pattern", pattern: "solid", fgColor: { argb: "FF007B8C" } };
                cell.alignment = { horizontal: "center", vertical: "middle" };
                cell.border = {
                    top: { style: "thin" },
                    left: { style: "thin" },
                    bottom: { style: "thin" },
                    right: { style: "thin" }
                };
            });

            rowIndex++;

            // ======================================================
            // 6) FILAS DE ASISTENCIA
            // ======================================================
            data?.asistenciaEstudents.forEach((alumno, index) => {

                let asistencias = fechas.map(fecha => {
                    const estado = alumno.asistencias[fecha] ?? 0;

                    return estado === 1 ? "✔"
                         : estado === 2 ? "✖"
                         : estado === 3 ? "T"
                         : estado === 4 ? "P"
                         : "";
                });

                const total = asistencias.length;
                const asistio = asistencias.filter(a => a === "✔").length;
                const porcentaje = total ? `${Math.round((asistio / total) * 100)}%` : "0%";

                worksheet.addRow([
                    index + 1,
                    alumno.nombre_completo,
                    ...asistencias,
                    porcentaje
                ]);
            });

            // ======================================================
            // 7) ANCHO DE COLUMNAS
            // ======================================================
            worksheet.columns = [
                { width: 5 },
                { width: 30 },
                ...fechas.map(() => ({ width: 12 })),
                { width: 12 }
            ];

            // ======================================================
            // 8) EXPORTAR
            // ======================================================
            const buffer = await workbook.xlsx.writeBuffer();
            saveAs(
                new Blob([buffer]),
                `Historial_Asistencias_${data?.especialidad}_${data?.modulo}_${data?.seccion}.xlsx`
            );

            showToast("Reporte de asistencias generado correctamente", "success");

        } catch (error) {
            console.error(error);
            showToast("Error al generar el reporte", "error");
        }
    };

    return { exportarAlumnosAsistencia };
}
