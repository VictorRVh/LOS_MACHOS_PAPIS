// src/composables/tabla/useAlumnosMatricula.js
import ExcelJS from "exceljs";
import { saveAs } from "file-saver";
import useGrupoStore from "@/store/Grupo/useGrupoStore";
import useModalToast from "../../composables/useModalToast";


const { showConfirmModal, showToast } = useModalToast();

export default function useExportAlumnos() {

    const grupoStore = useGrupoStore();

    const exportarAlumnos = async (lista) => {

        const workbook = new ExcelJS.Workbook();
        const worksheet = workbook.addWorksheet("Matriculados");

        // ======================================================
        // 1) TÍTULO PRINCIPAL
        // ======================================================
        worksheet.mergeCells("A1:H1");
        const titleCell = worksheet.getCell("A1");
        titleCell.value = `REPORTE DE ALUMNOS MATRICULADOS`;
        titleCell.font = { bold: true, size: 16, color: { argb: "FFFFFFFF" } };
        titleCell.alignment = { horizontal: "center", vertical: "middle" };
        titleCell.fill = { type: "pattern", pattern: "solid", fgColor: { argb: "FF007B8C" } };

        // ======================================================
        // 2) INFORMACIÓN DEL GRUPO
        // ======================================================
        const infoRows = [
            ["Especialidad:", lista.especialidad || "N/A", "Módulo:", lista.modulo || "N/A"],
            ["Docente:", lista.docente || "N/A"],
            ["Sección:", lista.seccion || "N/A", "Turno:", lista.turno || "N/A"],
        ];

        let rowIndex = 3;

        infoRows.forEach((rowData) => {
            const row = worksheet.getRow(rowIndex);

            // ======================================================
            // FILA 1: Especialidad - Módulo
            // ======================================================
            if (rowData.length === 4) {
                const [label1, value1, label2, value2] = rowData;

                // Especialidad
                row.getCell(2).value = label1;
                row.getCell(2).font = { bold: true };
                row.getCell(3).value = value1;

                // Módulo
                row.getCell(5).value = label2;
                row.getCell(5).font = { bold: true };
                row.getCell(6).value = value2;

                // Alinear
                ["B", "C", "E", "F"].forEach(col => {
                    worksheet.getCell(`${col}${rowIndex}`).alignment = {
                        vertical: "middle",
                        horizontal: "left"
                    };
                });

                // Si el módulo es largo, fusionar valores C–D y F–G
                if (label2 === "Módulo:") {
                    worksheet.mergeCells(`C${rowIndex}:D${rowIndex}`);
                    worksheet.mergeCells(`F${rowIndex}:G${rowIndex}`);
                }
            }

            // ======================================================
            // FILA 2: Docente (solo una pareja)
            // ======================================================
            else if (rowData.length === 2) {
                const [label, value] = rowData;

                row.getCell(2).value = label;
                row.getCell(2).font = { bold: true };
                row.getCell(3).value = value;

                row.getCell(2).alignment = { horizontal: "left", vertical: "middle" };
                row.getCell(3).alignment = { horizontal: "left", vertical: "middle" };

                // Docente → fusionar para textos largos
                if (label === "Docente:") {
                    worksheet.mergeCells(`C${rowIndex}:G${rowIndex}`);
                }
            }

            rowIndex++;
        });

        rowIndex++; // Línea en blanco antes de la tabla

        // ======================================================
        // 3) CABECERA DE LA TABLA
        // ======================================================
        worksheet.getRow(rowIndex).values = [
            "N",
            "Nombre",
            "Tipo Doc",
            "N° Documento",
            "Sexo",
            "Celular",
            "Email",
            "Fecha nacimiento"
        ];

        worksheet.getRow(rowIndex).eachCell((cell) => {
            cell.font = { bold: true, color: { argb: "FFFFFFFF" } };
            cell.fill = { type: "pattern", pattern: "solid", fgColor: { argb: "FF007B8C" } };
            cell.alignment = { horizontal: "center" };
            cell.border = {
                top: { style: "thin" },
                left: { style: "thin" },
                bottom: { style: "thin" },
                right: { style: "thin" }
            };
        });

        rowIndex++;

        // ======================================================
        // 4) AGREGAR ALUMNOS (CORREGIDO)
        // ======================================================


        lista?.matriculados?.forEach((item, index) => {
            worksheet.addRow([
                index + 1,
                `${item.nombre} ${item.apellidos}`,
                item.tipo_documento,
                item.nro_documento,
                item.sexo,
                item.celular_personal,
                item.correo_electronico,
                item.fecha_nacimiento,
            ]);
        });

        worksheet.columns = [
            { width: 5 },
            { width: 30 },
            { width: 12 },
            { width: 15 },
            { width: 10 },
            { width: 15 },
            { width: 32 },
            { width: 18 }
        ];
    showToast("Reporte generado correctamente!!.", "success");
        // ======================================================
        // 5) GENERAR ARCHIVO
        // ======================================================
        const buffer = await workbook.xlsx.writeBuffer();
        saveAs(new Blob([buffer]), `Lista de Matrícula especialidad: ${lista?.especialidad} módulo: ${lista?.modulo} sección: ${lista?.seccion} turno: ${lista?.turno}.xlsx`);
    };

    return { exportarAlumnos };
}
