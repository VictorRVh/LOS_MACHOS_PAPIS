import jsPDF from "jspdf";
import autoTable from "jspdf-autotable";
import useHttpRequest from "../composables/useHttpRequest";
import useModalToast from "../composables/useModalToast";

const { store: createDocumento } = useHttpRequest("/estudiante-documento");
const { showToast } = useModalToast();

function formatearFechaActual(lugar = "PUNO") {
  const meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
  const hoy = new Date();
  return `${lugar}, ${hoy.getDate()} de ${meses[hoy.getMonth()]} de ${hoy.getFullYear()}`;
}

export async function generateCertificadoEstudio(data, codigo, meta = {}) {
  try {
    const datosCetpro = data?.cetpro || {};
    const doc = new jsPDF({ 
      orientation: "portrait", 
      unit: "mm", 
      format: "a4",
      compress: true 
    });

    const pageW = 210;
    const margin = 20;
    const rightEdge = pageW - margin;

    const nombreCetpro = (datosCetpro?.cetpro || "PUNO").toUpperCase();
    const tipoGestion = (datosCetpro?.tipo_gestion || "PÚBLICO").toUpperCase();
    const estudiante = (data?.apellidos_nombres || "").toUpperCase();
    const especialidad = (data?.especialidad || "").toUpperCase();
    const moduloStr = (data?.modulo || "").toUpperCase();
    const unidades = Array.isArray(data?.unidades_didacticas) ? data.unidades_didacticas : [];

    // --- ENCABEZADO ---
    try { doc.addImage("/img/LogoMinisterio.png", "PNG", margin, 10, 49, 12, undefined, 'FAST'); } catch (e) {}
    try { doc.addImage("/img/CetproLOGOO.png", "PNG", (pageW / 2) - 10, 8, 18, 20, undefined, 'FAST'); } catch (e) {}

    doc.setLineWidth(0.2);
    doc.rect(rightEdge - 25, 10, 25, 30); 
    doc.setFont("helvetica", "normal");
    doc.setFontSize(8);
    doc.text("FOTO", rightEdge - 12.5, 25, { align: "center" });

    let y = 48;
    doc.setFont("helvetica", "bold"); doc.setFontSize(11);
    doc.text(`CENTRO DE EDUCACIÓN TÉCNICO-PRODUCTIVA (${tipoGestion})`, pageW / 2, y, { align: "center" });
    y += 6;
    doc.text(`"${nombreCetpro}"`, pageW / 2, y, { align: "center" });
    y += 12;
    doc.setFontSize(13);
    doc.text("CERTIFICADO DE ESTUDIOS", pageW / 2, y, { align: "center" });

    // --- TEXTO ---
    y += 12;
    doc.setFont("helvetica", "normal"); doc.setFontSize(11);
    doc.text("El CETPRO ", margin, y);
    doc.setFont("helvetica", "bold");
    doc.text(nombreCetpro + ",", margin + doc.getTextWidth("El CETPRO "), y);

    y += 7;
    doc.setFont("helvetica", "normal");
    doc.text("certifica que ", margin, y);
    doc.setFont("helvetica", "bold");
    doc.text(estudiante, margin + doc.getTextWidth("certifica que "), y);

    y += 7;
    doc.setFont("helvetica", "normal");
    doc.text("ha cursado las unidades didácticas, que se indican en el programa de estudios:", margin, y);
    y += 8;
    doc.setFont("helvetica", "bold");
    doc.text(especialidad, margin, y);
    y += 10;
    doc.setFont("helvetica", "normal");
    doc.text("Los resultados finales de las evaluaciones fueron las siguientes:", margin, y);

    // --- TABLA DINÁMICA ---
    const tableBody = unidades.map((u, index) => {
      const row = [];
      if (index === 0) {
        row.push({ 
          content: moduloStr, 
          rowSpan: unidades.length, 
          styles: { halign: 'center', valign: 'middle' } 
        });
      }
      row.push(u?.nombre_unidad || "");
      row.push(u?.creditos || "");
      row.push(u?.nota || "");
      row.push(meta?.anio || new Date().getFullYear());
      row.push(meta?.periodo || "-");
      row.push("");
      return row;
    });

    autoTable(doc, {
      startY: y + 5,
      margin: { left: margin, right: margin },
      theme: "grid",
      head: [["Módulo", "Unidad didáctica", "Número de créditos", "Calificación", "Año", "Periodo académico", "Observaciones"]],
      body: tableBody,
      styles: {
        font: "helvetica",
        fontSize: 7,
        lineColor: [0, 0, 0],
        lineWidth: 0.15,
        textColor: [0, 0, 0], // Asegura texto negro en toda la tabla
      },
      headStyles: {
        fillColor: [225, 225, 225],
        textColor: [0, 0, 0], // TEXTO NEGRO PARA CABECERA
        fontStyle: "bold",
        halign: "center",
      },
      columnStyles: {
        0: { cellWidth: 15 }, 
        1: { cellWidth: 57 },
        2: { cellWidth: 18, halign: "center" },
        3: { cellWidth: 18, halign: "center" },
        4: { cellWidth: 12, halign: "center" },
        5: { cellWidth: 25, halign: "center" },
        6: { cellWidth: 25 },
      },
      didDrawCell: (data) => {
        // CORRECCIÓN DE POSICIÓN DEL MÓDULO
        if (data.section === 'body' && data.column.index === 0) {
          if (data.cell.raw && data.cell.raw.rowSpan) {
            const cell = data.cell;
            
            // Limpiar la celda
            doc.setFillColor(255, 255, 255);
            doc.rect(cell.x + 0.1, cell.y + 0.1, cell.width - 0.2, cell.height - 0.2, 'F');
            
            doc.setFont("helvetica", "bold");
            doc.setFontSize(7.5);
            doc.setTextColor(0, 0, 0);
            
            // Centro exacto de la celda combinada
            const centerX = cell.x + (cell.width / 2);
            const centerY = cell.y + (cell.height / 2);
            
            doc.text(moduloStr, centerX, centerY, {
              angle: 90,
              align: 'center',
              baseline: 'middle'
            });
          }
        }
      }
    });

    // --- PIE DE PÁGINA ---
    let finalY = doc.lastAutoTable.finalY + 15;
    if (finalY > 260) { doc.addPage(); finalY = 30; }

    doc.setFont("helvetica", "normal");
    doc.setFontSize(10);
    doc.text(`Lugar y fecha: ${formatearFechaActual(datosCetpro?.lugar)}`, rightEdge, finalY, { align: "right" });

    const signY = finalY + 30;
    doc.setLineWidth(0.5);
    doc.line((pageW / 2) - 35, signY, (pageW / 2) + 35, signY);
    
    doc.setFont("helvetica", "bold");
    doc.text("DIRECTOR(A)", pageW / 2, signY + 6, { align: "center" });
    doc.setFontSize(9);
    doc.setFont("helvetica", "normal");
    doc.text("(Firma, post firma y sello)", pageW / 2, signY + 11, { align: "center" });

    window.open(URL.createObjectURL(doc.output("blob")), "_blank");

  } catch (error) {
    showToast("Error al generar PDF", "error");
  }
}