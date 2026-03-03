import jsPDF from "jspdf";
import useHttpRequest from "../composables/useHttpRequest";
import useModalToast from "../composables/useModalToast";

const { show: getDataEstudiante } = useHttpRequest("/egresados");
const { showToast } = useModalToast();

function formatearFechaActual(lugar = "PUNO") {
  const meses = [
    "enero",
    "febrero",
    "marzo",
    "abril",
    "mayo",
    "junio",
    "julio",
    "agosto",
    "septiembre",
    "octubre",
    "noviembre",
    "diciembre",
  ];
  const hoy = new Date();
  return `${lugar}, ${hoy.getDate()} de ${meses[hoy.getMonth()]} de ${hoy.getFullYear()}`;
}

function drawJustifiedRichLine(doc, words, x, y, maxWidth, justify = true) {
  const lineWordsWidth = words.reduce((sum, w) => {
    doc.setFont("helvetica", w.style);
    return sum + doc.getTextWidth(w.text);
  }, 0);

  const gaps = Math.max(words.length - 1, 0);
  doc.setFont("helvetica", "normal");
  const baseSpace = doc.getTextWidth(" ");
  const spaceWidth = justify && gaps > 0 ? (maxWidth - lineWordsWidth) / gaps : baseSpace;

  let xCursor = x;
  words.forEach((w, idx) => {
    doc.setFont("helvetica", w.style);
    doc.text(w.text, xCursor, y);
    xCursor += doc.getTextWidth(w.text);
    if (idx < words.length - 1) xCursor += spaceWidth;
  });
}

function drawJustifiedRichText(doc, segments, x, yStart, maxWidth, lineHeight = 7) {
  const words = [];
  segments.forEach((seg) => {
    const parts = String(seg.text || "").split(/\s+/).filter(Boolean);
    parts.forEach((p) => words.push({ text: p, style: seg.style || "normal" }));
  });

  const lines = [];
  let current = [];
  const measureLine = (arr) => {
    if (!arr.length) return 0;
    let width = 0;
    doc.setFont("helvetica", "normal");
    const space = doc.getTextWidth(" ");
    arr.forEach((w, i) => {
      doc.setFont("helvetica", w.style);
      width += doc.getTextWidth(w.text);
      if (i < arr.length - 1) width += space;
    });
    return width;
  };

  words.forEach((w) => {
    const tentative = [...current, w];
    if (measureLine(tentative) <= maxWidth || current.length === 0) {
      current = tentative;
    } else {
      lines.push(current);
      current = [w];
    }
  });
  if (current.length) lines.push(current);

  let y = yStart;
  lines.forEach((line, idx) => {
    const isLast = idx === lines.length - 1;
    drawJustifiedRichLine(doc, line, x, y, maxWidth, !isLast);
    y += lineHeight;
  });
  return y;
}

export async function generateConstanciaEgresado(id_egresado, codigo = "") {
  try {

 
    const codigoConstancia =codigo ||
      "........................";


    // 2. OBTENER DATOS DEL EGRESADO
    const res = await getDataEstudiante(id_egresado);

    if (!res?.success) {
      showToast("No se pudo obtener datos del egresado", "error");
      return;
    }

    const datos = res.data;


    // 3. EXTRAER DATOS
    const nombre = (datos?.apellidos_nombres || "").toUpperCase();

    const dni = datos?.nro_documento || "....................";

    const programa = (datos?.especialidad || "").toUpperCase();

    // CICLO desde programa
    const cicloRaw = datos?.ciclo || "AUXILIAR -- ";

    const cicloNormalizado = cicloRaw.trim().toUpperCase();

    const ciclo = cicloNormalizado ;


    const nombreCetpro =
      (datos?.cetpro?.cetpro || "----").toUpperCase();

    const lugarCetpro =
      datos?.cetpro?.lugar || "---";

    const anioOficial =
      datos?.cetpro?.anio || new Date().getFullYear();


    // 4. CREAR PDF
    const doc = new jsPDF({
      orientation: "portrait",
      unit: "mm",
      format: "a4",
      compress: true,
    });

    const mL = 25;
    const mR = 25;
    const width = 210;
    const areaW = width - mL - mR;


    // CODIGO
    doc.setLineWidth(0.3);
    doc.rect(mL, 15, 45, 8);

    doc.setFont("helvetica", "bold");
    doc.setFontSize(10);

    doc.text(`N.° ${codigoConstancia}`, mL + 2, 20.5);


    // LOGOS
    try {
      doc.addImage("/img/CetproLOGOO.png", "PNG", width - mR - 22, 14, 18,22);
    } catch { }

    try {
      doc.addImage("/img/LogoMinisterio.png", "PNG", mL, 26, 48, 11);
    } catch { }


    // AÑO
    doc.setFont("helvetica", "italic");
    doc.setFontSize(9);

    doc.text(`"${anioOficial}"`, width / 2, 52, { align: "center" });


    // TITULO
    doc.setFont("helvetica", "bold");
    doc.setFontSize(14);

    doc.text("CONSTANCIA DE EGRESADO", width / 2, 70, {
      align: "center",
    });


    // SUBTITULO
    doc.setFontSize(12);

    doc.text(
      "LA DIRECCIÓN DEL CENTRO DE EDUCACIÓN TÉCNICO-PRODUCTIVA",
      width / 2,
      88,
      { align: "center" }
    );


    doc.setFontSize(12);

    doc.text(`"${nombreCetpro}"`, width / 2, 96, {
      align: "center",
    });


    let cursorY = 115;

    doc.setFontSize(12);
    doc.setFont("helvetica", "bold");

    doc.text("HACE CONSTAR QUE:", mL, cursorY);


    cursorY += 10;

    doc.setFontSize(12);


    const parrafo1 = [
      { text: "", style: "normal" },
      { text: nombre + ",", style: "bold" },
      { text: "identificado (a) con DNI N.°", style: "normal" },
      { text: dni + ",", style: "bold" },
      {
        text:
          "ha aprobado la totalidad de las unidades didácticas y experiencias formativas en situaciones reales de trabajo, de acuerdo con el plan de estudio del programa de estudio de ",
        style: "normal",
      },
      { text: programa + ",", style: "bold" },
    ];

    cursorY = drawJustifiedRichText(
      doc,
      parrafo1,
      mL,
      cursorY,
      areaW,
      7
    );


    const parrafo2 = [
      { text: "correspondiente al ciclo formativo de", style: "normal" },
      { text: ciclo + ".", style: "bold" },
    ];

    cursorY = drawJustifiedRichText(
      doc,
      parrafo2,
      mL,
      cursorY,
      areaW,
      7
    );


    cursorY += 4;

    doc.setFont("helvetica", "normal");

    doc.text(
      "Se extiende la presente constancia conforme a lo precisado en el marco legal vigente establecido para los Centros de Educación Técnico Productiva.",
      mL,
      cursorY,
      { maxWidth: areaW, align: "justify" }
    );


    cursorY += 30;

    doc.setFont("helvetica");
  doc.setFontSize(11);
    doc.text(
      `Lugar y fecha: ${formatearFechaActual(lugarCetpro)}`,
      width - mR,
      cursorY,
      { align: "right" }
    );


    const signY = cursorY + 35;

    doc.line(width / 2 - 40, signY, width / 2 + 40, signY);

    doc.text("DIRECTOR(A)", width / 2, signY + 6, {
      align: "center",
    });


    doc.setFontSize(9);

    doc.text("(Firma, post firma y sello)", width / 2, signY + 11, {
      align: "center",
    });


    window.open(URL.createObjectURL(doc.output("blob")), "_blank");

  } catch (error) {

    showToast(error.message, "error");

  }
}
