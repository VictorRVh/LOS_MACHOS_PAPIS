import jsPDF from "jspdf";
import useHttpRequest from "../composables/useHttpRequest";
import useModalToast from "../composables/useModalToast";

const { store: createDocumento } = useHttpRequest("/estudiante-documento");
const { showToast } = useModalToast();

export async function generateConstanciaEgresado(data) {
  // 1. Registro BD
  try {
    const payload = {
      id_matricula: data.id_matricula,
      tipo_documento: 4,
      fecha_emision: new Date().toISOString().slice(0, 10),
    };
    createDocumento(payload).then((res) => { if(!res.success) console.warn("Error BD"); });
  } catch (e) { console.error(e); }

  // 2. Generación PDF
  try {
    const doc = new jsPDF({ orientation: "portrait", unit: "mm", format: "a4" });

    // --- VARIABLES DE GEOMETRÍA ---
    const mL = 20; // Margen Izquierdo (Un poco más abierto para este diseño)
    const mR = 20; // Margen Derecho
    const width = 210;
    const center = width / 2;
    const maxLineWidth = width - mL - mR;
    
    // --- 1. ENCABEZADO "PROFESIONAL 3 COLUMNAS" ---
    const headerY = 15;
    
    // A) LOGO IZQUIERDO (CETPRO HORIZONTAL)
    // Ruta web relativa (no local C:/...)
    const logoCetpro = "/img/cetprologoHorizontal.png"; 
    try {
        // Ajustamos dimensiones para un logo horizontal (más ancho que alto)
        // Proporción aprox 2.5:1
        doc.addImage(logoCetpro, "PNG", mL, headerY + 2, 40, 12); 
    } catch (e) {}

    // B) LOGO DERECHO (MINISTERIO)
    const logoMin = "/img/LogoMinisterio.png";
    try {
        // Alineado totalmente a la derecha
        doc.addImage(logoMin, "PNG", width - mR - 45, headerY + 4, 50, 11); 
    } catch (e) {}

    // C) TEXTO CENTRAL (JERARQUÍA VISUAL)
    const textCenterY = headerY + 5;
    
    // C.1 Título Superior (Pequeño, Sans Serif, Bold)
    doc.setFont("helvetica", "bold");
    doc.setFontSize(9);
    doc.text("CENTRO DE EDUCACIÓN TÉCNICO PRODUCTIVA", center, textCenterY, { align: "center" });

    // C.2 TÍTULO PRINCIPAL (Grande, Serif, Institucional)
    // Usamos Times para que se vea como en tu referencia (más "oficial")
    doc.setFont("times", "bold"); 
    doc.setFontSize(22);
    doc.text('"CETPRO PUNO"', center, textCenterY + 8, { align: "center" });

    // C.3 Datos Técnicos (Resolución y Código)
    doc.setFont("helvetica", "bold"); // Volvemos a Helvetica para legibilidad técnica
    doc.setFontSize(7);
    // Texto simulado basado en tu imagen, ajusta los datos si son dinámicos
    doc.text("R.D. N° 07592-2024-UGEL 06 ", center, textCenterY + 14, { align: "center" });

    // D) LÍNEA SEPARADORA (El toque final del encabezado)
    const lineY = headerY + 22;
    doc.setLineWidth(0.5); // Línea sólida y visible
    doc.setDrawColor(0); // Negro
    doc.line(mL, lineY, width - mR, lineY);


    // --- 2. SUB-ENCABEZADO (N° y Título Doc) ---
    
    // N° de Registro (Pegado a la derecha o izquierda, estilo moderno a la derecha)
    doc.setFont("helvetica", "bold");
    doc.setFontSize(10);
    // Alineamos el N° a la derecha debajo de la línea
    doc.text("REGISTRO N.° ........................", width - mR, lineY + 8, { align: "right" });

    // TÍTULO DEL DOCUMENTO
    doc.setFont("helvetica", "bold");
    doc.setFontSize(24);
    // Centrado y con espacio
    doc.text("CONSTANCIA DE EGRESADO", center, lineY + 35, { align: "center" });


    // --- 3. CUERPO JUSTIFICADO (ALGORITMO DE NEGRITAS) ---
    let cursorY = lineY + 65; // Posición de inicio del texto

    // Etiqueta
    doc.setFontSize(11);
    doc.text("HACE CONSTAR QUE:", mL, cursorY);
    cursorY += 8;

    // DATOS
    const nombre = data?.estudiante?.toUpperCase() || "MARIA COLLANQUIE QUISPE";
    const dni = data?.nro_documento || "71736658";
    const programa = data?.especialidad?.toUpperCase() || "PELUQUERIA";
    const ciclo = data?.ciclo?.toUpperCase() || "MÓDULO OCUPACIONAL";

    // ESTRUCTURA DEL TEXTO (Rich Text Array)
    const textData = [
      { text: nombre, bold: true },
      { text: ", identificado(a) con DNI N.° ", bold: false },
      { text: dni, bold: true },
      { text: ", ha aprobado satisfactoriamente la totalidad de las unidades didácticas y experiencias formativas en situaciones reales de trabajo, de acuerdo con el plan de estudio del programa de estudios de ", bold: false },
      { text: programa, bold: true },
      { text: ", correspondiente al ciclo formativo de ", bold: false },
      { text: `${ciclo}.`, bold: true }
    ];

    // --- ALGORITMO DE JUSTIFICACIÓN MATEMÁTICA ---
    let allWords = [];
    textData.forEach(chunk => {
        const words = chunk.text.split(" ");
        words.forEach(w => { if(w.length > 0) allWords.push({ text: w, bold: chunk.bold }); });
    });

    let lineBuffer = [];
    let currentLineWidth = 0;
    const fontSizeBody = 11;
    // Interlineado amplio para elegancia (factor 1.5 aprox)
    const lineHeight = 7; 
    const spaceWidth = doc.getStringUnitWidth(" ") * fontSizeBody / doc.internal.scaleFactor;

    allWords.forEach((wordObj) => {
        doc.setFont("helvetica", wordObj.bold ? "bold" : "normal");
        doc.setFontSize(fontSizeBody);
        const wordWidth = doc.getStringUnitWidth(wordObj.text) * fontSizeBody / doc.internal.scaleFactor;

        if (currentLineWidth + wordWidth + spaceWidth <= maxLineWidth) {
            lineBuffer.push({ ...wordObj, width: wordWidth });
            currentLineWidth += wordWidth + spaceWidth;
        } else {
            // Imprimir línea justificada
            printJustifiedLine(doc, lineBuffer, mL, cursorY, maxLineWidth);
            cursorY += lineHeight;
            // Nueva línea
            lineBuffer = [{ ...wordObj, width: wordWidth }];
            currentLineWidth = wordWidth + spaceWidth;
        }
    });
    // Última línea (alineada izquierda)
    if (lineBuffer.length > 0) {
        let x = mL;
        lineBuffer.forEach(w => {
            doc.setFont("helvetica", w.bold ? "bold" : "normal");
            doc.text(w.text, x, cursorY);
            x += w.width + spaceWidth;
        });
        cursorY += lineHeight * 2; 
    }

    // --- 4. CIERRE Y FECHA ---
    
    // Texto Legal
    doc.setFont("helvetica", "normal");
    doc.setFontSize(10);
    const legalText = "Se extiende la presente constancia conforme a lo precisado en el marco legal vigente establecido para los Centros de Educación Técnico Productiva, para los fines que el interesado estime conveniente.";
    const splitLegal = doc.splitTextToSize(legalText, maxLineWidth);
    
    // Justificamos el bloque legal también para consistencia visual
    doc.text(splitLegal, mL, cursorY, { align: "justify", maxWidth: maxLineWidth, lineHeightFactor: 1.5 });

    // FECHA
    const hoy = new Date();
    const meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
    const fechaTexto = `Puno, ${hoy.getDate()} de ${meses[hoy.getMonth()]} de ${hoy.getFullYear()}`; // Fecha dinámica real

    cursorY += 35; // Espacio generoso antes de la fecha
    doc.setFont("helvetica", "bold");
    doc.setFontSize(11);
    doc.text(`Lugar y fecha: ${fechaTexto}`, width - mR, cursorY, { align: "right" });

    // ABRIR
    window.open(URL.createObjectURL(doc.output("blob")), "_blank");

  } catch (error) {
    console.error(error);
    showToast(`Error: ${error.message}`, "error");
  }
}

// --- HELPER DE JUSTIFICACIÓN ---
function printJustifiedLine(doc, words, xStart, y, maxWidth) {
    if (words.length === 0) return;
    const contentWidth = words.reduce((acc, w) => acc + w.width, 0);
    const availableSpace = maxWidth - contentWidth;
    const gap = words.length > 1 ? availableSpace / (words.length - 1) : 0;
    let currentX = xStart;
    words.forEach((w, i) => {
        doc.setFont("helvetica", w.bold ? "bold" : "normal");
        doc.setFontSize(11);
        doc.text(w.text, currentX, y);
        if (i < words.length - 1) currentX += w.width + gap;
    });
}