import jsPDF from "jspdf";
import useModalToast from "../composables/useModalToast";
import useHttpRequest from "../composables/useHttpRequest";

const { showToast } = useModalToast();

const { show: getDataEstudiante } = useHttpRequest("/egresados");

function toUpper(value, fallback = "") {
  return String(value ?? fallback).trim().toUpperCase();
}

function formatDateParts(date = new Date()) {
  const months = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
  return {
    day: date.getDate(),
    month: months[date.getMonth()],
    year: date.getFullYear(),
  };
}

export async function generateTituloCetpro(id_egresado, codigoInstitucional = null, codigoUgel = null) {
  try {





    const codigo__Institucional = codigoInstitucional ||
      "........................";

    const codigo__Ugel = codigoUgel ||
      "........................";

    // 2. OBTENER DATOS DEL EGRESADO
    const res = await getDataEstudiante(id_egresado);
    if (!res?.success) {
      showToast("No se pudo obtener datos del egresado", "error");
      return;
    }
    const datos = res.data;


    const doc = new jsPDF({
      orientation: "landscape",
      unit: "mm",
      format: "a4",
      compress: true,
    });

    const pageW = 297;
    const margin = 25;
    const today = formatDateParts();

    // Datos procesados
    const nombreCetpro = 
      (datos?.cetpro?.cetpro || "----").toUpperCase();
    const tipoGestion = toUpper(datos?.cetpro?.tipo_gestion, "PÚBLICO");
    const estudiante = (datos?.apellidos_nombres || "").toUpperCase();

    const especialidad =(datos?.especialidad || "").toUpperCase();

    const directorNombre = (datos?.cetpro?.director || "----").toUpperCase();

    const lugar = toUpper(datos?.cetpro?.lugar || "PUNO");
    const rdAut = datos?.cetpro?.rd_autorizacion || "";
    const rdConv = datos?.cetpro?.rd_conversion || "";

    // ==========================================
    // CARA 1: ANVERSO
    // ==========================================

    // 1. LOGOS (Reducidos y centrados)
    try {
      // Logo CETPRO reducido
      doc.addImage("/img/CETPRO_Image.png", "PNG", margin, 15, 18, 20, undefined, "FAST");
    } catch (e) { }

    try {
      // Sello Central reducido
      doc.addImage("/img/Gran_Sello_de_la_República_del_Perú.svg.png", "PNG", (pageW / 2) - 9, 12, 18, 16, undefined, "FAST");
    } catch (e) { }

    // Cuadro FOTO
    doc.setLineWidth(0.2);
    doc.rect(pageW - margin - 22, 15, 22, 28);
    doc.setFont("times", "normal");
    doc.setFontSize(7);
    doc.text("FOTO", pageW - margin - 11, 28, { align: "center" });

    // 2. TÍTULOS
    let y = 46;
    doc.setFont("times", "bold");
    doc.setFontSize(16);
    doc.text("REPÚBLICA DEL PERÚ", pageW / 2, y, { align: "center" });
    y += 7;
    doc.text("MINISTERIO DE EDUCACIÓN", pageW / 2, y, { align: "center" });
    y += 7;
    doc.setFontSize(16);
    doc.text(`CENTRO DE EDUCACIÓN TÉCNICO-PRODUCTIVA (${tipoGestion})`, pageW / 2, y, { align: "center" });
    y += 8;
    doc.text(`"${nombreCetpro}"`, pageW / 2, y, { align: "center" });

    // 3. CUERPO
    y += 18;
    doc.setFont("times", "normal");
    doc.setFontSize(16);
    doc.text(`El Director del Centro de Educación Técnico Productiva (${tipoGestion.toLowerCase()})`, margin, y);
    doc.setFont("times", "bold");
    doc.text(directorNombre, pageW - margin - 15, y, { align: "right" });

    y += 12;
    doc.setFont("times", "normal");
    doc.text(`por cuanto `, margin, y);
    doc.setFont("times", "bold");
    doc.text(estudiante, pageW / 2, y - 0.8, { align: "center" });

    y += 12;
    doc.setFont("times", "normal");
    doc.text("ha cumplido satisfactoriamente con las normas y disposiciones reglamentarias vigentes, le otorga el Título de", margin, y);

    y += 14;
    doc.setFont("times", "bold");
    doc.setFontSize(16);
    doc.text(especialidad, pageW / 2, y, { align: "center" });
    doc.setLineWidth(0.4);
    doc.line(margin + 30, y + 1.5, pageW - margin - 30, y + 1.5);

    y += 20;
    doc.setFont("times", "italic");
    doc.setFontSize(16);
    doc.text("POR TANTO:", margin + 20, y);
    y += 7;
    doc.text("Se expide el presente TÍTULO para que se le reconozca como tal.", margin + 20, y);

    y += 10;
    doc.setFont("times", "normal");
    doc.text(`Dado en ${lugar} a los ${today.day} días del mes de ${today.month} de ${today.year}`, pageW - margin, y, { align: "right" });

    const signY = 185;
    doc.setLineWidth(0.3);
    doc.line(pageW / 2 - 35, signY, pageW / 2 + 35, signY);
    doc.setFont("times", "bold");
    doc.text("DIRECTOR(A)", pageW / 2, signY + 6, { align: "center" });
    doc.setFontSize(12);
    doc.setFont("times", "normal");
    doc.text("(Firma, post firma y sello)", pageW / 2, signY + 11, { align: "center" });

    // ==========================================
    // CARA 2: REVERSO (REGISTRO)
    // ==========================================
    doc.addPage("landscape");

    // 1. CELDAS PEGADAS SUPERIORES (Diseño oficial)
    const headBoxY = 15;
    const headBoxH = 16;
    doc.setDrawColor(180);
    doc.setLineWidth(0.2);

    // Celda Izquierda (Logo MINEDU)
    doc.rect(margin, headBoxY, 55, headBoxH);
    try {
      doc.addImage("/img/LogoMinisterio.png", "PNG", margin + 3, headBoxY + 3, 48, 10, undefined, "FAST");
    } catch (e) { }

    // Celda Derecha (Texto Normativo Inclinado)
    doc.rect(margin + 55, headBoxY, 95, headBoxH);
    doc.setFont("helvetica", "oblique"); // Texto inclinado
    doc.setFontSize(7.5);
    doc.setTextColor(80);
    doc.text('Denominación del documento normativo: "Lineamientos Académicos', margin + 58, headBoxY + 6);
    doc.text('Generales para los Centros de Educación Técnico-Productiva"', margin + 58, headBoxY + 11);

    // 2. RECUADRO CENTRAL
    const boxW = 100;
    const boxH = 140;
    const boxX = (pageW / 2) - (boxW / 2);
    const boxY = 42;

    doc.setTextColor(0);
    doc.setDrawColor(0);
    doc.setLineWidth(0.4);
    doc.rect(boxX, boxY, boxW, boxH);

    // Logo dentro del recuadro (Reducido y centrado)
    try {
      doc.addImage("/img/CETPRO_Image.png", "PNG", boxX + (boxW / 2) - 13, boxY + 8, 24, 26, undefined, "FAST");
    } catch (e) { }

    let ry = boxY + 55;
    doc.setFont("helvetica", "bold");
    doc.setFontSize(9.5);
    doc.text("Código de Registro Institucional", boxX + 12, ry);
    ry += 6;
    doc.text(`N°`, boxX + 12, ry);
    doc.setFont("helvetica", "normal");
    doc.line(boxX + 20, ry + 1, boxX + 88, ry + 1);
    doc.text(String(rdAut), boxX + 54, ry, { align: "center" });

    ry += 20;
    doc.setFont("helvetica", "bold");
    doc.text("Código de Registro de la UGEL", boxX + 12, ry);
    ry += 6;
    doc.text(`N°`, boxX + 12, ry);
    doc.setFont("helvetica", "normal");
    doc.line(boxX + 20, ry + 1, boxX + 88, ry + 1);
    doc.text(String(rdConv), boxX + 54, ry, { align: "center" });

    // Firma Reverso
    ry += 32;
    doc.setFontSize(12);
    doc.setLineWidth(0.3);
    doc.line(boxX + 25, ry, boxX + 75, ry);
    doc.setFont("helvetica", "bold");
    doc.text("DIRECTOR(A)", boxX + 50, ry + 6, { align: "center" });
    doc.setFontSize(12);
    doc.setFont("times", "normal");
    doc.text("(Firma, post firma y sello)", pageW / 2, ry + 11, { align: "center" });

    window.open(URL.createObjectURL(doc.output("blob")), "_blank");

  } catch (error) {
    showToast("Error al generar el Título", "error");
  }
}