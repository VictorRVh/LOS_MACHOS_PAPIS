import jsPDF from "jspdf";
import autoTable from "jspdf-autotable";

function formatearFecha(fechaStr) {
  if (!fechaStr) return "..........";
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
  const H = 210;
  const mL = 25;
  const mR = 25;

  const logoMin = "/img/LogoMinisterio.png";
  const logoCetpro = "/img/insignia.png";

  const estudiante = (data?.apellidos_nombres || "").toUpperCase();
  const modulo = (data?.unidad_competencia || "").toUpperCase(); 
  const especialidad = (data?.especialidad || "").toUpperCase();
  const totalCreditos = data?.creditos || "0";
  const totalHoras = data?.horas || "0";
  
  const fechaIni = formatearFecha(data?.fecha_inicio);
  const fechaFin = formatearFecha(data?.fecha_fin);

  // ----------------------------------------------------------------
  // PRIMERA CARA (INTACTA)
  // ----------------------------------------------------------------
  doc.setLineWidth(0.3);
  doc.rect(15, 10, 50, 12);
  doc.setFont("times", "bold");
  doc.setFontSize(7);
  doc.text("Código de Registro Institucional", 17, 14);
  doc.setFontSize(9);
  doc.text("N.° _______________", 17, 19);

  doc.setFontSize(10);
 
  doc.setFontSize(12);
  doc.text("MODELO ÚNICO NACIONAL DE CERTIFICADO MODULAR", W / 2, 16, { align: "center" });

  try {
    const insW = 20;
    const insH = 20;
    doc.addImage(logoCetpro, "PNG", 40, 26, insW, insH);
    doc.setFontSize(6);
    doc.text("LOGO DEL", 40 + (insW/2), 49, { align: "center" });
    doc.text("CETPRO", 40 + (insW/2), 52, { align: "center" });
  } catch(e){}

  try {
    doc.addImage(logoMin, "PNG", (W / 2) - 25, 28, 50, 12);
  } catch(e){}

  doc.rect(W - 45, 25, 25, 30);
  doc.setFontSize(8);
  doc.text("FOTO", W - 32.5, 42, { align: "center" });

  let curY = 65;
  doc.setFont("times", "bold");
  doc.setFontSize(14);
  doc.text("CENTRO DE EDUCACIÓN TÉCNICO-PRODUCTIVA (público/privado)", W / 2, curY, { align: "center" });
  
  curY += 8;
  doc.setFontSize(16);
  doc.text('"CETPRO PUNO"', W / 2, curY, { align: "center" });

  curY += 18;
  doc.setFontSize(20);
  doc.text("CERTIFICADO MODULAR", W / 2, curY, { align: "center" });

  curY += 25;
  doc.setFontSize(12);
  doc.setFont("times", "normal");

  doc.text("Otorgado a...........................................................................................................................................................................................................................", mL, curY);
  doc.setFont("times", "bold");
  doc.text(estudiante, mL + 30, curY - 1); 

  curY += 14;
  doc.setFont("times", "normal");
  doc.text("Por haber aprobado satisfactoriamente el módulo formativo................................................................................................................................................", mL, curY);
  doc.setFont("times", "bold");
  doc.text(modulo, mL + 110, curY - 1);

  curY += 14;
  doc.setFont("times", "normal");
  doc.text("Correspondiente al programa de estudios ...........................................................................................................................................................................", mL, curY);
  doc.setFont("times", "bold");
  doc.text(especialidad, mL + 80, curY - 1);

  curY += 14;
  doc.setFont("times", "normal");
  doc.text("desarrollado del................................................................. al......................................................, con un total de......................... créditos,", mL, curY);
  
  doc.setFont("times", "bold");
  doc.text(fechaIni, mL + 35, curY - 1);
  doc.text(fechaFin, mL + 115, curY - 1);
  doc.text(totalCreditos.toString(), mL + 195, curY - 1);

  curY += 14;
  doc.setFont("times", "normal");
  doc.text("equivalente a ..................... horas.", mL, curY);
  doc.setFont("times", "bold");
  doc.text(totalHoras.toString(), mL + 35, curY - 1);

  curY += 25;
  doc.setFont("times", "normal");
  doc.text(`Lugar y fecha:   ${obtenerFechaEmision()}`, W - mR, curY, { align: "right" });

  const firmaY = 180;
  doc.setLineWidth(0.5);
  doc.line((W / 2) - 40, firmaY, (W / 2) + 40, firmaY);
  doc.setFont("times", "bold");
  doc.text("DIRECTOR(A)", W / 2, firmaY + 5, { align: "center" });
  doc.setFont("times", "normal");
  doc.setFontSize(10);
  doc.text("(Firma, post firma y sello)", W / 2, firmaY + 10, { align: "center" });

  doc.addPage();

  // ----------------------------------------------------------------
  // SEGUNDA CARA
  // ----------------------------------------------------------------

  try {
    doc.addImage(logoMin, "PNG", mL, 10, 45, 11);
  } catch(e){}

  doc.setFontSize(10);
  doc.setFont("times", "bold");
  doc.text("CICLO FORMATIVO:", mL, 35);
  doc.setFont("times", "normal");
  doc.text("TÉCNICO ", mL + 40, 35);

  doc.setFont("times", "bold");
  doc.text("MODALIDAD:", 150, 35);
  doc.setFont("times", "normal");
  doc.text("PRESENCIAL ", 180, 35);

  const unidades = data?.unidades_didacticas || [];
  const bodyRows = [];
  
  // Calculamos cuantas filas ocupará el modulo: Cantidad de unidades + 1 fila para experiencias
  // Si no hay unidades, ponemos 1 fila de aviso + 1 fila experiencias = 2
  const filasUnidades = unidades.length > 0 ? unidades.length : 1;
  const totalRowSpan = filasUnidades + 1; 

  if (unidades.length > 0) {
    unidades.forEach((u, i) => {
      const row = [];
      
      // COLUMNA 1: Módulo (Solo se define en la primera iteración con rowSpan)
      if (i === 0) {
        row.push({
          content: modulo,
          rowSpan: totalRowSpan, 
          styles: { valign: 'middle', halign: 'center' }
        });
      }

      // COLUMNA 2: Unidad didáctica
      row.push(u.nombre_unidad || "-");

      // COLUMNA 3: Calificación
      row.push(u.nota || "-");

      bodyRows.push(row);
    });
  } else {
    // Si no hay unidades, creamos la estructura base
    bodyRows.push([
      {
        content: modulo,
        rowSpan: totalRowSpan,
        styles: { valign: 'middle', halign: 'center' }
      },
      "SIN UNIDADES REGISTRADAS",
      "-"
    ]);
  }

  // AGREGAMOS LA FILA DE EXPERIENCIAS DENTRO DE LA MISMA ESTRUCTURA
  // Nota: Como la columna 1 (Modulo) tiene rowSpan activo desde arriba, 
  // en esta fila SOLO definimos las columnas siguientes (Unidad y Nota).
  bodyRows.push([
    { 
      content: "Experiencias formativas en situaciones reales de trabajo", 
      styles: { halign: 'right' } // Alineado a la derecha para diferenciarlo de las unidades
    },
    data?.nota_experiencias || "-"
  ]);

  autoTable(doc, {
    startY: 45,
    margin: { left: mL, right: mR },
    head: [
      [
        "Modulo",          // Col 1
        "Unidad didáctica",// Col 2
        "Calificación"     // Col 3
      ]
    ],
    body: bodyRows,
    theme: 'grid',
    styles: {
      font: "times",
      fontSize: 9,
      lineColor: 0,
      lineWidth: 0.1,
      textColor: 0,
      cellPadding: 3,
      valign: 'middle',
      halign: 'center'
    },
    headStyles: {
      fillColor: 255,
      textColor: 0,
      fontStyle: 'bold',
      lineWidth: 0.1,
      lineColor: 0,
      halign: 'center',
      valign: 'middle'
    },
    columnStyles: {
      0: { cellWidth: 50 },                     // Módulo
      1: { cellWidth: 'auto', halign: 'left' }, // Unidad didáctica (alineación izquierda por defecto)
      2: { cellWidth: 25 }                      // Calificación
    }
  });

  const finalY = doc.lastAutoTable.finalY;
  
  // Rectángulo inferior para Institución
  doc.setLineWidth(0.1);
  doc.rect(mL, finalY, W - mL - mR, 9);
  
  doc.setFont("times", "bold");
  doc.setFontSize(8);
  doc.text("Institución(es) en que realizó la experiencia", mL + 2, finalY + 5);
  // doc.text(data?.institucion || "", mL + 70, finalY + 5);

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