import jsPDF from "jspdf";
import QRCode from "qrcode";
import useHttpRequest from "../composables/useHttpRequest";
import useModalToast from "../composables/useModalToast";

const { store: createCertificado } = useHttpRequest("/estudiante-constancia");
const { showConfirmModal, showToast } = useModalToast();

export async function generateConstanciaEstudiante(data) {
  try {
    const payload = {
      id_matricula: data.id_matricula,
      tipo_documento: 1, // CONSTANCIA DE ESTUDIOS
      fecha_emision: new Date().toISOString().slice(0, 10),
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
    const marginL = 25;
    const marginR = 20;
    const contentWidth = pageWidth - marginL - marginR;

    const logoMin = "/img/LogoMinisterio.png";
    //const logoCetpro = "/img/cetprologoHorizontal.png";
    const logoCetpro = "/img/CetproLOGOO.png";

    // --- 1. ENCABEZADO (ALINEADO Y REDUCIDO) ---
    // Definimos una Y base para que logos y texto estén en la misma fila
    const headerY = 12; 
    
    // Reducimos tamaño de logos (Ancho reducido, Alto reducido a 8-9mm)
    const logoH = 9; 
    const logoW_L = 30; // Antes 40
    const logoW_R = 35; // Antes 45

    try {
      // Logos alineados verticalmente con el texto
      doc.addImage(logoCetpro, "PNG", marginL+5, headerY-5, logoW_L-13, logoH+10);
      doc.addImage(logoMin, "PNG", pageWidth - marginR - logoW_R, headerY, logoW_R+2, logoH);
    } catch (e) {
      console.error("Error cargando logos");
    }

    // Texto CENTRADO entre los logos (Subimos Y para alinear)
    doc.setFont("times", "bold");
    doc.setFontSize(9);
    doc.text("CENTRO DE EDUCACIÓN TÉCNICO PRODUCTIVA", pageWidth / 2, headerY + 2, { align: "center" });
    
    doc.setFontSize(16);
    doc.text('"CETPRO PUNO"', pageWidth / 2, headerY + 8, { align: "center" }); // Centrado visualmente
    
    doc.setFontSize(7);
    doc.setFont("times", "normal");
    doc.text("R.D. N° 07592-2024-UGEL 06 | CÓDIGO MODULAR: 469452", pageWidth / 2, headerY + 12, { align: "center" });

    // Línea separadora (Subida para pegar el encabezado)
    const lineY = headerY + 16;
    doc.setLineWidth(0.5);
    doc.line(marginL, lineY, pageWidth - marginR, lineY);

    // --- 2. FOTO Y TÍTULO (RE-COORDENADAS HACIA ARRIBA) ---
    // Subimos la foto y el título porque el encabezado ahora es más compacto
    const photoX = pageWidth - marginR - 25;
    const photoY = lineY + 8; // Pegado a la línea (aprox Y=36)
    
    doc.setLineWidth(0.2);
    doc.rect(photoX, photoY, 25, 32);
    doc.setFontSize(7);
    doc.text("FOTOGRAFÍA", photoX + 12.5, photoY + 14, { align: "center" });

    doc.setFont("times", "bold");
    doc.setFontSize(22);
    // Título alineado con la foto
    doc.text("CONSTANCIA DE ESTUDIOS", pageWidth / 2, photoY + 20, { align: "center" });

    // --- 3. CUERPO DEL DOCUMENTO ---
    doc.setFontSize(11);
    doc.setFont("times", "normal");

    // Inicio del texto (Subido desde 95 a 80 para cerrar hueco)
    const textStartY = photoY + 42; 

    doc.text("EL/LA DIRECTOR(A) DEL CETPRO PUNO QUE SUSCRIBE, POR LA PRESENTE:", marginL, textStartY);
    doc.setFont("times", "bold");
    doc.text("HACE CONSTAR QUE:", marginL, textStartY + 8);

    const nombre = data?.estudiante?.toUpperCase() || "—";
    const dni = data?.nro_documento || "—";
    const carrera = data?.especialidad?.toUpperCase() || "—";
    const modulo = data?.modulo?.toUpperCase() || "—";
    const periodo = data?.periodo || "—";

    doc.setFont("times", "normal");
    const parrafoLegal = `Al amparo de la Ley N° 28044 (Ley General de Educación), su Reglamento D.S. N° 011-2012-ED y la R.V.M. N° 188-2020-MINEDU; se certifica la situación académica del siguiente administrado, según consta en los folios de matrícula de nuestra institución:`;

    const splitLegal = doc.splitTextToSize(parrafoLegal, contentWidth);
    doc.text(splitLegal, marginL, textStartY + 15, { align: "justify", maxWidth: contentWidth });

    // --- DATOS DEL ESTUDIANTE ---
    // Ajustamos cursor basado en el nuevo inicio
    let cursorY = textStartY + 38; 
    const lineHeight = 8;

    doc.text("El/la estudiante:", marginL, cursorY);
    doc.setFont("times", "bold");
    doc.text(nombre, marginL + 35, cursorY);

    cursorY += lineHeight;
    doc.setFont("times", "normal");
    doc.text("identificado(a) con DNI N°:", marginL, cursorY);
    doc.setFont("times", "bold");
    doc.text(dni, marginL + 50, cursorY);

    cursorY += lineHeight;
    doc.setFont("times", "normal");
    doc.text("Se encuentra con MATRÍCULA VIGENTE, cursando el programa de:", marginL, cursorY);

    // --- 4. TABLA TÉCNICA ---
    const tabY = cursorY + 14;
    const tabX = marginL + 15;

    doc.setFont("times", "bold");
    doc.text("PROGRAMA DE ESTUDIOS :", tabX, tabY);
    doc.text("MÓDULO FORMATIVO        :", tabX, tabY + 8);
    doc.text("PERÍODO ACADÉMICO      :", tabX, tabY + 16);

    doc.setFont("times", "normal");
    doc.text(carrera, tabX + 65, tabY);
    doc.text(modulo, tabX + 65, tabY + 8);
    doc.text(periodo, tabX + 65, tabY + 16);

    // --- 5. CIERRE Y FECHA ---
    const cierreY = tabY + 30;
    const textoFinal = "Se expide la presente a solicitud de la parte interesada para los fines que estime conveniente.";
    doc.text(textoFinal, marginL, cierreY);

    const hoy = new Date();
    const meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
    const fechaTexto = `Puno, ${hoy.getDate()} de ${meses[hoy.getMonth()]} de ${hoy.getFullYear()}`;

    doc.setFont("times", "bold");
    doc.text(fechaTexto, pageWidth - marginR, cierreY + 10, { align: "right" });

    // --- 6. ZONA DE FIRMA Y QR (FIJA AL FONDO) ---
    const zonaFirmaY = 225;

    const qrUrl = `https://choclon.com/verificarCertificado/${data?.id_matricula}`;
    const qrBase64 = await QRCode.toDataURL(qrUrl, {
      width: 300,
      margin: 1,
      errorCorrectionLevel: "H",
    });

    const qrSize = 26;
    const padding = 3;
    const qrX = pageWidth - marginR - qrSize - padding;
    const qrY = zonaFirmaY - 15;

    doc.setLineWidth(0.3);
    doc.setDrawColor(0);
    doc.rect(qrX - padding, qrY - padding, qrSize + (padding * 2), qrSize + (padding * 2) + 5);

    doc.addImage(qrBase64, "PNG", qrX, qrY, qrSize, qrSize);
    doc.setFontSize(6);
    doc.text("Verificación QR", qrX + (qrSize / 2), qrY + qrSize + 4, { align: "center" });

    // --- FIRMA CENTRAL ---
    doc.setLineWidth(0.6);
    doc.line(75, zonaFirmaY, 135, zonaFirmaY);
    doc.setFontSize(11);
    doc.setFont("times", "bold");
    doc.text("DIRECCIÓN GENERAL", pageWidth / 2, zonaFirmaY + 5, { align: "center" });
    doc.setFontSize(10);
    doc.text("CETPRO PUNO", pageWidth / 2, zonaFirmaY + 10, { align: "center" });

    // --- 7. CLÁUSULA LEGAL (PIE DE PÁGINA) ---
    const footerY = 258;
    doc.setFontSize(8);
    doc.setFont("times", "bold");
    doc.text("CLÁUSULA DE EXONERACIÓN Y VERACIDAD:", marginL, footerY);

    doc.setFont("times", "italic");
    const clausula = `La presente constancia se expide con base exclusiva en los registros existentes en el archivo académico de la institución a la fecha de su emisión. Conforme al Principio de Presunción de Veracidad (Art. IV, Num. 1.7 de la Ley N° 27444), la institución se deslinda de toda responsabilidad legal por el uso indebido, alteraciones fraudulentas o falsificaciones que terceros pudieren realizar sobre este documento. La institución no asume perjuicios por demandas derivadas de la interpretación subjetiva de este certificado por parte de entidades externas o particulares.`;
    const splitClausula = doc.splitTextToSize(clausula, contentWidth);
    doc.text(splitClausula, marginL, footerY + 4, { align: "justify", maxWidth: contentWidth, lineHeightFactor: 1.1 });

    doc.setFontSize(7);
    doc.setTextColor(150);
    doc.text("Validación académica según RVM N° 188-2020-MINEDU. Documento oficial.", pageWidth / 2, 288, { align: "center" });

    const pdfBlob = doc.output("blob");
    const pdfUrl = URL.createObjectURL(pdfBlob);
    window.open(pdfUrl, "_blank");

  } catch (error) {
    showToast(`Error generando documento: ${error.message}`, "warning");
  }
}