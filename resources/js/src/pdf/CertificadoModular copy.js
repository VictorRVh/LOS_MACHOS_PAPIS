import jsPDF from "jspdf";
import autoTable from "jspdf-autotable";

// ===== UTILIDADES DE FECHA =====
function obtenerFechaActual() {
  const meses = [
    "enero", "febrero", "marzo", "abril", "mayo", "junio",
    "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"
  ];
  const fecha = new Date();
  return `Puno, ${fecha.getDate()} de ${meses[fecha.getMonth()]} de ${fecha.getFullYear()}`;
}

function formatearFecha(fechaStr) {
  if (!fechaStr) return "..........";
  const [anio, mes, dia] = fechaStr.split("-");
  const meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
  return `${dia} de ${meses[parseInt(mes) - 1]} de ${anio}`;
}

// ===== FUNCIÓN PRINCIPAL =====
export function generateCertificadoModular(data) {
  const doc = new jsPDF({
    orientation: "landscape",
    unit: "mm",
    format: "a4",
  });

  const pageWidth = 297;
  const pageHeight = 210;
  const marginL = 20;
  const marginR = 20;

  // Rutas de imagen
  const logoMin = "/img/LogoMinisterio.png";
  const logoCetpro = "/img/insignia.png";

  // Datos seguros
  const estudiante = data?.estudiante?.toUpperCase() || ".......................................................................................................";
  const modulo = data?.modulo?.toUpperCase() || ".......................................................................................................";
  const especialidad = data?.especialidad?.toUpperCase() || ".......................................................................................................";
  const ciclo = data?.ciclo?.toUpperCase() || "TÉCNICO"; // O Auxiliar Técnico
  const creditos = data?.total_creditos || "....";
  const horas = data?.total_horas || "....";
  const fechaInicio = formatearFecha(data?.fecha_inicio);
  const fechaFin = formatearFecha(data?.fecha_fin);

  // ==========================================
  // PÁGINA 1: CARA FRONTAL (ANEXO 4)
  // ==========================================

  // 1. Recuadro Superior Izquierdo (Registro)
  doc.setLineWidth(0.4);
  doc.rect(15, 10, 50, 12);
  doc.setFont("times", "bold");
  doc.setFontSize(7);
  doc.text("Código de Registro Institucional", 17, 14);
  doc.setFontSize(9);
  doc.text("N.° _______________", 17, 19);

  // 2. Título Superior
  doc.setFontSize(10);
  doc.text("ANEXO N° 4:", pageWidth / 2, 10, { align: "center" });
  doc.setFontSize(12);
  doc.text("MODELO ÚNICO NACIONAL DE CERTIFICADO MODULAR", pageWidth / 2, 15, { align: "center" });

  // 3. Logos y Foto
  const logosY = 25;
  
  // Logo CETPRO (Izquierda - Placeholder en imagen, pero usamos la insignia real)
  try {
    doc.addImage(logoCetpro, "PNG", 30, logosY, 18, 22); 
    doc.setFontSize(7);
    doc.text("LOGO DEL", 39, logosY + 25, { align: "center" });
    doc.text("CETPRO", 39, logosY + 28, { align: "center" });
  } catch (e) {}

  // Logo MINEDU (Centro)
  try {
    doc.addImage(logoMin, "PNG", (pageWidth / 2) - 25, logosY, 50, 12);
  } catch (e) {}

  // Foto (Derecha)
  const fotoX = pageWidth - 40;
  doc.rect(fotoX, logosY, 25, 30);
  doc.text("FOTO", fotoX + 12.5, logosY + 15, { align: "center" });

  // 4. Institución y Título Central
  let cursorY = 60;
  doc.setFontSize(14);
  doc.setFont("times", "bold");
  doc.text("CENTRO DE EDUCACIÓN TÉCNICO-PRODUCTIVA (público/privado)", pageWidth / 2, cursorY, { align: "center" });
  
  cursorY += 8;
  doc.setFontSize(16);
  doc.text('"CETPRO PUNO"', pageWidth / 2, cursorY, { align: "center" });

  cursorY += 15;
  doc.setFontSize(18);
  doc.text("CERTIFICADO MODULAR", pageWidth / 2, cursorY, { align: "center" });

  // 5. Cuerpo del Texto
  cursorY += 20;
  doc.setFontSize(12);
  doc.setFont("times", "normal");
  
  // Otorgado a...
  doc.text("Otorgado a...........................................................................................................................................................................................................................", marginL, cursorY);
  doc.setFont("times", "bold");
  doc.text(estudiante, marginL + 25, cursorY - 1); // Relleno

  // Por haber aprobado...
  cursorY += 12;
  doc.setFont("times", "normal");
  doc.text("Por haber aprobado satisfactoriamente el módulo formativo................................................................................................................................................", marginL, cursorY);
  doc.setFont("times", "bold");
  doc.text(modulo, marginL + 95, cursorY - 1);

  // Correspondiente a...
  cursorY += 12;
  doc.setFont("times", "normal");
  doc.text("Correspondiente al programa de estudios ...........................................................................................................................................................................", marginL, cursorY);
  doc.setFont("times", "bold");
  doc.text(especialidad, marginL + 75, cursorY - 1);

  // Desarrollado del...
  cursorY += 12;
  doc.setFont("times", "normal");
  // Texto base con espacios calculados
  doc.text(`desarrollado del................................................................. al................................................................., con un total de......................... créditos,`, marginL, cursorY);
  
  // Rellenar datos fechas y creditos
  doc.setFont("times", "bold");
  doc.text(fechaInicio, marginL + 35, cursorY - 1);
  doc.text(fechaFin, marginL + 110, cursorY - 1);
  doc.text(creditos.toString(), marginL + 185, cursorY - 1);

  // Equivalente a horas...
  cursorY += 12;
  doc.setFont("times", "normal");
  doc.text(`equivalente a ..................... horas.`, marginL, cursorY);
  doc.setFont("times", "bold");
  doc.text(horas.toString(), marginL + 30, cursorY - 1);

  // 6. Lugar y Fecha
  cursorY += 20;
  doc.setFont("times", "normal");
  doc.text(`Lugar y fecha:   ${obtenerFechaActual()}`, 150, cursorY);

  // 7. Firma
  const firmaY = 185;
  doc.setLineWidth(0.5);
  doc.line((pageWidth / 2) - 40, firmaY, (pageWidth / 2) + 40, firmaY);
  doc.setFont("times", "bold");
  doc.text("DIRECTOR(A)", pageWidth / 2, firmaY + 5, { align: "center" });
  doc.setFont("times", "normal");
  doc.setFontSize(10);
  doc.text("(Firma, post firma y sello)", pageWidth / 2, firmaY + 10, { align: "center" });


  // ==========================================
  // PÁGINA 2: REVERSO (NOTAS)
  // ==========================================
  doc.addPage();

  // 1. Encabezado Reverso
  try {
    // Logo pequeño izquierda
    doc.addImage(logoMin, "PNG", marginL, 10, 40, 10);
  } catch (e) {}

  doc.setFontSize(7);
  doc.setFont("times", "italic");
  doc.text("Denominación del documento normativo: \"Lineamientos Académicos", 150, 12);
  doc.text("Generales para los Centros de Educación Técnico-Productiva\"", 150, 15);
  
  doc.setLineWidth(0.1);
  doc.setLineDash([1, 1]); // Línea punteada fina
  doc.line(marginL, 22, pageWidth - marginR, 22);
  doc.setLineDash([]); // Reset

  // 2. Ciclo y Modalidad
  doc.setFontSize(10);
  doc.setFont("times", "bold");
  doc.text("CICLO FORMATIVO:", marginL, 35);
  doc.setFont("times", "normal");
  doc.text(`${ciclo} ........................................................`, marginL + 40, 35);

  doc.setFont("times", "bold");
  doc.text("MODALIDAD:", 140, 35);
  doc.setFont("times", "normal");
  doc.text("PRESENCIAL .................................................", 170, 35);

  // 3. Tabla de Notas
  
  // Preparar filas
  // Asumimos que data.unidades_didacticas viene poblado
  // Columna 0: Unidad Competencia (A veces se agrupa, aquí pondremos el nombre del módulo en la primera fila)
  
  const filas = (data?.unidades_didacticas || []).map((u, index) => [
    index === 0 ? (data.unidad_competencia || modulo) : "", // Solo mostrar en la primera fila o repetir
    u.nombre_unidad || "-",
    u.credito || "0",
    u.hora || "0",
    "LOGRO", // Capacidades (Hardcodeado o dinámico si tienes el dato)
    u.nota || "-"
  ]);

  // Agregar fila de EFSRT (Experiencias Formativas)
  filas.push([
    "", // Columna Competencia vacía
    "Experiencias formativas en \nsituaciones reales de trabajo", // Texto específico
    data?.efsrt_creditos || "0",
    data?.efsrt_horas || "0",
    "LOGRO",
    data?.efsrt_nota || "-" // Nota de prácticas
  ]);

  // Agregar fila final "Institución"
  // Esta la manejaremos con autoTable foot o dibujando después, 
  // pero el modelo la tiene pegada a la tabla.

  autoTable(doc, {
    startY: 45,
    margin: { left: marginL, right: marginR },
    head: [
      [
        { content: 'Unidad de\ncompetencia', styles: { valign: 'middle' } },
        { content: 'Unidad didáctica', styles: { valign: 'middle' } },
        { content: 'N° de\ncréditos', styles: { valign: 'middle' } },
        { content: 'N°\nHoras', styles: { valign: 'middle' } },
        { content: 'Capacidades', styles: { valign: 'middle' } },
        { content: 'Calificación', styles: { valign: 'middle' } }
      ]
    ],
    body: filas,
    theme: 'grid',
    styles: {
      font: "times",
      fontSize: 9,
      lineColor: 0,
      lineWidth: 0.1,
      textColor: 0,
      cellPadding: 2,
    },
    headStyles: {
      fillColor: 255,
      textColor: 0,
      fontStyle: 'bold',
      halign: 'center',
      lineWidth: 0.1,
      lineColor: 0
    },
    columnStyles: {
      0: { cellWidth: 50 }, // U. Competencia
      1: { cellWidth: 80 }, // U. Didáctica
      2: { cellWidth: 20, halign: 'center' },
      3: { cellWidth: 20, halign: 'center' },
      4: { cellWidth: 60 }, // Capacidades
      5: { cellWidth: 25, halign: 'center' }
    },
    // Fila especial para "Institución" al final de la tabla
    didDrawPage: (data) => {
      // Guardamos la posición Y final para dibujar el recuadro de abajo
    }
  });

  // 4. Fila "Institución en que realizó la experiencia"
  // Lo dibujamos manualmente justo debajo de la tabla
  let finalY = doc.lastAutoTable.finalY;
  
  // Dibujar el recuadro
  doc.rect(marginL, finalY, pageWidth - marginR - marginL, 10);
  doc.setFont("times", "bold");
  doc.setFontSize(9);
  doc.text("Institución(es) en que realizó la experiencia", marginL + 2, finalY + 6);
  // Aquí iría el nombre de la empresa si existe en data, o se deja vacío para llenar

  // 5. Firma en la segunda página
  const firmaY2 = 180;
  doc.setLineWidth(0.5);
  doc.line((pageWidth / 2) - 40, firmaY2, (pageWidth / 2) + 40, firmaY2);
  doc.setFont("times", "bold");
  doc.setFontSize(11);
  doc.text("DIRECTOR(A)", pageWidth / 2, firmaY2 + 5, { align: "center" });
  doc.setFont("times", "normal");
  doc.setFontSize(10);
  doc.text("(Firma, post firma y sello)", pageWidth / 2, firmaY2 + 10, { align: "center" });

  // ===== SALIDA =====
  const pdfBlob = doc.output("blob");
  const pdfUrl = URL.createObjectURL(pdfBlob);
  window.open(pdfUrl, "_blank");
}