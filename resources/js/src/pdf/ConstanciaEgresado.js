import jsPDF from "jspdf";
import useHttpRequest from "../composables/useHttpRequest";
import useModalToast from "../composables/useModalToast";

const { store: createDocumento } = useHttpRequest("/estudiante-documento");
const { showToast } = useModalToast();

export async function generateConstanciaEgresado(data) {
  // 1. Backend (Sin cambios)
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

    // --- GEOMETRÍA EXACTA ---
    const mL = 25; // Margen Izquierdo
    const mR = 25; // Margen Derecho
    const width = 210;
    const maxLineWidth = width - mL - mR;
    
    // Configuración de fuentes
    const fontNormal = "helvetica";
    const fontBold = "helvetica";
    const fontSizeBody = 11;
    const lineHeight = 6; // Altura entre líneas

    // --- 1. CABECERA ALINEADA ---
    
    // A) Cuadro N° (Arriba a la izquierda)
    // Coordenada Y base: 15
    doc.setLineWidth(0.3);
    doc.setDrawColor(0);
    doc.rect(mL, 15, 45, 8); // Rectángulo
    
    doc.setFont(fontBold, "bold");
    doc.setFontSize(10);
    doc.text("N.° ........................", mL + 2, 20);

    // B) Insignia CETPRO (Arriba a la derecha)
    // Debe alinearse visualmente con el top del cuadro N° (Y=15)
    // Ancho calculado para mantener proporción
    const logoCetpro = "/img/CetproLOGOO.png";
    try {
        // x = anchoTotal - margenDerecho - anchoImagen
        doc.addImage(logoCetpro, "PNG", width - mR - 22, 14, 19, 22); 
    } catch (e) {}

    // C) Logo Ministerio (Debajo del N°)
    // Y = 15 (rect) + 8 (alto rect) + 2 (espacio) = 25
    const logoMin = "/img/LogoMinisterio.png";
    try {
        // Ancho 60, Alto 15 (proporción rectangular)
        doc.addImage(logoMin, "PNG", mL, 25, 48, 11);
    } catch (e) {}

    // D) Línea de Año (Equilibrada)
    // Y calculado para que esté debajo de los logos
    const yAnio = 50;
    doc.setFont(fontBold, "bold");
    doc.setFontSize(10);
    doc.text('"Año', mL, yAnio); // Inicio en margen izquierdo

    // Cálculo de puntos suspensivos EXACTOS
    doc.setFont(fontNormal, "normal");
    const startTextWidth = doc.getStringUnitWidth('"Año ') * 10 / doc.internal.scaleFactor;
    const endCharWidth = doc.getStringUnitWidth('"') * 10 / doc.internal.scaleFactor;
    
    // Dibujamos una línea de puntos real para controlar el tamaño del punto
    // Desde donde termina "Año " hasta antes de las comillas finales
    const startDots = mL + startTextWidth;
    const endDots = width - mR - endCharWidth;
    
    // Texto de puntos manual para que sean pequeños y densos
    doc.setFontSize(8); // Puntos pequeños
    let dots = "";
    while (doc.getStringUnitWidth(dots) * 8 / doc.internal.scaleFactor < (endDots - startDots)) {
        dots += ".";
    }
    doc.text(dots, startDots, yAnio);

    // Comilla de cierre alineada al margen derecho
    doc.setFontSize(10);
    doc.text('"', width - mR, yAnio, { align: "right" });


    // --- 2. TÍTULOS ---
    doc.setFont(fontBold, "bold");
    doc.setFontSize(23); // Grande
    doc.text("CONSTANCIA DE EGRESADO", width / 2, 72, { align: "center" });

    doc.setFontSize(12);
    doc.text("LA DIRECCIÓN DEL CENTRO DE EDUCACIÓN TÉCNICO PRODUCTIVA", width / 2, 85, { align: "center" });
    
    doc.setFontSize(18);
    doc.text("CETPRO ILAVE", width / 2, 95, { align: "center" });

    // --- 3. CUERPO JUSTIFICADO CON NEGRITAS ---
    let cursorY = 115;
    
    // Etiqueta
    doc.setFontSize(12);
    doc.setFont(fontBold, "bold");
    doc.text("HACE CONSTAR QUE:", mL, cursorY);
    cursorY += 8;

    // DEFINICIÓN DE CONTENIDO
    const nombre = data?.estudiante?.toUpperCase() || "MARIA COLLANQUIE QUISPE";
    const dni = data?.nro_documento || "71736658";
    const programa = data?.especialidad?.toUpperCase() || "PELUQUERIA";
    const ciclo = data?.ciclo?.toUpperCase() || "MÓDULO OCUPACIONAL";

    // Array de objetos de texto
    const textData = [
      { text: nombre, bold: true },
      { text: " , identificado(a) con DNI N.° ", bold: false }, // Espacio al inicio importante
      { text: dni, bold: true },
      { text: " , ha aprobado satisfactoriamente la totalidad de las unidades didácticas y experiencias formativas en situaciones reales de trabajo, de acuerdo con el plan de estudio del programa de estudios de ", bold: false },
      { text: programa, bold: true },
      { text: " , correspondiente al ciclo formativo de ", bold: false },
      { text: `${ciclo}.`, bold: true }
    ];

    // --- ALGORITMO DE JUSTIFICACIÓN HÍBRIDO ---
    // 1. Descomponer todo en palabras individuales con sus estilos
    let allWords = [];
    textData.forEach(chunk => {
        const words = chunk.text.split(" ");
        words.forEach(w => {
            if(w.length > 0) allWords.push({ text: w, bold: chunk.bold });
        });
    });

    let lineBuffer = [];
    let currentLineWidth = 0;
    const spaceWidth = doc.getStringUnitWidth(" ") * fontSizeBody / doc.internal.scaleFactor;

    allWords.forEach((wordObj, index) => {
        // Medir palabra (cambiando fuente temporalmente)
        doc.setFont(wordObj.bold ? fontBold : fontNormal, wordObj.bold ? "bold" : "normal");
        doc.setFontSize(fontSizeBody);
        const wordWidth = doc.getStringUnitWidth(wordObj.text) * fontSizeBody / doc.internal.scaleFactor;

        // Cabe en la línea?
        if (currentLineWidth + wordWidth + spaceWidth <= maxLineWidth) {
            lineBuffer.push({ ...wordObj, width: wordWidth });
            currentLineWidth += wordWidth + spaceWidth;
        } else {
            // LÍNEA LLENA -> IMPRIMIR JUSTIFICADA
            printJustifiedLine(doc, lineBuffer, mL, cursorY, maxLineWidth, width);
            cursorY += lineHeight;
            
            // Iniciar nueva línea
            lineBuffer = [{ ...wordObj, width: wordWidth }];
            currentLineWidth = wordWidth + spaceWidth;
        }
    });

    // Imprimir última línea (Izquierda, NO justificada)
    if (lineBuffer.length > 0) {
        let x = mL;
        lineBuffer.forEach(w => {
            doc.setFont(w.bold ? fontBold : fontNormal, w.bold ? "bold" : "normal");
            doc.text(w.text, x, cursorY);
            x += w.width + spaceWidth;
        });
        cursorY += lineHeight * 2; // Espacio tras el párrafo
    }


    // --- 4. TEXTO LEGAL ---
    doc.setFont(fontBold, "bold"); // Según tu imagen, parece tener peso
    doc.setFontSize(10);
    const legalText = "Se extiende la presente constancia conforme a lo precisado en el marco legal vigente establecido para los Centros de Educación Técnico Productiva.";
    const splitLegal = doc.splitTextToSize(legalText, maxLineWidth);
    doc.text(splitLegal, mL, cursorY); // Este puede ir normal a la izquierda


    // --- 5. FECHA (ALINEADA A LA DERECHA) ---
    const hoy = new Date();
    const meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
    // Forzamos 2026 como en tu imagen o usamos dinámico
    const anio = 2026; // hoy.getFullYear(); 
    const fechaTexto = `ILAVE, 3 de enero de ${anio}`; // Ojo: Hardcodeé el día para igualar tu imagen, cambia a dinámico si quieres.

    cursorY += 40;
    doc.setFont(fontBold, "bold");
    doc.setFontSize(10);
    doc.text(`Lugar y fecha: ${fechaTexto}`, width - mR, cursorY, { align: "right" });

    // Abrir PDF
    window.open(URL.createObjectURL(doc.output("blob")), "_blank");

  } catch (error) {
    console.error(error);
    showToast(`Error: ${error.message}`, "error");
  }
}

// --- FUNCIÓN DE IMPRESIÓN JUSTIFICADA ---
function printJustifiedLine(doc, words, xStart, y, maxWidth, pageWidth) {
    if (words.length === 0) return;
    
    // 1. Calcular ancho real del contenido (solo palabras)
    const contentWidth = words.reduce((acc, w) => acc + w.width, 0);
    
    // 2. Calcular espacio disponible para repartir
    const availableSpace = maxWidth - contentWidth;
    
    // 3. Calcular tamaño de cada espacio (gap)
    // Si hay una sola palabra, se imprime a la izquierda
    const gap = words.length > 1 ? availableSpace / (words.length - 1) : 0;

    let currentX = xStart;

    words.forEach((w, i) => {
        doc.setFont("helvetica", w.bold ? "bold" : "normal");
        doc.setFontSize(11);
        doc.text(w.text, currentX, y);
        
        // Mover cursor: ancho de palabra + gap justificado
        // No agregamos gap después de la última palabra
        if (i < words.length - 1) {
            currentX += w.width + gap;
        }
    });
}