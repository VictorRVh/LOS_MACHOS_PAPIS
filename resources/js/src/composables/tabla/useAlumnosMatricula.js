// src/composables/tabla/useAlumnosMatricula.js
import ExcelJS from "exceljs";
import { saveAs } from "file-saver";
import useGrupoStore from "@/store/Grupo/useGrupoStore";
import useModalToast from "../../composables/useModalToast";

const { showToast } = useModalToast();

export default function useExportAlumnos() {

    const exportarAlumnos = async (lista) => {

        const workbook = new ExcelJS.Workbook();
        const worksheet = workbook.addWorksheet("Matriculados");

        // ======================================================
        // 1) TÍTULO PRINCIPAL
        // ======================================================
        worksheet.mergeCells("A1:K1");
        const titleCell = worksheet.getCell("A1");
        titleCell.value = "REPORTE DE ALUMNOS MATRICULADOS";
        titleCell.font = { bold: true, size: 16, color: { argb: "FFFFFFFF" } };
        titleCell.alignment = { horizontal: "center", vertical: "middle" };
        titleCell.fill = {
            type: "pattern",
            pattern: "solid",
            fgColor: { argb: "FF007B8C" },
        };

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

            if (rowData.length === 4) {
                const [label1, value1, label2, value2] = rowData;

                row.getCell(2).value = label1;
                row.getCell(2).font = { bold: true };
                row.getCell(3).value = value1;

                row.getCell(5).value = label2;
                row.getCell(5).font = { bold: true };
                row.getCell(6).value = value2;

                worksheet.mergeCells(`C${rowIndex}:D${rowIndex}`);
                worksheet.mergeCells(`F${rowIndex}:G${rowIndex}`);
            }

            else if (rowData.length === 2) {
                const [label, value] = rowData;

                row.getCell(2).value = label;
                row.getCell(2).font = { bold: true };
                row.getCell(3).value = value;

                worksheet.mergeCells(`C${rowIndex}:G${rowIndex}`);
            }

            rowIndex++;
        });

        rowIndex++;

        // ======================================================
        // 3) CABECERA DE LA TABLA (incluye datos de pago)
        // ======================================================
        worksheet.getRow(rowIndex).values = [
            "N°",
            "Nombre",
            "Tipo Doc",
            "N° Documento",
            "Sexo",
            "Celular",
            "Email",
            "Fecha Nac.",
            "Condición de Pago",
            "N° Recibo",
            "Aporte",
        ];

        worksheet.getRow(rowIndex).eachCell((cell) => {
            cell.font = { bold: true, color: { argb: "FFFFFFFF" } };
            cell.fill = { type: "pattern", pattern: "solid", fgColor: { argb: "FF007B8C" } };
            cell.alignment = { horizontal: "center" };
            cell.border = {
                top: { style: "thin" },
                left: { style: "thin" },
                bottom: { style: "thin" },
                right: { style: "thin" },
            };
        });

        rowIndex++;

        // ======================================================
        // 4) AGREGAR ALUMNOS (CON columnas de pago)
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

                // 🟩 DATOS DEL PAGO (SIN estado_pago)
                item.condicion,
                item.nro_recibo,
                item.aporte
            ]);
        });

        // ======================================================
        // 5) ANCHO DE COLUMNAS
        // ======================================================
        worksheet.columns = [
            { width: 5 },
            { width: 30 },
            { width: 12 },
            { width: 15 },
            { width: 10 },
            { width: 15 },
            { width: 32 },
            { width: 15 },
            { width: 20 },
            { width: 15 },
            { width: 12 }
        ];

        // ======================================================
        // 6) GENERAR ARCHIVO
        // ======================================================
        const buffer = await workbook.xlsx.writeBuffer();

        saveAs(
            new Blob([buffer]),
            `Lista de Matrícula - ${lista.especialidad} - ${lista.modulo} - ${lista.seccion} - ${lista.turno}.xlsx`
        );

        showToast("Reporte generado correctamente!!", "success");
    };

    return { exportarAlumnos };
}
