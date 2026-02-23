import jsPDF from "jspdf";
import autoTable from "jspdf-autotable";

export function generatePdfMatricula(data) {
  const doc = new jsPDF({
    orientation: "landscape",
    unit: "mm",
    format: "a4",
  });

  const logoUrl = "/img/LogoMinisterio.png";
  try {
    doc.addImage(logoUrl, "PNG", 15, 10, 70, 15);
  } catch (e) {
    console.error("Error cargando logo");
  }

  doc.setFontSize(10);
  doc.setFont("helvetica", "bold");
  doc.text("ANEXO N° 1", 148.5, 10, { align: "center" });
  doc.setFontSize(14);
  doc.text("FICHA DE REGISTRO DE MATRÍCULA", 148.5, 17, { align: "center" });
  doc.setFontSize(12);
  doc.text(`AÑO: ${data?.ficha?.periodo || "2025"}`, 148.5, 24, { align: "center" });

  const sL = { fillColor: [200, 200, 200], textColor: [0, 0, 0], fontStyle: "normal", lineWidth: 0.2, lineColor: [0, 0, 0] };
  const sD = { textColor: [0, 0, 0], fontStyle: "normal", lineWidth: 0.2, lineColor: [0, 0, 0] };

  autoTable(doc, {
    startY: 30,
    body: [
      [
        { content: "Nombre del CETPRO", styles: sL },
        { content: "CETPRO PUNO", styles: sD },
        { content: "DRE/GRE", styles: sL },
        { content: "PUNO", styles: sD },
      ],
      [
        { content: "Código modular", styles: sL },
        { content: "469452", styles: sD },
        { content: "UGEL", styles: sL },
        { content: "Puno", styles: sD },
      ],
      [
        { content: "Departamento", styles: sL },
        { content: "PUNO", styles: sD },
        { content: "Tipo de Gestión", styles: sL },
        { content: "Pública", styles: sD },
      ],
        [
        { content: "Provincia", styles: sL },
        { content: "PUNO", styles: sD },
        { content: "Periodo electivo:", styles: sL },
        { content: data?.ficha?.periodo || "—", styles: sD },
      ],
      [
        { content: "Distrito", styles: sL },
        { content: "PUNO", styles: sD },
        { content: "Periodo académico:", styles: sL },
        { content: data?.ficha?.periodo || "—", styles: sD },
      ],
      [
        { content: "Programa de estudios", styles: sL },
        { content: data?.ficha?.especialidad || "—", styles: sD },
        { content: "Inicio y término de clases:", styles: sL },
        { content: data?.ficha?.periodo_clases || "—", styles: sD },
      ],
      [
        { content: "Módulo", styles: sL },
        { content: data?.ficha?.modulo || "—", styles: sD },
        { content: "Apellidos y nombres del estudiante", styles: sL },
        { content: data?.ficha?.estudiante || "—", styles: sD },
      ],

      [
        { content: "Ciclo:", styles: sL },
        { content: "AUXILIAR TÉCNICO", styles: sD },
        { content: "Número de documento de identidad del estudiante:", styles: sL },
        { content: data?.ficha?.nro_documento || "—", styles: sD },
      ],
      [
        { content: "Modalidad del servicio educativo", styles: sL },
        { content: "Presencial", styles: sD },
        { content: "Grado de instrucción del estudiante", styles: sL },
        { content: data?.ficha?.grado_instruccion || "—", styles: sD },
      ],
      [
        { content: "Apellidos y Nombres", styles: sL },
        { content: data?.ficha?.estudiante || "—", styles: sD },
        { content: "Edad del estudiante", styles: sL },
        { content: data?.ficha?.edad_estudiante || "—", styles: sD },
      ],
    ],
    theme: "grid",
    styles: { fontSize: 9, cellPadding: 1.2 },
    columnStyles: { 0: { cellWidth: 55 }, 1: { cellWidth: 85 }, 2: { cellWidth: 55 }, 3: { cellWidth: 82 } },
    margin: { left: 10, right: 10 },
  });

  doc.setFontSize(10);
  doc.setFont("helvetica", "bold");
  doc.text("UNIDADES DIDÁCTICAS / MÓDULOS", 148.5, doc.lastAutoTable.finalY + 12, { align: "center" });

  autoTable(doc, {
    startY: doc.lastAutoTable ? doc.lastAutoTable.finalY + 20 : 20,
    head: [[
      { content: "N°", styles: { halign: "center" } },
      { content: "UNIDAD DIDÁCTICA", styles: { halign: "center" } },
      { content: "CRÉDITOS", styles: { halign: "center" } },
      { content: "HORAS", styles: { halign: "center" } },
      { content: "OBSERVACIONES", styles: { halign: "center" } }
    ]],
    body: (data?.capacidades_terminales || []).map((cap, i) => [
      { content: (i + 1).toString(), styles: { halign: "center" } },
      { content: cap.nombre_capacidad || "—" },
      { content: cap.creditos?.toString() || "—", styles: { halign: "center" } },
      { content: cap.horas?.toString() || "—", styles: { halign: "center" } },
      { content: "", styles: { halign: "center" } },
    ]),
    theme: "grid",
    styles: {
      fontSize: 8,
      cellPadding: 2,
      lineColor: [0, 0, 0],
      lineWidth: 0.2
    },
    headStyles: {
      fillColor: [200, 200, 200],
      textColor: [0, 0, 0]
    },
    columnStyles: {
      0: { cellWidth: 15 },   // N°
      1: { cellWidth: 150 },   // Nombre unidad
      2: { cellWidth: 35 },   // Créditos
      3: { cellWidth: 35 },   // Horas

    },
    margin: { left: 10, right: 10 },
  });

  // PRACTICAS
  doc.setFontSize(10);
  doc.setFont("helvetica", "bold");
  doc.text(
    "EXPERIENCIAS FORMATIVAS EN SITUACIONES REALES DE TRABAJO",
    148.5,
    doc.lastAutoTable.finalY + 12,
    { align: "center" }
  );

  autoTable(doc, {
    startY: doc.lastAutoTable.finalY + 20,
    head: [[
      { content: "EN EL CETPRO /CENTRO LABORAL", styles: { halign: "center" }, styles: { halign: "center" }  },
      { content: "CRÉDITOS", styles: { halign: "center" }, styles: { halign: "center" }  },
      { content: "HORAS", styles: { halign: "center" }, styles: { halign: "center" }  },
      { content: "OBSERVACIONES", styles: { halign: "center" } }
    ]],
    body: data?.experiencia_formativa
      ? [[
      // { content: data.experiencia_formativa.nombre_experiencia || "—", styles: { halign: "center" }},
      { content: "CETPRO"|| "—", styles: { halign: "center" }},
        {content: data.experiencia_formativa.creditos?.toString() || "—", styles: { halign: "center" }},
        {content: data.experiencia_formativa.horas?.toString() || "—", styles: { halign: "center" }},
        { content: "", styles: { halign: "center" } },
      ]]
      : [],
    theme: "grid",
    styles: {
      fontSize: 8,
      cellPadding: 2,
      lineColor: [0, 0, 0],
      lineWidth: 0.2
    },
    headStyles: {
      fillColor: [200, 200, 200],
      textColor: [0, 0, 0]
    },
    columnStyles: {
      0: { cellWidth: 150 },
      1: { cellWidth: 35 },
      2: { cellWidth: 35 },
    },
    margin: { left: 10, right: 10 },
  });

  /*
    doc.text("UNIDADES DIDÁCTICAS DE SUBSANACIÓN", 148.5, doc.lastAutoTable.finalY + 7, { align: "center" });
  
    autoTable(doc, {
      startY: doc.lastAutoTable.finalY + 10,
      head: [["N°", "MÓDULO", "CONDICIÓN"]],
      body: [["1", "—", "—"]],
      theme: "grid",
      styles: { fontSize: 8, cellPadding: 1.5, lineColor: [0, 0, 0], lineWidth: 0.2, halign: "center" },
      headStyles: { fillColor: [255, 255, 255], textColor: [0, 0, 0] },
      columnStyles: { 0: { cellWidth: 15 }, 1: { cellWidth: 222 }, 2: { cellWidth: 40 } },
      margin: { left: 10, right: 10 },
    });
  */
  const fY = doc.lastAutoTable.finalY + 25;
  doc.setFontSize(8);
  doc.line(40, fY, 90, fY);
  doc.text("Director", 65, fY + 4, { align: "center" });
  doc.text("Sello, firma, post firma", 65, fY + 8, { align: "center" });

  doc.line(123, fY, 173, fY);
  doc.text("Coordinador Académico", 148.5, fY + 4, { align: "center" });
  doc.text("Sello, firma, post firma", 148.5, fY + 8, { align: "center" });

  doc.line(207, fY, 257, fY);
  doc.text("Estudiante", 232, fY + 4, { align: "center" });
  doc.text("Sello, firma, post firma", 232, fY + 8, { align: "center" });

  const pdfBlob = doc.output("blob");
  const pdfUrl = URL.createObjectURL(pdfBlob);
  window.open(pdfUrl, "_blank");
}