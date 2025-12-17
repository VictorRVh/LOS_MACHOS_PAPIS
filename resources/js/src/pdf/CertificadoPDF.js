import jsPDF from "jspdf";
import autoTable from "jspdf-autotable";

function agregarFondo(doc, imagen) {
  if (!imagen) return;

  doc.addImage(
    imagen,
    "PNG",   // o "JPG"
    0,
    0,
    297,     // ancho A4 horizontal
    210      // alto A4 horizontal
  );
}



// ===== FUNCIÓN PARA FORMATEAR FECHAS =====
function formatDateRangeFromSlash(startDate, endDate) {
  const months = [
    "enero", "febrero", "marzo", "abril", "mayo", "junio",
    "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"
  ];

  const parseDate = (date) => {
    if (!date) return {};
    const [year, month, day] = date.split("-").map(Number);
    return { day, month: months[month - 1], year };
  };

  const start = parseDate(startDate);
  const end = parseDate(endDate);

  if (start.year === end.year) {
    if (start.month === end.month) {
      return ` ${start.day} al ${end.day} de ${start.month} del ${start.year} `;
    }
    return  `${start.day} de ${start.month} al ${end.day} de ${end.month} del ${start.year} `;
  }

  return ` ${start.day} de ${start.month} del ${start.year} al ${end.day} de ${end.month} del ${end.year} `;
}

// ===== FUNCIÓN FECHA ACTUAL =====
function obtenerFechaActual() {
  const meses = [
    "enero", "febrero", "marzo", "abril", "mayo", "junio",
    "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"
  ];

  const ciudad = "Puno";
  const fecha = new Date();

  return ` ${ciudad}, ${fecha.getDate()} de ${meses[fecha.getMonth()]} de ${fecha.getFullYear()} `;
}

// ===== GENERAR CERTIFICADO =====
export function generateCertificate(data, certificado) {
  const doc = new jsPDF("landscape", "mm", "a4");

 agregarFondo(doc, "/img/iconosArchivo/fondo-certificado.svg");


  const posY = 20;

  // ===== ENCABEZADO =====
  doc.setFont("times", "bold");
  doc.setFontSize(16);
  doc.text(
    "CENTRO DE EDUCACIÓN TÉCNICO PRODUCTIVA PÚBLICO",
    148.5,
    posY + 20,
    { align: "center" }
  );

  doc.text('"PUNO"', 148.5, posY + 30, { align: "center" });

  doc.setFontSize(23);
  doc.text("CERTIFICADO MODULAR", 148.5, posY + 45, { align: "center" });

  // ===== LOGOS =====
  if (data.photoMinisterio) {
    doc.addImage(data.photoMinisterio, "PNG", 120, 20, 40, 10);
  }

  if (data.logo) {
    doc.addImage(data.logo, "PNG", 20, 25, 30, 30);
  }

  if (data.photo) {
    doc.addImage(data.photo, "PNG", 255, 25, 20, 25);
  }

  // ===== TEXTO PRINCIPAL =====
  doc.setFontSize(16);
  doc.setFont("times", "italic");
  doc.text("Otorgado a:", 20, posY + 70);

  doc.setFont("times", "bold");
  doc.text(
    certificado[0]?.apellidos_nombres || "NOMBRE DEL BENEFICIARIO",
    60,
    posY + 70
  );

  doc.setFont("times", "italic");
  doc.text(
    "Por haber aprobado satisfactoriamente el módulo formativo:",
    20,
    posY + 80
  );

  doc.setFont("times", "bold");
  doc.text(
    certificado[0]?.unidad_competencia?.toUpperCase() || "NOMBRE DEL MÓDULO",
    148.5,
    posY + 90,
    { align: "center" }
  );

  doc.setFont("times", "italic");
  doc.text(
    "Correspondiente al Programa de Estudios:",
    20,
    posY + 100
  );

  doc.setFont("times", "bold");
  doc.text(
    certificado[0]?.especialidad?.toUpperCase() || "NOMBRE DEL PROGRAMA",
    148.5,
    posY + 110,
    { align: "center" }
  );

  // ===== CÁLCULOS =====
  let sumCreditos = 0;
  let sumHoras = 0;

  certificado[0]?.unidades_didacticas?.forEach(u => {
    sumCreditos += u.credito || 0;
    sumHoras += u.hora || 0;
  });

  if (certificado[0]?.experiencias_formativas?.[0]) {
    sumCreditos += certificado[0].experiencias_formativas[0].creditos_exp || 0;
    sumHoras += certificado[0].experiencias_formativas[0].horas_exp || 0;
  }

  const rangoFechas = formatDateRangeFromSlash(
    certificado[0]?.fecha_inicio,
    certificado[0]?.fecha_fin
  );

  doc.setFont("times", "italic");
  doc.text(`
    Desarrollado del ${rangoFechas}, con un total de ${certificado[0]?.creditos} créditos, equivalente a ${certificado[0]?.horas} horas.`,
    20,
    posY + 125
  );


  // ===== FECHA Y FIRMA =====
  doc.setFontSize(13);
  doc.text(obtenerFechaActual(), 240, posY + 170, { align: "center" });

  doc.text(
    "(Firma, post firma y sello)",
    148.5,
    posY + 185,
    { align: "center" }
  );




  doc.addPage();

  doc.setFont("times", "bold");
  doc.setFontSize(18);
  doc.text("RELACIÓN DE UNIDADES DIDÁCTICAS", 148.5, 25, { align: "center" });

  const headers = [["N°", "Unidades Didácticas", "Calificación"]];

  const rows = certificado[0].unidades_didacticas.map((u) => [
    u.numero_unidad,
    u.nombre_unidad,
    u.nota || "-"
  ]);

  autoTable(doc, {
    startY: 35,
    margin: { left: 25 },
    head: headers,
    body: rows,

    theme: "grid", // 👈 importante

    styles: {
      fontSize: 13,
      halign: "center",
      valign: "middle",
      cellPadding: 6,
      lineWidth: 0.3,
      lineColor: [0, 0, 0],
      fillColor: [255, 255, 255], // 👈 filas blancas
      textColor: 0,
    },

    headStyles: {
      fillColor: [255, 255, 255], // 👈 encabezado blanco
      textColor: 0,
      fontStyle: "bold",
    },

    columnStyles: {
      0: { cellWidth: 25 },
      1: { cellWidth: 180 },
      2: { cellWidth: 40 },
    },
  });



  // ===== ABRIR PARA IMPRIMIR =====
  const pdfBlob = doc.output("blob");
  const pdfUrl = URL.createObjectURL(pdfBlob);
  window.open(pdfUrl, "_blank");
}