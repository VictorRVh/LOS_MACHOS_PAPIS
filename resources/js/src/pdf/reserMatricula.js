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
        ["Nombre del CETPRO", "CETPRO PUNO"],
        ["Estudiante", data.apellidos_nombres],
    ];

    autoTable(doc, {
        startY: y,
        body: tabla2,
        theme: "grid",
        styles: { cellPadding: 3, fontSize: 11 },
        columnStyles: {
            0: { cellWidth: 60 },
            1: { cellWidth: 120 },
        }
    });

    y = doc.lastAutoTable.finalY + 10;

    // ============================================
    //        2) TABLA DE 4 COLUMNAS
    // ============================================
    const tabla4 = [
        ["DNI", data.nro_documento, "Edad", data.edad + " años"],
        ["Estado civil", data.estado_civil, "Turno", data.turno],
        ["Teléfono", data.telefono, "Correo", data.correo_electronico],
    ];

    autoTable(doc, {
        startY: y,
        body: tabla4,
        theme: "grid",
        styles: { cellPadding: 3, fontSize: 11 },
        columnStyles: {
            0: { cellWidth: 40 },
            1: { cellWidth: 50 },
            2: { cellWidth: 40 },
            3: { cellWidth: 60 },
        }
    });

    y = doc.lastAutoTable.finalY + 10;

    // ============================================
    //        3) TABLA DE 6 COLUMNAS
    // ============================================
    const tabla6 = [
        ["Especialidad", data.especialidad, "Módulo", data.modulo, "Fecha reserva", data.fecha_reserva ?? "—"]
    ];

    autoTable(doc, {
        startY: y,
        body: tabla6,
        theme: "grid",
        styles: { cellPadding: 3, fontSize: 11 },
        columnStyles: {
            0: { cellWidth: 30 },
            1: { cellWidth: 30 },
            2: { cellWidth: 30 },
            3: { cellWidth: 30 },
            4: { cellWidth: 40 },
            5: { cellWidth: 40 },
        }
    });

    // ============================================
    //                  FIRMAS
    // ============================================
    y = doc.lastAutoTable.finalY + 30;

    doc.setFontSize(12);
    doc.text("_____________________________", 25, y);
    doc.text("DIRECTOR", 55, y + 8);

    doc.text("_____________________________", 125, y);
    doc.text("ESTUDIANTE", 150, y + 8);

    // ABRIR PDF
    const pdfBlob = doc.output("blob");
    const pdfUrl = URL.createObjectURL(pdfBlob);
    window.open(pdfUrl, "_blank");
}
