import jsPDF from "jspdf";

export function generateConstanciaEstudiante(data) {
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
  const logoCetpro = "/img/cetprologoHorizontal.png";


  // --- 1. ENCABEZADO REESTRUCTURADO (ALTURA EQUILIBRADA) ---
  const headerY = 12; 
  const headerH = 10;

  try {
    // Logo 1: CETPRO (Izquierda)
    doc.addImage(logoCetpro, "PNG", marginL, headerY, 40, headerH); 
    // Logo 2: Ministerio (Derecha)
    doc.addImage(logoMin, "PNG", pageWidth - marginR - 45, headerY, 45, headerH);
  } catch (e) {
    console.error("Error cargando logos principales");
  }

  // Identidad CETPRO (Bajada para evitar colisión)
  doc.setFont("times", "bold");
  doc.setFontSize(10);
  doc.text("CENTRO DE EDUCACIÓN TÉCNICO PRODUCTIVA", pageWidth / 2, 32, { align: "center" });
  doc.setFontSize(18);
  doc.text('"CETPRO PUNO"', pageWidth / 2, 40, { align: "center" });
  doc.setFontSize(8);
  doc.setFont("times", "normal");
  doc.text("R.D. N° 07592-2024-UGEL 06 | CÓDIGO MODULAR: 469452", pageWidth / 2, 45, { align: "center" });

  doc.setLineWidth(0.5);
  doc.line(marginL, 48, pageWidth - marginR, 48);

  // --- 2. RECUADRO DE FOTO Y TÍTULO ---
  const photoX = pageWidth - marginR - 25;
  const photoY = 55;
  doc.setLineWidth(0.2);
  doc.rect(photoX, photoY, 25, 32); 
  doc.setFontSize(7);
  doc.text("FOTOGRAFÍA", photoX + 12.5, photoY + 14, { align: "center" });

  doc.setFont("times", "bold");
  doc.setFontSize(22);
  doc.text("CONSTANCIA DE ESTUDIOS", pageWidth / 2, 75, { align: "center" });

  // --- 3. CUERPO DEL DOCUMENTO (TEXTO JUSTIFICADO Y LIMPIO) ---
  doc.setFontSize(11);
  doc.setFont("times", "normal");
  
  doc.text("EL/LA DIRECTOR(A) DEL CETPRO PUNO QUE SUSCRIBE, POR LA PRESENTE:", marginL, 95);
  doc.setFont("times", "bold");
  doc.text("HACE CONSTAR QUE:", marginL, 103);

  const nombre = data?.estudiante?.toUpperCase() || "—";
  const dni = data?.nro_documento || "—";
  const carrera = data?.especialidad?.toUpperCase() || "—";
  const modulo = data?.modulo?.toUpperCase() || "—";
  const periodo = data?.periodo || "—";

  doc.setFont("times", "normal");
  
  // Párrafo Legal
  const parrafoLegal = `Al amparo de la Ley N° 28044 (Ley General de Educación), su Reglamento D.S. N° 011-2012-ED y la R.V.M. N° 188-2020-MINEDU; se certifica la situación académica del siguiente administrado, según consta en los folios de matrícula de nuestra institución:`;
  const splitLegal = doc.splitTextToSize(parrafoLegal, contentWidth);
  doc.text(splitLegal, marginL, 110, { align: "justify", maxWidth: contentWidth });
  
  // Datos del Estudiante (Con Negritas)
  doc.text("El/la estudiante:", marginL, 122);
  doc.setFont("times", "bold");
  doc.text(nombre, marginL + 30, 122);
  
  doc.setFont("times", "normal");
  doc.text("identificado(a) con DNI N°:", marginL, 128);
  doc.setFont("times", "bold");
  doc.text(dni, marginL + 45, 128);

  doc.setFont("times", "normal");
  doc.text("Se encuentra con MATRÍCULA VIGENTE, cursando el programa de:", marginL, 134);

  // --- 4. TABLA TÉCNICA (CENTRALIZADA) ---
  const tabX = marginL + 15;
  doc.setFont("times", "bold");
  doc.text("PROGRAMA DE ESTUDIOS :", tabX, 148);
  doc.text("MÓDULO FORMATIVO        :", tabX, 156);
  doc.text("PERÍODO ACADÉMICO      :", tabX, 164);

  doc.setFont("times", "normal");
  doc.text(carrera, tabX + 65, 148);
  doc.text(modulo, tabX + 65, 156);
  doc.text(periodo, tabX + 65, 164);

  // --- 5. CIERRE Y FECHA ---
  const textoFinal = "Se expide la presente a solicitud de la parte interesada para los fines que estime conveniente.";
  doc.text(textoFinal, marginL, 180);

  const hoy = new Date();
  const meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
  const fechaTexto = `Puno, ${hoy.getDate()} de ${meses[hoy.getMonth()]} de ${hoy.getFullYear()}`;
  doc.setFont("times", "bold");
  doc.text(fechaTexto, pageWidth - marginR, 195, { align: "right" });

  // --- 6. ÁREA DE FIRMA Y SELLO DE COORDINACIÓN ---
  const firmaY = 230;
  
  
  doc.setLineWidth(0.6);
  doc.line(75, firmaY, 135, firmaY);
  doc.setFontSize(11);
  doc.text("DIRECCIÓN GENERAL", pageWidth / 2, firmaY + 5, { align: "center" });
  doc.setFontSize(10);
  doc.text("CETPRO PUNO", pageWidth / 2, firmaY + 10, { align: "center" });

  // --- 7. CLÁUSULA LEGAL (AL PIE DE PÁGINA - PROTECCIÓN JURÍDICA) ---
  const footerY = 255;
  doc.setFontSize(8);
  doc.setFont("times", "bold");
  doc.text("CLÁUSULA DE EXONERACIÓN Y VERACIDAD:", marginL, footerY);
  
  doc.setFont("times", "italic");
  const clausula = `La presente constancia se expide con base exclusiva en los registros existentes en el archivo académico de la institución a la fecha de su emisión. Conforme al Principio de Presunción de Veracidad (Art. IV, Num. 1.7 de la Ley N° 27444), la institución se deslinda de toda responsabilidad legal por el uso indebido, alteraciones fraudulentas o falsificaciones que terceros pudieren realizar sobre este documento. La institución no asume perjuicios por demandas derivadas de la interpretación subjetiva de este certificado por parte de entidades externas o particulares.`;
  const splitClausula = doc.splitTextToSize(clausula, contentWidth);
  doc.text(splitClausula, marginL, footerY + 4, { align: "justify", maxWidth: contentWidth, lineHeightFactor: 1.1 });

  // Pie final sutil
  doc.setFontSize(7);
  doc.setTextColor(150);
  doc.text("Validación académica según RVM N° 188-2020-MINEDU. Documento oficial.", pageWidth / 2, 288, { align: "center" });

  const pdfBlob = doc.output('blob');
  const pdfUrl = URL.createObjectURL(pdfBlob);
  window.open(pdfUrl, '_blank');
}