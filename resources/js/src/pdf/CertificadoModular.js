import jsPDF from "jspdf";
import autoTable from "jspdf-autotable";
import useHttpRequest from "../composables/useHttpRequest";

const { store: createCertificado } = useHttpRequest("/estudiante-documento");

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
  return `PUNO, ${hoy.getDate()} de ${meses[hoy.getMonth()]} de ${hoy.getFullYear()}`;
}

export async function generateCertificadoModular(data, codigo) {

  const payload = {
    id_matricula: data.id_matricula,
    tipo_documento: 3, // CONSTANCIA DE ESTUDIOS
    fecha_emision: new Date().toISOString().slice(0, 10),
    codigo: codigo
  };

  // 2️⃣ Guardar en BD
  const response = await createCertificado(payload);
  if (!response?.success) {
    showToast(
      `"${data?.estudiante?.toUpperCase()}" No se pudo generar la constancia`,
      "warning"
    );
  }

  const codigoFinal = response.codigo;

  const doc = new jsPDF({
    orientation: "landscape",
    unit: "mm",
    format: "a4",
  });

  const W = 297;
  const mL = 20;
  const mR = 20;

  // Rutas de imágenes
  const logoMin = "/img/LogoMinisterio.png";
  const logoCetpro = "/img/CetproLOGOO.png";

  const estudiante = (data?.apellidos_nombres || "").toUpperCase();
  const modulo = (data?.modulo || "").toUpperCase();
  const unidad_competencia = (data?.unidad_competencia || "");
  const especialidad = (data?.especialidad || "").toUpperCase();
  const totalCreditos = data?.creditos_totales || "0";
  const totalHoras = data?.horas_totales || "0";

  const fechaIni = formatearFecha(data?.fecha_inicio);
  const fechaFin = formatearFecha(data?.fecha_fin);

  // ==========================================
  // PRIMERA PÁGINA 
  // ==========================================

  const row1_Y = 12;

  // 1. CÓDIGO REGISTRO
  doc.setFont("times", "bold");
  doc.setTextColor(100);
  doc.setFontSize(8);
  doc.text("Código de Registro Institucional", mL, row1_Y + 4);
  doc.setTextColor(0);
  doc.setFontSize(10);
  doc.text(`N.° ${codigoFinal}`, mL, row1_Y + 9);

  // 2. LOGO MINISTERIO
  try {
    doc.addImage(logoMin, "PNG", (W / 2) - 25, row1_Y, 50, 12);
  } catch (e) { }

  // 3. LOGO CETPRO (RESTAURADO SIN RECORTE PARA QUE APAREZCA SÍ O SÍ)
  const row2_Y = 35;
  const logoW = 23;
  const logoH = 26;
  const logoX = mL + 5;

  try {
    // Método simple para asegurar visibilidad
    doc.addImage(logoCetpro, "PNG", logoX, row2_Y, logoW, logoH);
  } catch (e) {
    // Fallback: si no carga la imagen, dibuja un rectangulo vacio para saber donde va
    doc.rect(logoX, row2_Y, logoW, logoH);
  }

  // 4. FOTO
  const photoW = 25;
  const photoH = 30;
  doc.setDrawColor(0);
  doc.setLineWidth(0.2);
  doc.rect(W - mR - photoW, row2_Y, photoW, photoH);
  doc.setFontSize(8);
  doc.text("FOTO", W - mR - (photoW / 2), row2_Y + (photoH / 2), { align: "center" });

  // 5. TÍTULOS
  const textY = row2_Y + 8;
  doc.setFont("times", "bold");
  doc.setFontSize(16);
  doc.text("CENTRO DE EDUCACIÓN TÉCNICO-PRODUCTIVA PÚBLICO", W / 2, textY, { align: "center" });
  doc.setFontSize(22);
  doc.text('"CETPRO PUNO"', W / 2, textY + 10, { align: "center" });

  let curY = 75;
  doc.setFontSize(30);
  doc.text("CERTIFICADO MODULAR", W / 2, curY, { align: "center" });

  // 6. DATOS
  curY += 25;
  const printField = (label, value, y) => {
    doc.setFont("times", "normal");
    doc.setFontSize(12);
    doc.setTextColor(100);
    doc.text(label, mL, y);
    doc.setFont("times", "bold");
    doc.setFontSize(14);
    doc.setTextColor(0);
    doc.text(value, (W + mL) / 2, y, { align: "center" });
  };



  /////////////////////////////////////
  ////////////////////
  // Texto principal

  const posY = 30;

  doc.setFontSize(16);
  doc.setFont("times", "italic");
  doc.text("Otorgado a:", 20, posY + 60);

  doc.setFont("times", "bold");

  doc.text(estudiante || "NOMBRE DEL BENEFICIARIO", 60, posY + 60);


  doc.setFont("times", "italic");
  doc.text("Por haber aprobado satisfactoriamente el módulo formativo:", 20, posY + 70);

  doc.setFont("times", "bold");
  doc.text(modulo || "NOMBRE DEL MÓDULO", 148.5, posY + 80, { align: "center" });

  doc.setFont("times", "italic");
  doc.text("Correspondiente al Programa de Estudios:", 20, posY + 90);

  doc.setFont("times", "bold");
  doc.text(especialidad || "NOMBRE DE ESPECIALIDAD", 148.5, posY + 100, { align: "center" });
  ///////////////////////
  ////////////////////////

  curY += 45;
  doc.setFont("times", "italic");
  //doc.setTextColor(50);
  const textoResumen = `desarrollado del ${fechaIni} al ${fechaFin}, con un total de ${totalCreditos} créditos, equivalente a ${totalHoras} horas.`;
  doc.text(textoResumen, mL, curY);

  const pieY = 175;
  doc.setFont("times", "normal");
  doc.setFontSize(11);
  doc.setTextColor(0);
  doc.text(`Lugar y fecha:   ${obtenerFechaEmision()}`, W - mR, pieY + 20, { align: "right" });


  // ==========================================
  // SEGUNDA PÁGINA 
  // ==========================================
  doc.addPage();

  try {
    doc.addImage(logoMin, "PNG", mL, 10, 40, 10);
  } catch (e) { }

  doc.setFontSize(10);
  doc.setFont("times", "bold");
  doc.text("CICLO FORMATIVO:", mL, 30);
  doc.setFont("times", "normal");
  doc.text(`${data.ciclo_academico}`, mL + 40, 30);

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
          content: unidad_competencia,
          rowSpan: unidades.length,
          styles: { valign: 'middle', halign: 'center', fontStyle: 'bold' }
        });
      }

      const capacidadesTexto = Array.isArray(u.capacidades)
        ? u.capacidades.join(", ")
        : (u.capacidades || "-");

      row.push(u.nombre_unidad || "-");
      row.push(u.creditos || "1");
      row.push(u.horas || "16");
      row.push(capacidadesTexto);
      row.push(u.nota || "-");

      bodyRows.push(row);
    });

  } else {
    bodyRows.push([modulo, "-", "-", "-", "-", "-"]);
  }

  bodyRows.push([
    {
      content: "Experiencias formativas en situaciones reales de trabajo",
      colSpan: 5,
      styles: { halign: 'left', fontStyle: 'bold' }
    },
    data?.experiencia_formativa || "-"
  ]);

  autoTable(doc, {
    startY: 38,
    margin: { left: mL, right: mR },
    head: [[
      "Unidad de Competencia",
      "Unidad didáctica",
      "N° de\nCréditos",
      "Horas",
      "Capacidades",
      "Calificación"
    ]],
    body: bodyRows,
    theme: 'plain',
    styles: {
      font: "times",
      fontSize: 8,
      lineColor: 150,
      lineWidth: 0.1,
      cellPadding: 3,
      valign: 'middle',
      halign: 'center',
      textColor: 0
    },
    headStyles: {
      fillColor: 220,
      textColor: 0,
      fontStyle: 'bold',
      lineWidth: 0.1,
      lineColor: 0,
      halign: 'center'
    },
    columnStyles: {
      0: { cellWidth: 40, lineWidth: 0.1, lineColor: 150 },
      1: { cellWidth: 45, halign: 'left', lineWidth: 0.1, lineColor: 150 },
      2: { cellWidth: 15, lineWidth: 0.1, lineColor: 150 },
      3: { cellWidth: 15, lineWidth: 0.1, lineColor: 150 },
      4: { cellWidth: 'auto', halign: 'left', lineWidth: 0.1, lineColor: 150 },
      5: { cellWidth: 20, lineWidth: 0.1, lineColor: 150 }
    }
  });

  const finalY = doc.lastAutoTable.finalY;

  // ===============================================
  // CUADRO DE INSTITUCIÓN
  // ===============================================
  const boxWidth = W - mL - mR;
  const headerHeight = 7;
  const contentHeight = 10;

  doc.setDrawColor(0);
  doc.setLineWidth(0.2);

  // 1. Cabecera del cuadro (Título)
  doc.rect(mL, finalY, boxWidth, headerHeight);
  doc.setFont("times", "bold");
  doc.setFontSize(9);
  doc.text("Institución(es) en que realizó la experiencia:", mL + 2, finalY + 5);

  // 2. Cuerpo del cuadro (Nombre del CETPRO)
  const contentY = finalY + headerHeight;
  doc.rect(mL, contentY, boxWidth, contentHeight);
  doc.setFont("times", "normal");
  doc.text("• CENTRO DE EDUCACION TECNICO PRODUCTIVA PUNO", mL + 5, contentY + 6);

  // 3. COD (FUERA DEL CUADRO, ABAJO A LA IZQUIERDA)
  const footerY = contentY + contentHeight + 5; // 5mm de separación
  doc.text(`N.° ${codigoFinal}`, mL, footerY);

  const pdfBlob = doc.output("blob");
  const pdfUrl = URL.createObjectURL(pdfBlob);
  window.open(pdfUrl, "_blank");
}