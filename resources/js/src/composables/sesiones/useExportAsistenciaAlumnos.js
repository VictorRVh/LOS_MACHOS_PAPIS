import ExcelJS from "exceljs";
import { saveAs } from "file-saver";

import useAsistenciaGrupoStore from "@/store/Asistencia/UseAsistenciaStore";
import useModalToast from "@/composables/useModalToast";

export default function useExportAsistenciaAlumnos() {

    const asistenciaStore = useAsistenciaGrupoStore();
    const { showToast } = useModalToast();

    const exportarAlumnosAsistencia = async (idGrupo) => {
        try {
            // ======================================================
            // 1) OBTENER DATOS
            // ======================================================
            await asistenciaStore.loadAsistenciaEstudents(idGrupo);

            const data = asistenciaStore.asistenciaEstudents;

            if (!data || !data.asistenciaEstudents?.length) {
                showToast("No hay asistencias registradas", "warning");
                return;
            }

            const fechas = data.fechas || [];

            // ======================================================
            // 2) CREAR EXCEL
            // ======================================================
            const workbook = new ExcelJS.Workbook();
            const worksheet = workbook.addWorksheet("Asistencias");

            const totalCols = 2 + fechas.length + 1;

            // ======================================================
            // 3) TÍTULO
            // ======================================================
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
            // 4) INFO DEL GRUPO (🔥 SIN ERRORES)
            // ======================================================
            let rowIndex = 3;

            // ESPECIALIDAD
            worksheet.getCell(`B${rowIndex}`).value = "Especialidad:";
            worksheet.getCell(`B${rowIndex}`).font = { bold: true };
            worksheet.getCell(`C${rowIndex}`).value = data.especialidad;
            worksheet.mergeCells(`C${rowIndex}:F${rowIndex}`);
            rowIndex++;

            // MÓDULO
            worksheet.getCell(`B${rowIndex}`).value = "Módulo:";
            worksheet.getCell(`B${rowIndex}`).font = { bold: true };
            worksheet.getCell(`C${rowIndex}`).value = data.modulo;
            worksheet.mergeCells(`C${rowIndex}:F${rowIndex}`);
            rowIndex++;

            // DOCENTE
            worksheet.getCell(`B${rowIndex}`).value = "Docente:";
            worksheet.getCell(`B${rowIndex}`).font = { bold: true };
            worksheet.getCell(`C${rowIndex}`).value = data.docente;
            worksheet.mergeCells(`C${rowIndex}:F${rowIndex}`);
            rowIndex++;

            // SECCIÓN / TURNO
            worksheet.getCell(`B${rowIndex}`).value = "Sección:";
            worksheet.getCell(`B${rowIndex}`).font = { bold: true };
            worksheet.getCell(`C${rowIndex}`).value = data.seccion;

            worksheet.getCell(`E${rowIndex}`).value = "Turno:";
            worksheet.getCell(`E${rowIndex}`).font = { bold: true };
            worksheet.getCell(`F${rowIndex}`).value = data.turno;

            rowIndex += 2;

            // ======================================================
            // 5) CABECERA (FECHAS)
            // ======================================================
            const fechasFormateadas = fechas.map(f => {
                const [year, month, day] = f.split("-");
                return `${day}/${month}/${year}`;
            });


            worksheet.getRow(rowIndex).values = [
                "N°",
                "Alumno",
                ...fechasFormateadas,

            ];

            worksheet.getRow(rowIndex).eachCell(cell => {
                cell.font = { bold: true, color: { argb: "FFFFFFFF" } };
                cell.fill = {
                    type: "pattern",
                    pattern: "solid",
                    fgColor: { argb: "FF007B8C" }
                };
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
            data.asistenciaEstudents.forEach((alumno, index) => {

                const asistencias = fechas.map(fecha => {
                    const estado = alumno.asistencias?.[fecha] ?? 0;

                    return estado === 1 ? "P"
                        : estado === 2 ? "F"
                            : estado === 3 ? "T"
                                : estado === 4 ? "E"//permiso
                                    : "";
                });

                // const total = asistencias.length;
                // const asistio = asistencias.filter(a => a === "P").length;
                // const porcentaje = total
                //     ? `${Math.round((asistio / total) * 100)}%`
                //     : "0%";

                // 👉 Crear fila
                const row = worksheet.addRow([
                    index + 1,
                    alumno.nombre_completo,
                    ...asistencias,
                ]);

                // 👉 Si está RETIRADO (estado = 2), pintar toda la fila de rojo
                if (alumno.estado_matricula === 2) {
                    row.eachCell((cell) => {
                        cell.fill = {
                            type: 'pattern',
                            pattern: 'solid',
                            fgColor: { argb: 'FFFFC7CE' } // rojo claro (estilo Excel)
                        };

                        cell.font = {
                            color: { argb: 'FF9C0006' }, // rojo oscuro
                            bold: true
                        };
                    });
                }

            });

            // ======================================================
            // 7) ANCHO DE COLUMNAS
            // ======================================================
            worksheet.columns = [
                { width: 5 },
                { width: 35 },
                ...fechas.map(() => ({ width: 12 })),
                { width: 12 }
            ];

            // ======================================================
            // 8) EXPORTAR
            // ======================================================
            const buffer = await workbook.xlsx.writeBuffer();

            saveAs(
                new Blob([buffer]),
                `Historial_Asistencias_${data.especialidad}_${data.modulo}_${data.seccion}.xlsx`
            );

            showToast("Reporte de asistencias generado correctamente", "success");

        } catch (error) {
            console.error(error);
            showToast("Error al generar el reporte", "error");
        }
    };

    return { exportarAlumnosAsistencia };
}
