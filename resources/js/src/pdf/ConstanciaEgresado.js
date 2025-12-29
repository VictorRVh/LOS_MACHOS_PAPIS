import jsPDF from "jspdf";
import useHttpRequest from "../composables/useHttpRequest";
import useModalToast from "../composables/useModalToast";

const { store: createDocumento } = useHttpRequest("/estudiante-documento");
const { showToast } = useModalToast();

export async function generateConstanciaEgresado(data) {
  // 1. Intentamos guardar en BD (Sin bloquear el PDF si falla)
  try {
    const payload = {
      id_matricula: data.id_matricula,
      tipo_documento: 4, // 4 = Constancia Egresado
      fecha_emision: new Date().toISOString().slice(0, 10),
    };
    // No usamos await bloqueante estricto o capturamos su error individualmente
    createDocumento(payload).then((res) => {
        if(!res.success) console.warn("No se pudo registrar la emisión en BD");
    }).catch(err => console.error("Error api documento:", err));
  } catch (e) {
    console.error("Error preparando guardado en BD", e);
  }

  // 2. Generación del PDF (Independiente del backend)
  try {
    const doc = new jsPDF({
      orientation: "portrait",
      unit: "mm",
      format: "a4",
    });

    const pageWidth = 210;
    const marginL = 25;
    const marginR = 25;
    const contentWidth = pageWidth - marginL - marginR;

    // Rutas de imagenes
    const logoMin = "/img/LogoMinisterio.png";
    const logoCetpro = "/img/insignia.png"; 

    // --- RECUADRO SUPERIOR "Nº" ---
    doc.setLineWidth(0.3);
    doc.rect(marginL, 15, 35, 8); 
    doc.setFont("times", "bold");
    doc.setFontSize(9);
    doc.text("N.° ........................", marginL + 2, 20);

    // --- LOGOS ---
    const logosY = 28;
    const logosH = 12;

    try {
      doc.addImage(logoMin, "PNG", marginL, logosY, 50, logosH);
      const insigniaW = 18; 
      doc.addImage(logoCetpro, "PNG", pageWidth - marginR - insigniaW, logosY - 2, insigniaW, 18);
    } catch (e) {
      console.warn("No se pudieron cargar los logos (verificar rutas en /public/img/)");
    }

    // --- TÍTULOS ---
    doc.setFont("times", "normal");
    doc.setFontSize(8);
    doc.text('"Año ............................................................................................................................."', pageWidth / 2, 50, { align: "center" });

    doc.setFont("times", "bold");
    doc.setFontSize(14);
    doc.text("CONSTANCIA DE EGRESADO", pageWidth / 2, 65, { align: "center" });

    doc.setFontSize(11);
    doc.text("LA DIRECCIÓN DEL CENTRO DE EDUCACIÓN TÉCNICO PRODUCTIVA", pageWidth / 2, 80, { align: "center" });
    
    doc.setFontSize(16);
    doc.text('"CETPRO PUNO"', pageWidth / 2, 88, { align: "center" });

    // --- CUERPO ---
    const bodyY = 105;
    doc.setFontSize(12);
    doc.setFont("times", "bold");
    doc.text("HACE CONSTAR QUE:", marginL, bodyY);

    doc.setFont("times", "normal");
    
    // Datos seguros (si vienen nulos, ponemos puntos suspensivos)
    const nombre = data?.estudiante?.toUpperCase() || ".......................................................";
    const dni = data?.nro_documento || "..................";
    const programa = data?.especialidad?.toUpperCase() || "..........................................................................."; 
    const ciclo = data?.ciclo?.toUpperCase() || "MÓDULO OCUPACIONAL"; 

    // Texto justificado
    const textoCuerpo = `                           ${nombre}, identificado con DNI N.° ${dni}, ha aprobado la totalidad de las unidades didácticas y experiencias formativas en situaciones reales de trabajo, de acuerdo con el plan de estudio del programa de estudio de ${programa}, correspondiente al ciclo formativo de ${ciclo}.`;

    const splitTexto = doc.splitTextToSize(textoCuerpo, contentWidth);
    doc.text(splitTexto, marginL, bodyY + 12, { align: "justify", maxWidth: contentWidth, lineHeightFactor: 1.5 });

    // --- CIERRE Y FECHA ---
    let currentY = bodyY + 12 + (splitTexto.length * 8) + 10;

    const textoLegal = "Se extiende la presente constancia conforme a lo precisado en el marco legal vigente establecido para los Centros de Educación Técnico Productiva.";
    const splitLegal = doc.splitTextToSize(textoLegal, contentWidth);
    doc.text(splitLegal, marginL, currentY, { align: "justify", maxWidth: contentWidth });

    const hoy = new Date();
    const meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
    const fechaTexto = `Puno, ${hoy.getDate()} de ${meses[hoy.getMonth()]} de ${hoy.getFullYear()}`;
    
    currentY += 20;
    doc.text(`Lugar y fecha:      ${fechaTexto}`, marginL + 20, currentY);

    // --- FIRMA ---
    const firmaY = 240; 
    doc.setLineWidth(0.5);
    doc.line(75, firmaY, 135, firmaY); 
    
    doc.setFont("times", "bold");
    doc.setFontSize(11);
    doc.text("DIRECTOR(A)", pageWidth / 2, firmaY + 5, { align: "center" });
    
    doc.setFont("times", "normal");
    doc.setFontSize(10);
    doc.text("(Firma, post firma y sello)", pageWidth / 2, firmaY + 10, { align: "center" });

    // Abrir PDF
    const pdfBlob = doc.output("blob");
    const pdfUrl = URL.createObjectURL(pdfBlob);
    window.open(pdfUrl, "_blank");

  } catch (error) {
    console.error(error);
    showToast(`Error generando el PDF visual: ${error.message}`, "error");
  }
}