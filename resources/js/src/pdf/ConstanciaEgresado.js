import jsPDF from "jspdf";
import useHttpRequest from "../composables/useHttpRequest";
import useModalToast from "../composables/useModalToast";

const { store: createDocumento } = useHttpRequest("/estudiante-documento");
const { show: constanciaEgresado } = useHttpRequest("/egresados");
const { showToast } = useModalToast();


// =============================
// FECHA
// =============================
function formatearFechaActual(lugar = "PUNO") {

  const meses = [
    "enero","febrero","marzo","abril","mayo","junio",
    "julio","agosto","septiembre","octubre","noviembre","diciembre"
  ];

  const hoy = new Date();

  return `${lugar}, ${hoy.getDate()} de ${meses[hoy.getMonth()]} de ${hoy.getFullYear()}`;
}



// =============================
// JUSTIFICADO RICO
// =============================

function drawJustifiedRichLine(doc, words, x, y, maxWidth, justify = true) {

  const lineWordsWidth = words.reduce((sum, w) => {
    doc.setFont("helvetica", w.style);
    return sum + doc.getTextWidth(w.text);
  }, 0);

  const gaps = Math.max(words.length - 1, 0);

  doc.setFont("helvetica", "normal");

  const baseSpace = doc.getTextWidth(" ");

  const spaceWidth =
    justify && gaps > 0
      ? (maxWidth - lineWordsWidth) / gaps
      : baseSpace;

  let xCursor = x;

  words.forEach((w, idx) => {

    doc.setFont("helvetica", w.style);

    doc.text(w.text, xCursor, y);

    xCursor += doc.getTextWidth(w.text);

    if (idx < words.length - 1)
      xCursor += spaceWidth;

  });

}



function drawJustifiedRichText(
  doc,
  segments,
  x,
  yStart,
  maxWidth,
  lineHeight = 7
) {

  const words = [];

  segments.forEach(seg => {

    const parts = String(seg.text)
      .split(/\s+/)
      .filter(Boolean);

    parts.forEach(p =>
      words.push({
        text: p,
        style: seg.style || "normal"
      })
    );

  });


  const lines = [];

  let current = [];

  const measureLine = arr => {

    if (!arr.length) return 0;

    let width = 0;

    doc.setFont("helvetica", "normal");

    const space = doc.getTextWidth(" ");

    arr.forEach((w, i) => {

      doc.setFont("helvetica", w.style);

      width += doc.getTextWidth(w.text);

      if (i < arr.length - 1)
        width += space;

    });

    return width;

  };


  words.forEach(w => {

    const tentative = [...current, w];

    if (
      measureLine(tentative) <= maxWidth ||
      current.length === 0
    ) {
      current = tentative;
    }
    else {
      lines.push(current);
      current = [w];
    }

  });


  if (current.length)
    lines.push(current);


  let y = yStart;

  lines.forEach((line, idx) => {

    const isLast = idx === lines.length - 1;

    drawJustifiedRichLine(
      doc,
      line,
      x,
      y,
      maxWidth,
      !isLast
    );

    y += lineHeight;

  });

  return y;

}



// =============================
// FUNCIÓN PRINCIPAL
// =============================

export async function generateConstanciaEgresado(id_egresado, codigo = "") {

  try {

    // =============================
    // OBTENER DATOS DEL API
    // =============================

    const response = await constanciaEgresado(id_egresado);

    if (!response || !response.success) {

      showToast("No se pudo obtener los datos del egresado", "error");

      return;
    }

    const data = response.data;



    // =============================
    // REGISTRAR DOCUMENTO
    // =============================

    await createDocumento({

      id_egresado: id_egresado,

      tipo_documento: 4,

      fecha_emision: new Date().toISOString().slice(0,10),

      codigo: codigo || null

    });



    // =============================
    // DATOS
    // =============================

    const nombre =
      (data.apellidos_nombres || "......................")
      .toUpperCase();

    const dni =
      data.dni || "................";

    const especialidad =
      (data.especialidad || "................")
      .toUpperCase();

    const programa =
      (data.programa || "AUXILIAR TÉCNICO")
      .toUpperCase();

    const cetproNombre =
      (data.cetpro?.cetpro || "CETPRO")
      .toUpperCase();

    const lugar =
      data.cetpro?.lugar || "PUNO";

    const director =
      (data.cetpro?.director || "....................")
      .toUpperCase();



    // =============================
    // PDF
    // =============================

    const doc = new jsPDF({

      orientation: "portrait",

      unit: "mm",

      format: "a4"

    });


    const mL = 25;

    const mR = 25;

    const width = 210;

    const areaW = width - mL - mR;



    // código

    doc.rect(mL, 15, 45, 8);

    doc.setFont("helvetica","bold");

    doc.setFontSize(10);

    doc.text(

      `N.° ${codigo || ".........."}`,

      mL + 2,

      20

    );



    // logos

    try {

      doc.addImage("/img/CetproLOGOO.png","PNG",

        width - mR - 22,14,22,22);

    } catch {}



    try {

      doc.addImage("/img/LogoMinisterio.png","PNG",

        mL,26,48,11);

    } catch {}



    // titulo

    doc.setFontSize(22);

    doc.text(

      "CONSTANCIA DE EGRESADO",

      width/2,

      75,

      {align:"center"}

    );



    doc.setFontSize(11);

    doc.text(

      "LA DIRECCIÓN DEL CENTRO DE EDUCACIÓN TÉCNICO PRODUCTIVA",

      width/2,

      88,

      {align:"center"}

    );



    doc.setFontSize(16);

    doc.text(

      `"${cetproNombre}"`,

      width/2,

      96,

      {align:"center"}

    );



    // contenido

    let cursorY = 115;

    doc.setFontSize(12);

    doc.setFont("helvetica","bold");

    doc.text(

      "HACE CONSTAR QUE:",

      mL,

      cursorY

    );



    cursorY += 10;

    doc.setFontSize(11);



    const parrafo = [

      {text:"Don (ña)",style:"normal"},

      {text:nombre + ",",style:"bold"},

      {text:"identificado (a) con DNI N°",style:"normal"},

      {text:dni + ",",style:"bold"},

      {text:"ha concluido satisfactoriamente el programa de estudios de",style:"normal"},

      {text:especialidad + ",",style:"bold"},

      {text:"correspondiente al nivel formativo de",style:"normal"},

      {text:programa + ".",style:"bold"}

    ];



    cursorY = drawJustifiedRichText(

      doc,

      parrafo,

      mL,

      cursorY,

      areaW,

      7.2

    );



    cursorY += 10;



    doc.text(

      "Se expide la presente constancia a solicitud del interesado para los fines que estime conveniente.",

      mL,

      cursorY,

      {maxWidth:areaW,align:"justify"}

    );



    cursorY += 30;



    doc.setFont("helvetica","bold");

    doc.text(

      `Lugar y fecha: ${formatearFechaActual(lugar)}`,

      width - mR,

      cursorY,

      {align:"right"}

    );



    const signY = cursorY + 35;



    doc.line(

      width/2 - 40,

      signY,

      width/2 + 40,

      signY

    );



    doc.text(

      director,

      width/2,

      signY + 6,

      {align:"center"}

    );



    doc.setFont("helvetica","normal");

    doc.text(

      "DIRECTOR(A)",

      width/2,

      signY + 12,

      {align:"center"}

    );



    // abrir pdf

    window.open(

      URL.createObjectURL(doc.output("blob")),

      "_blank"

    );



  }

  catch(error){

    showToast(

      error.message || "Error al generar constancia",

      "error"

    );

  }

}