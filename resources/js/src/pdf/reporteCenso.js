import ExcelJS from "exceljs";
import { saveAs } from "file-saver";

export async function exportarCensoEducativo() {
    const workbook = new ExcelJS.Workbook();
    const worksheet = workbook.addWorksheet("Censo Educativo");

    /* ===============================
     * CONFIGURACIÓN GENERAL
     * =============================== */
    worksheet.columns = [
        { width: 5 },   // A
        { width: 18 },  // B
        { width: 35 },  // C
        { width: 12 },  // D
        { width: 12 },  // E
        { width: 10 },  // F
        { width: 10 },  // G
        { width: 10 },  // H
    ];

    const center = {
        vertical: "middle",
        horizontal: "center",
    };

    const borderAll = {
        top: { style: "thin" },
        left: { style: "thin" },
        bottom: { style: "thin" },
        right: { style: "thin" },
    };

    /* ===============================
     * ENCABEZADO
     * =============================== */
    worksheet.mergeCells("A1:C2");
    worksheet.getCell("A1").value = "CENSO EDUCATIVO";
    worksheet.getCell("A1").font = { bold: true, size: 14 };
    worksheet.getCell("A1").alignment = center;

    worksheet.mergeCells("D1:F2");
    worksheet.getCell("D1").value = "2024\nCENSO EDUCATIVO\nEducación Técnico Productiva";
    worksheet.getCell("D1").alignment = {
        vertical: "middle",
        horizontal: "center",
        wrapText: true,
    };
    worksheet.getCell("D1").font = { bold: true };

    worksheet.mergeCells("G1:H2");
    worksheet.getCell("G1").value = "CÉDULA\n9A";
    worksheet.getCell("G1").alignment = center;
    worksheet.getCell("G1").font = { bold: true };

    /* ===============================
     * IDENTIFICACIÓN DEL SERVICIO
     * =============================== */
    worksheet.mergeCells("A4:H4");
    worksheet.getCell("A4").value = "IDENTIFICACIÓN DEL SERVICIO EDUCATIVO";
    worksheet.getCell("A4").font = { bold: true };
    worksheet.getCell("A4").alignment = center;
    worksheet.getCell("A4").border = borderAll;

    const info = [
        ["1. CÓDIGO MODULAR:", "0240069"],
        ["2. CÓDIGO DE LOCAL EDUCATIVO:", "441744"],
        ["3. NOMBRE DEL SERVICIO EDUCATIVO:", "CETPRO ILAVE"],
        ["4. DISTRITO:", "ILAVE"],
    ];

    let rowIndex = 5;
    info.forEach(([label, value]) => {
        worksheet.mergeCells(`A${rowIndex}:C${rowIndex}`);
        worksheet.mergeCells(`D${rowIndex}:H${rowIndex}`);

        worksheet.getCell(`A${rowIndex}`).value = label;
        worksheet.getCell(`D${rowIndex}`).value = value;

        worksheet.getCell(`A${rowIndex}`).border = borderAll;
        worksheet.getCell(`D${rowIndex}`).border = borderAll;

        rowIndex++;
    });

    /* ===============================
     * TABLA DE TURNOS
     * =============================== */
    rowIndex++;
    worksheet.mergeCells(`A${rowIndex}:H${rowIndex}`);
    worksheet.getCell(`A${rowIndex}`).value = "TURNOS Y DÍAS DE LA SEMANA";
    worksheet.getCell(`A${rowIndex}`).font = { bold: true };
    worksheet.getCell(`A${rowIndex}`).alignment = center;

    rowIndex++;

    const dias = ["TURNO", "LU", "MA", "MI", "JU", "VI", "SA", "DO"];
    worksheet.addRow(dias);

    dias.forEach((_, i) => {
        const cell = worksheet.getRow(rowIndex).getCell(i + 1);
        cell.font = { bold: true };
        cell.alignment = center;
        cell.border = borderAll;
    });

    const turnos = [
        ["Mañana", "X", "X", "X", "X", "X", "", ""],
        ["Tarde", "X", "X", "X", "X", "X", "", ""],
        ["Noche", "X", "X", "X", "X", "X", "", ""],
    ];

    turnos.forEach((t) => {
        const r = worksheet.addRow(t);
        r.eachCell((cell) => {
            cell.alignment = center;
            cell.border = borderAll;
        });
    });

    /* ===============================
     * TABLA DE PROGRAMAS
     * =============================== */
    rowIndex = worksheet.lastRow.number + 2;

    worksheet.mergeCells(`A${rowIndex}:H${rowIndex}`);
    worksheet.getCell(`A${rowIndex}`).value =
        "OPCIONES OCUPACIONALES Y/O PROGRAMAS DE ESTUDIO";
    worksheet.getCell(`A${rowIndex}`).font = { bold: true };
    worksheet.getCell(`A${rowIndex}`).alignment = center;

    rowIndex++;

    const headers = [
        "N°",
        "CÓDIGO",
        "DENOMINACIÓN",
        "HORAS",
        "CRÉDITOS",
        "MAÑANA",
        "TARDE",
        "NOCHE",
    ];

    worksheet.addRow(headers);

    headers.forEach((_, i) => {
        const cell = worksheet.getRow(rowIndex).getCell(i + 1);
        cell.font = { bold: true };
        cell.alignment = center;
        cell.border = borderAll;
    });

    const programas = [
        [1, "CAT7236186", "CONFECCIÓN TEXTIL", 264, 0, 2, 0, 1],
        [2, "CAT7326201", "CARPINTERÍA", 264, 0, 0, 1, 0],
        [3, "CAT7136309", "CONSTRUCCIÓN METÁLICAS", 264, 0, 0, 1, 0],
        [4, "CAT0126262", "ASISTENCIA EN COCINA", 264, 0, 1, 1, 1],
        [5, "CAT7216263", "PANADERÍA Y PASTELERÍA", 264, 0, 1, 0, 1],
    ];

    programas.forEach((p) => {
        const r = worksheet.addRow(p);
        r.eachCell((cell) => {
            cell.border = borderAll;
            cell.alignment = center;
        });
        r.getCell(3).alignment = { vertical: "middle", horizontal: "left" };
    });

    /* ===============================
     * EXPORTAR
     * =============================== */
    const buffer = await workbook.xlsx.writeBuffer();
    const blob = new Blob([buffer], {
        type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    });

    saveAs(blob, "Censo_Educativo_2024.xlsx");
}
