import jsPDF from "jspdf";
import autoTable from "jspdf-autotable";

export function generatePdfReservaMatricula(data) {
    const doc = new jsPDF();

    // LOGO
    doc.addImage("img/choclon.jpg", "JPEG", 15, 10, 35, 20);

    // TITULO
    doc.setFont("helvetica", "bold");
    doc.setFontSize(22);
    doc.text("RESERVA DE MATRÍCULA", 105, 20, { align: "center" });
    doc.setFontSize(14);
    doc.text("AÑO 2025", 105, 28, { align: "center" });

    let y = 40;

    // ============================================
    //        1) TABLA DE 2 COLUMNAS
    // ============================================
    const tabla2 = [
        ["Nombre de la institución:", "CETPRO ILAVE"],

    ];

    autoTable(doc, {
        startY: y,
        body: tabla2,
        theme: "grid",
        styles: { cellPadding: 3, fontSize: 11 },
        columnStyles: {
            0: { cellWidth: 60, fontStyle: "bold" },
            1: { cellWidth: 130 },
        }
    });

    y = doc.lastAutoTable.finalY;

    // ============================================
    //        2) TABLA DE 4 COLUMNAS
    // ============================================

    const tabla4 = [
        ["Estudiante:", data.apellidos_nombres],

    ];

    autoTable(doc, {
        startY: y,
        body: tabla4,
        theme: "grid",
        styles: { cellPadding: 3, fontSize: 11 },
        columnStyles: {
            0: { cellWidth: 40, fontStyle: "bold" },
            1: { cellWidth: 150 },
        }
    });

    // ❗ IMPORTANTE — ESTO FALTABA
    y = doc.lastAutoTable.finalY;

    const tabla6 = [
        ["DNI:", data.nro_documento, "Teléfono:", data.telefono],
        // ["Estado civil", data.estado_civil, "Turno", data.turno],
        // ["Teléfono", data.telefono, "Correo", data.correo_electronico],
    ];

    autoTable(doc, {
        startY: y,
        body: tabla6,
        theme: "grid",
        styles: { cellPadding: 3, fontSize: 11 },
        columnStyles: {
            0: { cellWidth: 40, fontStyle: "bold" },
            1: { cellWidth: 50 },
            2: { cellWidth: 40, fontStyle: "bold" },
            3: { cellWidth: 60 },
        }
    });

    y = doc.lastAutoTable.finalY + 10;


    const tabla8 = [
        ["Especialidad:", data.especialidad, "Módulo:", data.modulo],
        // ["Estado civil", data.estado_civil, "Turno", data.turno],
        // ["Teléfono", data.telefono, "Correo", data.correo_electronico],
    ];

    autoTable(doc, {
        startY: y,
        body: tabla8,
        theme: "grid",
        styles: { cellPadding: 3, fontSize: 11 },
        columnStyles: {
            0: { cellWidth: 31, fontStyle: "bold" },
            1: { cellWidth: 59 },
            2: { cellWidth: 21, fontStyle: "bold" },
            3: { cellWidth: 79 },
        }
    });

    // ❗ IMPORTANTE — ESTO FALTABA
    y = doc.lastAutoTable.finalY;


    // ============================================
    //        3) TABLA DE 6 COLUMNAS
    // ============================================
    const tabla10 = [
        ["Sección:", data.seccion, "Turno:", data.turno, "Créditos:", data.creditos ?? "—"]
    ];

    autoTable(doc, {
        startY: y,
        body: tabla10,
        theme: "grid",
        styles: { cellPadding: 3, fontSize: 11 },
        columnStyles: {
            0: { cellWidth: 31, fontStyle: "bold" },
            1: { cellWidth: 29 },
            2: { cellWidth: 30, fontStyle: "bold" },
            3: { cellWidth: 30 },
            4: { cellWidth: 35, fontStyle: "bold" },
            5: { cellWidth: 35 },
        }
    });
function invertirFechaSimple(fecha) {
    if (!fecha) return "—";

    fecha = String(fecha).replaceAll("-", "/");

    const [anio, mes, dia] = fecha.split("/");
    return `${dia}/${mes}/${anio}`;
}

    // ❗ IMPORTANTE — ESTO FALTABA
    y = doc.lastAutoTable.finalY;
    const tabla12 = [
        ["Fecha de matrícula:", new Date(data.created_at)
            .toLocaleDateString('es-PE'), "Fecha de reserva:", invertirFechaSimple(data.fecha_reserva)],
        // ["Estado civil", data.estado_civil, "Turno", data.turno],
        // ["Teléfono", data.telefono, "Correo", data.correo_electronico],
    ];

    autoTable(doc, {
        startY: y,
        body: tabla12,
        theme: "grid",
        styles: { cellPadding: 3, fontSize: 11 },
        columnStyles: {
            0: { cellWidth: 55, fontStyle: "bold" },
            1: { cellWidth: 40 },
            2: { cellWidth: 55, fontStyle: "bold" },
            3: { cellWidth: 40 },
        }
    });


    // ============================================
    //                  FIRMAS
    // ============================================
    y = doc.lastAutoTable.finalY + 50;

    doc.setFontSize(12);
    doc.text("_____________________________", 25, y);
    doc.text("DIRECCIÓN / COORDINACIÓN", 28, y + 8);

    doc.text("_____________________________", 125, y);
    doc.text("ESTUDIANTE", 145, y + 8);

    // ABRIR PDF
    const pdfBlob = doc.output("blob");
    const pdfUrl = URL.createObjectURL(pdfBlob);
    window.open(pdfUrl, "_blank");
}
