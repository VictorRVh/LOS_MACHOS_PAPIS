import jsPDF from "jspdf";
import autoTable from "jspdf-autotable";
import useHttpRequest from "../composables/useHttpRequest";

const { store: createCertificado } = useHttpRequest("/estudiante-documento");

// ===== FUNCIÓN FECHA ACTUAL FORMAL =====
function obtenerFechaActual() {
  const meses = [
    "enero", "febrero", "marzo", "abril", "mayo", "junio",
    "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"
  ];
  const fecha = new Date();
  // Formato: "ILAVE, 22 de diciembre de 2025"
  return `ILAVE, ${fecha.getDate()} de ${meses[fecha.getMonth()]} de ${fecha.getFullYear()}`;
}

// ===== GENERAR CERTIFICADO (FINAL) =====
export async function generateCertificate(data, codigo, certificado) {

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

  const doc = new jsPDF({
    orientation: "portrait",
    unit: "mm",
    format: "a4",
  });

  const pageWidth = 210;
  const marginL = 20;
  const marginR = 20;

  // Variables de datos (Fallback para evitar errores)
  const nombreEstudiante = certificado?.apellidos_nombres || "Victor Raul Valdez Huanacuni";
  const nombreEspecialidad = certificado?.especialidad?.toUpperCase() || "Peluqueria";
  const nombreModuloGeneral = certificado?.unidad_competencia || "";

  // ==========================================
  // 1. ENCABEZADO Y LOGOS (ALINEADOS)
  // ==========================================
  const headerY = 15;
  const logoMin = "/img/LogoMinisterio.png"; 
  const logoCetpro = "/img/cetproLOGOO.png";    

  // -- LOGO MINEDU (Izquierda) --
  // Ajuste Y (+2) para centrarlo visualmente con la insignia
  try {
    doc.addImage(logoMin, "PNG", marginL, headerY + 2, 50, 13);
  } catch (e) { console.warn("Falta logo Minedu"); }

  // -- INSIGNIA (Centro Exacto) --
  const insigniaW = 18;
  const insigniaH = 18;
  try {
    const insigniaX = (pageWidth / 2) - (insigniaW / 2);
    doc.addImage(logoCetpro, "PNG", insigniaX, headerY, insigniaW, insigniaH);
  } catch (e) { console.warn("Falta insignia"); }

  // -- FOTO (Derecha) --
  const fotoSizeW = 23;
  const fotoSizeH = 18;
  const fotoX = pageWidth - marginR - fotoSizeW;

  doc.setLineWidth(0.2);
  doc.rect(fotoX, headerY, fotoSizeW, fotoSizeH);
  doc.setFontSize(8);
  doc.setFont("times", "normal");
  doc.text("FOTO", fotoX + (fotoSizeW / 2), headerY + (fotoSizeH / 2), { align: "center" });

  // ==========================================
  // 2. TÍTULOS
  // ==========================================
  let cursorY = headerY + 30; // Espacio seguro tras logos

  doc.setFont("times", "bold");
  doc.setFontSize(14);
  doc.text("CENTRO DE EDUCACIÓN TÉCNICO PRODUCTIVA", pageWidth / 2, cursorY, { align: "center" });

  cursorY += 6;
  doc.setFontSize(16);
  doc.text('"CETPRO ILAVE"', pageWidth / 2, cursorY, { align: "center" });

  cursorY += 25;
  doc.setFontSize(20);
  doc.text("CERTIFICADO DE ESTUDIOS", pageWidth / 2, cursorY, { align: "center" });

  // ==========================================
  // 3. CUERPO DEL TEXTO
  // ==========================================
  cursorY += 20;
  doc.setFontSize(12);
  doc.setFont("times", "normal");

  const labelX = marginL;
  const valueX = marginL + 35;

  // Línea 1
  doc.text("El   CETPRO", labelX, cursorY);
  doc.setFont("times", "bold");
  doc.text("ILAVE", valueX, cursorY);

  // Línea 2
  cursorY += 12;
  doc.setFont("times", "normal");
  doc.text("certifica que", labelX, cursorY);
  doc.setFont("times", "bold");
  doc.text(nombreEstudiante, valueX, cursorY);

  // Línea 3
  cursorY += 12;
  doc.setFont("times", "normal");
  doc.text("ha cursado las unidades didácticas, que se indican en el programa de estudios:", labelX, cursorY);

  // Línea 4 (Especialidad)
  cursorY += 10;
  doc.setFont("times", "bold");
  doc.text(nombreEspecialidad, pageWidth / 2, cursorY, { align: "center" });

  // Subrayado de especialidad
  doc.setLineWidth(0.4);
  doc.line(marginL, cursorY + 2, pageWidth - marginR, cursorY + 2);

  // Línea 5
  cursorY += 10;
  doc.setFont("times", "normal");
  doc.text("Los resultados finales de las evaluaciones fueron las siguientes:", marginL, cursorY);

  // ==========================================
  // 4. TABLA (SIN OBSERVACIONES)
  // ==========================================
  cursorY += 6;

  // Datos para la tabla: [Módulo, Unidad, Créditos, Nota, Año, Periodo]
  const tableRows = (certificado?.unidades_didacticas || []).map((u) => [
    nombreModuloGeneral,
    u.nombre_unidad || "-",
    u.credito || "0",
    u.nota || "-",
    new Date().getFullYear(),
    "2025-I"
  ]);

  autoTable(doc, {
    startY: cursorY,
    margin: { left: marginL, right: marginR },

    // Encabezados
    head: [
      [
        "Módulo",
        "Unidad \ndidáctica",
        "Número \nde \ncréditos",
        "Calificación",
        "Año",
        "Periodo \nacadémico"
      ]
    ],
    body: tableRows,

    theme: "grid",
    styles: {
      font: "times",
      fontSize: 9,
      textColor: 0,
      lineColor: 0,
      lineWidth: 0.1,
      valign: "middle",
      halign: "center",
      cellPadding: 3,
    },

    headStyles: {
      fillColor: 255, // Blanco
      textColor: 0,   // Negro
      fontStyle: "bold",
      halign: "center",
      valign: "middle",
      lineWidth: 0.1,
      lineColor: 0,
    },

    // Anchos optimizados (Total ~170mm)
    columnStyles: {
      0: { cellWidth: 35 },                 // Módulo
      1: { cellWidth: 70, halign: "left" }, // Unidad (Ancho aumentado)
      2: { cellWidth: 20 },                 // Créditos
      3: { cellWidth: 20 },                 // Calificación
      4: { cellWidth: 15 },                 // Año
      5: { cellWidth: 20 },                 // Periodo
    },
  });

  // ==========================================
  // 5. PIE DE PÁGINA
  // ==========================================

  let finalY = doc.lastAutoTable.finalY + 20;

  if (finalY > 260) {
    doc.addPage();
    finalY = 40;
  }

  // Fecha
  doc.setFont("times", "normal");
  doc.setFontSize(11);
  doc.text(`Lugar y fecha:   ${obtenerFechaActual()}`, pageWidth / 2, finalY, { align: "center" });

  // Firma
  const firmaY = finalY + 40;
  doc.setLineWidth(0.5);
  doc.line((pageWidth / 2) - 40, firmaY, (pageWidth / 2) + 40, firmaY);

  doc.setFont("times", "bold");
  doc.text("DIRECTOR(A)", pageWidth / 2, firmaY + 5, { align: "center" });

  doc.setFont("times", "normal");
  doc.setFontSize(10);
  doc.text("(Firma, post firma y sello)", pageWidth / 2, firmaY + 10, { align: "center" });

  // Output
  const pdfBlob = doc.output("blob");
  const pdfUrl = URL.createObjectURL(pdfBlob);
  window.open(pdfUrl, "_blank");
}