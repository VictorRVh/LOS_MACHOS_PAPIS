import jsPDF from "jspdf";
import autoTable from "jspdf-autotable";

export function generatePdfReservaMatricula(data) {

    const doc = new jsPDF({
        orientation: "portrait",
        unit: "mm",
        format: "a4",
        compress: true
    });

    const pageW = doc.internal.pageSize.getWidth();
    const pageH = doc.internal.pageSize.getHeight();
    const margin = 20;
    const primaryColor = [0, 0, 0]; // Azul Ejecutivo
    const secondaryColor = [60, 60, 60];

    const cetpro = data?.cetpro || {};
    const nombreCetpro = (cetpro?.cetpro || "PUNO").toUpperCase();

    // ============================================
    //      1. ENCABEZADO TÉCNICO (EQUILIBRADO)
    // ============================================
    const drawHeader = () => {
        // --- LOGO CETPRO (IZQUIERDA) ---
        try {
            doc.addImage("/img/CETPRO_Image.png", "PNG", margin, 12, 18, 20, undefined, 'FAST');
        } catch (e) {}

        // --- BLOQUE CENTRAL (TEXTO TÉCNICO) ---
        doc.setTextColor(0);
        doc.setFont("helvetica", "bold");
        doc.setFontSize(9);
        doc.text("CENTRO DE EDUCACIÓN TÉCNICO-PRODUCTIVA", pageW / 2, 16, { align: "center" });
        
        doc.setFontSize(13);
        doc.text(`"${nombreCetpro}"`, pageW / 2, 22, { align: "center" });

        doc.setFont("helvetica", "normal");
        doc.setFontSize(7.5);
        doc.setTextColor(secondaryColor[0], secondaryColor[1], secondaryColor[2]);
        const subHeader = `R.D. N° ${cetpro.rd_autorizacion || '07592-2024'} | CÓDIGO MODULAR: ${cetpro.codigo_modular || '469452'}`;
        doc.text(subHeader, pageW / 2, 27, { align: "center" });
        
        doc.setFont("helvetica", "italic");
        doc.text("Lineamientos Académicos Generales - RVM N° 188-2020-MINEDU", pageW / 2, 31, { align: "center" });

        // --- R1 (DERECHA - GRANDE Y SECO) ---
        doc.setTextColor(0);
        doc.setFont("helvetica", "bold");
        doc.setFontSize(28);
        doc.text("R1", pageW - margin, 26, { align: "right" });

        // Línea inferior de cierre de encabezado
        doc.setDrawColor(0);
        doc.setLineWidth(0.4);
        doc.line(margin, 36, pageW - margin, 36);
    };

    const drawFooter = () => {
        const footerY = pageH - 12;
        doc.setFont("helvetica", "normal");
        doc.setFontSize(7);
        doc.setTextColor(150);
        doc.text(`Identificador de Proceso Académico R1 - Reserva de Vacante de Matrícula`, margin, footerY);
        doc.text(`Página 1 de 1`, pageW - margin, footerY, { align: "right" });
    };

    drawHeader();
    drawFooter();

    // ============================================
    //      2. TÍTULO DEL DOCUMENTO
    // ============================================
    let y = 48;
    doc.setFont("helvetica", "bold");
    doc.setFontSize(13);
    doc.setTextColor(primaryColor[0], primaryColor[1], primaryColor[2]);
    doc.text("CONSTANCIA DE RESERVA DE VACANTE", pageW - margin, y, { align: "right" });
    
    doc.setFontSize(9);
    doc.setFont("helvetica", "normal");
    doc.setTextColor(secondaryColor[0], secondaryColor[1], secondaryColor[2]);
    doc.text(`Periodo Lectivo: ${data.periodo}`, pageW - margin, y + 5, { align: "right" });

    y += 15;
    
    // Texto de Referencia Legal
    doc.setFont("helvetica", "normal");
    doc.setFontSize(9);
    doc.setTextColor(40);
    const refText = "Por intermedio de la presente, la institución garantiza la disponibilidad de vacante para el proceso de matrícula, sujeta a la ratificación del postulante según la normativa vigente:";
    doc.text(doc.splitTextToSize(refText, pageW - (margin * 2)), margin, y);

    y += 10;

    const tableStyle = {
        theme: 'plain',
        margin: { left: margin, right: margin },
        styles: { fontSize: 10, cellPadding: 1.5, font: "helvetica", textColor: [20, 20, 20] },
        columnStyles: { 
            0: { fontStyle: 'bold', textColor: primaryColor, cellWidth: 40 },
            2: { fontStyle: 'bold', textColor: primaryColor, cellWidth: 35 } 
        }
    };

    // --- I. DATOS DEL POSTULANTE ---
    doc.setFont("helvetica", "bold");
    doc.setFontSize(8);
    doc.setTextColor(150);
    doc.text("I. DATOS DEL ESTUDIANTE", margin, y);

    autoTable(doc, {
        ...tableStyle,
        startY: y + 2,
        body: [
            ["APELLIDOS Y NOMBRES:", (data.apellidos_nombres || "").toUpperCase()],
            ["NRO. DOCUMENTO:", data.nro_documento || "-", "TELÉFONO:", data.telefono || "-"]
        ]
    });

    // --- II. ESPECIFICACIONES DE LA RESERVA ---
    y = doc.lastAutoTable.finalY + 8;
    doc.text("II. ESPECIFICACIONES TÉCNICAS", margin, y);

    function invertirFechaSimple(fecha) {
        if (!fecha) return "—";
        const parts = String(fecha).split(/[-/]/);
        return parts.length === 3 ? `${parts[2]}/${parts[1]}/${parts[0]}` : fecha;
    }

    autoTable(doc, {
        ...tableStyle,
        startY: y + 2,
        body: [
            ["PROGRAMA:", data.especialidad || "-", "MÓDULO:", data.modulo || "-"],
            ["SECCIÓN:", data.seccion || "-", "TURNO:", data.turno || "-"],
            ["CRÉDITOS:", data.creditos || "—", "FECHA DE MATRÍCULA:", invertirFechaSimple(data.fecha_reserva)],
            ["FECHA DE EMISIÓN:", new Date(data.created_at).toLocaleDateString('es-PE'), "ESTADO:", "RESERVADO"]
        ]
    });

    // --- NOTA DE PIE ---
    y = doc.lastAutoTable.finalY + 12;
    doc.setFillColor(248, 248, 248);
    doc.rect(margin, y, pageW - (margin * 2), 15, 'F');
    
    doc.setFont("helvetica", "italic");
    doc.setFontSize(8);
    doc.setTextColor(100);
    const mensaje = "La reserva de vacante es un acto administrativo previo a la matrícula. El estudiante deberá presentar su expediente académico completo para la generación de la nómina oficial según el cronograma.";
    doc.text(doc.splitTextToSize(mensaje, pageW - (margin * 2) - 10), margin + 5, y + 6);

    // ============================================
    //      3. FIRMAS EJECUTIVAS
    // ============================================
    const signY = pageH - 45;
    doc.setDrawColor(primaryColor[0], primaryColor[1], primaryColor[2]);
    doc.setLineWidth(0.5);

    doc.line(margin + 5, signY, margin + 65, signY);
    doc.setFont("helvetica", "bold");
    doc.setFontSize(9);
    doc.setTextColor(0);
    doc.text("DIRECCIÓN ACADÉMICA", margin + 35, signY + 5, { align: "center" });

    doc.line(pageW - margin - 65, signY, pageW - margin - 5, signY);
    doc.text("FIRMA DEL ESTUDIANTE", pageW - margin - 35, signY + 5, { align: "center" });
    doc.setFont("helvetica", "normal");
    doc.setFontSize(8);
    doc.text(`DNI: ${data.nro_documento}`, pageW - margin - 35, signY + 9, { align: "center" });

    window.open(URL.createObjectURL(doc.output("blob")), "_blank");
}