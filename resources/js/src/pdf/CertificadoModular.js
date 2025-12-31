import jsPDF from "jspdf";
import autoTable from "jspdf-autotable";

function formatearFecha(fechaStr) {
  if (!fechaStr) return "";
  const parte = fechaStr.split("-");
  if (parte.length !== 3) return fechaStr;
  
  const anio = parte[0];
  const mes = parseInt(parte[1]) - 1;
  const dia = parte[2];
  
  const meses = [
    "enero", "febrero", "marzo", "abril", "mayo", "junio", 
    "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"
  ];
  
  return `${dia} de ${meses[mes]} de ${anio}`;
}

function obtenerFechaEmision() {
  const hoy = new Date();
  const meses = [
    "enero", "febrero", "marzo", "abril", "mayo", "junio", 
    "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"
  ];
  return `Puno, ${hoy.getDate()} de ${meses[hoy.getMonth()]} de ${hoy.getFullYear()}`;
}

export function generateCertificadoModular(data) {
  const doc = new jsPDF({
    orientation: "landscape",
    unit: "mm",
    format: "a4",
  });

  const W = 297;
  const mL = 20;
  const mR = 20;

  const logoMin = "/img/LogoMinisterio.png";
  const logoCetpro = "/img/CETPRO_Image.png";

  const estudiante = (data?.apellidos_nombres || "").toUpperCase();
  const modulo = (data?.unidad_competencia || "").toUpperCase(); 
  const especialidad = (data?.especialidad || "").toUpperCase();
  const totalCreditos = data?.creditos || "0";
  const totalHoras = data?.horas || "0";
  
  const fechaIni = formatearFecha(data?.fecha_inicio);
  const fechaFin = formatearFecha(data?.fecha_fin);

  // ==========================================
  // PRIMERA PÁGINA (INTACTA)
  // ==========================================
  
  const drawFieldLine = (label, value, y) => {
    doc.setFont("times", "normal");
    doc.setFontSize(12);
    doc.setTextColor(0, 0, 0);
    doc.text(label, mL, y);
    const labelWidth = doc.getTextWidth(label);
    const lineStartX = mL + labelWidth + 2;
    const lineEndX = W - mR;
    doc.setDrawColor(100); 
    doc.setLineWidth(0.1); 
    doc.line(lineStartX, y + 1, lineEndX, y + 1);
    if (value) {
      doc.setFont("times", "bold");
      doc.setTextColor(0, 0, 0);
      const centerLine = lineStartX + ((lineEndX - lineStartX) / 2);
      doc.text(value, centerLine, y, { align: "center" });
    }
  };

  const drawInlineField = (label, value, x, y, minLineWidth = 30) => {
    doc.setFont("times", "normal");
    doc.setFontSize(12);
    doc.text(label, x, y);
    const labelW = doc.getTextWidth(label);
    const lineStartX = x + labelW + 2;
    const valueW = doc.getTextWidth(value);
    const lineLength = Math.max(valueW + 15, minLineWidth); 
    const lineEndX = lineStartX + lineLength;
    doc.setDrawColor(100);
    doc.setLineWidth(0.1);
    doc.line(lineStartX, y + 1, lineEndX, y + 1);
    doc.setFont("times", "bold");
    const centerLine = lineStartX + (lineLength / 2);
    doc.text(value, centerLine, y, { align: "center" });
    return lineEndX + 2; 
  };
  
  const row1_Y = 12;
  doc.setDrawColor(0);
  doc.setLineWidth(0.3);
  doc.rect(15, row1_Y, 50, 12);
  doc.setFont("times", "bold");
  doc.setFontSize(7);
  doc.text("Código de Registro Institucional", 17, row1_Y + 4);
  doc.setFontSize(9);
  doc.text("N.° _______________", 17, row1_Y + 9);

  try {
    doc.addImage(logoMin, "PNG", (W / 2) - 25, row1_Y, 50, 12);
  } catch(e){}

  const row2_Y = 35;
  const logoSize = 25;
  const photoW = 25;
  const photoH = 30;

  try {
    doc.addImage(logoCetpro, "PNG", mL + 5, row2_Y, logoSize, logoSize);
    doc.setFontSize(6);
    doc.setFont("times", "bold");
    doc.text("LOGO DEL", mL + 5 + (logoSize/2), row2_Y + logoSize + 3, { align: "center" });
    doc.text("CETPRO", mL + 5 + (logoSize/2), row2_Y + logoSize + 6, { align: "center" });
  } catch(e){}

  doc.setLineWidth(0.2);
  doc.rect(W - mR - photoW, row2_Y, photoW, photoH);
  doc.setFontSize(8);
  doc.text("FOTO", W - mR - (photoW/2), row2_Y + (photoH/2), { align: "center" });

  const textY = row2_Y + 8; 
  doc.setFont("times", "bold");
  doc.setFontSize(16); 
  doc.text("CENTRO DE EDUCACIÓN TÉCNICO-PRODUCTIVA PÚBLICO", W / 2, textY, { align: "center" });
  doc.setFontSize(22); 
  doc.text('"CETPRO PUNO"', W / 2, textY + 10, { align: "center" });

  let curY = 75; 
  doc.setFontSize(28); 
  doc.text("CERTIFICADO MODULAR", W / 2, curY, { align: "center" });

  curY += 25;
  drawFieldLine("Otorgado a:", estudiante, curY);
  curY += 14;
  drawFieldLine("Por haber aprobado satisfactoriamente el módulo formativo:", modulo, curY);
  curY += 14;
  drawFieldLine("Correspondiente al programa de estudios:", especialidad, curY);
  curY += 14;
  let currentX = mL;
  currentX = drawInlineField("desarrollado del", fechaIni, currentX, curY, 45);
  currentX = drawInlineField(" al", fechaFin, currentX, curY, 45);
  currentX = drawInlineField(", con un total de", totalCreditos.toString(), currentX, curY, 15);
  doc.setFont("times", "normal");
  doc.text(" créditos,", currentX, curY);
  curY += 14;
  currentX = mL;
  currentX = drawInlineField("equivalente a", totalHoras.toString(), currentX, curY, 20);
  doc.setFont("times", "normal");
  doc.text(" horas.", currentX, curY);

  const pieY = 175;
  doc.setFont("times", "normal");
  doc.setFontSize(11);
  doc.text(`Lugar y fecha:   ${obtenerFechaEmision()}`, W - mR, pieY + 20, { align: "right" });

  doc.setDrawColor(0);
  doc.setLineWidth(0.5); 
  doc.line((W / 2) - 40, pieY, (W / 2) + 40, pieY);
  doc.setFont("times", "bold");
  doc.setFontSize(11);
  doc.text("DIRECTOR(A)", W / 2, pieY + 5, { align: "center" });
  doc.setFont("times", "normal");
  doc.setFontSize(10);
  doc.text("(Firma, post firma y sello)", W / 2, pieY + 10, { align: "center" });

  doc.addPage();

  // ==========================================
  // SEGUNDA PÁGINA
  // ==========================================

  try {
    doc.addImage(logoMin, "PNG", mL, 10, 40, 10);
  } catch(e){}

  doc.setFontSize(10);
  doc.setFont("times", "bold");
  doc.text("CICLO FORMATIVO:", mL, 30);
  doc.setFont("times", "normal");
  doc.text("TÉCNICO ", mL + 40, 30);

  doc.setFont("times", "bold");
  doc.text("MODALIDAD:", 150, 30);
  doc.setFont("times", "normal");
  doc.text("PRESENCIAL ", 180, 30);

  const unidades = data?.unidades_didacticas || [];
  const bodyRows = [];
  
  if (unidades.length > 0) {
    unidades.forEach((u, i) => {
      const row = [];
      if (i === 0) {
        row.push({
          content: modulo,
          rowSpan: unidades.length, 
          styles: { valign: 'middle', halign: 'center' }
        });
      }
      row.push(u.nombre_unidad || "-");
      row.push(u.nota || "-");
      bodyRows.push(row);
    });
  } else {
    bodyRows.push([
      { content: modulo, styles: { valign: 'middle', halign: 'center' } },
      "SIN UNIDADES", "-"
    ]);
  }

  bodyRows.push([
    { 
      content: "Experiencias formativas en situaciones reales de trabajo", 
      colSpan: 2, 
      styles: { halign: 'left', fontStyle: 'bold' } 
    },
    data?.nota_experiencias || "-"
  ]);

  autoTable(doc, {
    startY: 38,
    margin: { left: mL, right: mR },
    head: [["Módulo", "Unidad didáctica", "Calificación"]],
    body: bodyRows,
    theme: 'plain',
    styles: {
      font: "times",
      fontSize: 9,
      lineColor: 200, 
      lineWidth: 0.1,
      cellPadding: 4,
      valign: 'middle',
      halign: 'center'
    },
    headStyles: {
      fillColor: 245,
      textColor: 0,
      fontStyle: 'bold',
      lineWidth: 0.1,
      lineColor: 150,
    },
    columnStyles: {
      0: { cellWidth: 50, lineWidth: 0.1, lineColor: 150 },
      1: { cellWidth: 'auto', halign: 'left', lineWidth: 0.1, lineColor: 150 },
      2: { cellWidth: 25, lineWidth: 0.1, lineColor: 150 }
    }
  });

  const finalY = doc.lastAutoTable.finalY;
  
  // ===============================================
  // CUADRO DE INSTITUCIÓN PEGADO Y DIVIDIDO
  // ===============================================
  // No sumamos gap, usamos finalY directamente para que esté PEGADO
  const boxWidth = W - mL - mR;
  const boxHeight = 16; 
  const headerHeight = 7; // Altura de la fila del título
  
  doc.setDrawColor(0);
  doc.setLineWidth(0.1);
  
  // 1. Dibujar el recuadro exterior
  doc.rect(mL, finalY, boxWidth, boxHeight);
  
  // 2. Título
  doc.setFont("times", "bold");
  doc.setFontSize(9);
  doc.text("Institución(es) en que realizó la experiencia:", mL + 2, finalY + 5);
  
  // 3. LÍNEA DIVISORIA EN MEDIO (Separa título de contenido)
  const lineY = finalY + headerHeight;
  doc.line(mL, lineY, mL + boxWidth, lineY);
  
  // 4. Contenido (Punto de lista)
  doc.setFont("times", "normal");
  doc.text("• CENTRO DE EDUCACION TECNICO PRODUCTIVA PUNO", mL + 5, lineY + 6);

  // Firma
  const firmaY2 = 170;
  doc.setLineWidth(0.5);
  doc.line((W / 2) - 40, firmaY2, (W / 2) + 40, firmaY2);
  doc.setFont("times", "bold");
  doc.setFontSize(11);
  doc.text("DIRECTOR(A)", W / 2, firmaY2 + 5, { align: "center" });
  doc.setFont("times", "normal");
  doc.setFontSize(10);
  doc.text("(Firma, post firma y sello)", W / 2, firmaY2 + 10, { align: "center" });

  const pdfBlob = doc.output("blob");
  const pdfUrl = URL.createObjectURL(pdfBlob);
  window.open(pdfUrl, "_blank");
}