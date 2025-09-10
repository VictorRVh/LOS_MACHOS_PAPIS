import jsPDF from "jspdf";
import autoTable from "jspdf-autotable";

export function generatePdfMatricula(data) {
  const doc = new jsPDF();

  doc.addImage(
    "img/choclon.jpg",    // Fuente de la imagen
    "JPEG",                // Formato de la imagen
    10,                   // Posición X (desde la izquierda)
    10,                   // Posición Y (desde arriba)
    40,                   // Ancho de la imagen
    15,                   // Alto de la imagen
    null,                 // Nombre opcional para el recurso de imagen (puede ser nulo)
    {                    // Opciones adicionales
      align: 'left',      // Alineación (si aplicable)
      rotation: 45        // Rotación en grados
    }
  );
  // Título
  // Cabecera
  doc.setFontSize(18);
  doc.setFont("helvetica", "bold");
  doc.text("FICHA DE MATRICULA", 105, 15, { align: "center" });
  doc.setFontSize(12);
  doc.text("AÑO 2024", 105, 22, { align: "center" });

autoTable(doc ,{
    startY: 30,
    head: [],
    body: [
      [
        { content: "Nombre del CETPRO", styles: { fillColor: [200, 200, 200], textColor: [0, 0, 0], lineWidth: 0.25, lineColor: [0, 0, 0] } },
        { content: "CETPRO HUANCANÉ", styles: { lineWidth: 0.25, lineColor: [0, 0, 0], halign: 'center' } },
        { content: "DRE", styles: { fillColor: [200, 200, 200], textColor: [0, 0, 0], lineWidth: 0.25, lineColor: [0, 0, 0] } },
        { content: "PUNO", styles: { lineWidth: 0.25, lineColor: [0, 0, 0], halign: 'center' } },
        ""
      ],
      [
        { content: "Código Modular", styles: { fillColor: [200, 200, 200], textColor: [0, 0, 0], lineWidth: 0.25, lineColor: [0, 0, 0] } },
        { content: "469452", styles: { lineWidth: 0.25, lineColor: [0, 0, 0] } },
        { content: "Tipo de Gestión", styles: { fillColor: [200, 200, 200], textColor: [0, 0, 0], lineWidth: 0.25, lineColor: [0, 0, 0] } },
        { content: "Pública", styles: { lineWidth: 0.25, lineColor: [0, 0, 0] } },
        ""
      ],
      [
        { content: "Departamento", styles: { fillColor: [200, 200, 200], textColor: [0, 0, 0], lineWidth: 0.25, lineColor: [0, 0, 0] } },
        { content: "Puno", styles: { lineWidth: 0.25, lineColor: [0, 0, 0] } },
        { content: "Provincia", styles: { fillColor: [200, 200, 200], textColor: [0, 0, 0], lineWidth: 0.25, lineColor: [0, 0, 0] } },
        { content: "Huancané", styles: { lineWidth: 0.25, lineColor: [0, 0, 0] } },
        ""
      ],
      [
        { content: "Distrito", styles: { fillColor: [200, 200, 200], textColor: [0, 0, 0], lineWidth: 0.25, lineColor: [0, 0, 0] } },
        { content: "Huancané", styles: { lineWidth: 0.25, lineColor: [0, 0, 0] } },
        { content: "R.D.", styles: { fillColor: [200, 200, 200], textColor: [0, 0, 0], lineWidth: 0.25, lineColor: [0, 0, 0] } },
        { content: "RD N° 07592 - 2024 - UGEL 06", styles: { lineWidth: 0.25, lineColor: [0, 0, 0] } },
        ""
      ],
      [
        { content: "Programa de estudios", styles: { fillColor: [200, 200, 200], textColor: [0, 0, 0], lineWidth: 0.25, lineColor: [0, 0, 0] } },
        { content: data.especialidad, styles: { lineWidth: 0.25, lineColor: [0, 0, 0] } },
        { content: "Período Lectivo", styles: { fillColor: [200, 200, 200], textColor: [0, 0, 0], lineWidth: 0.25, lineColor: [0, 0, 0] } },
        { content: "2024", styles: { lineWidth: 0.25, lineColor: [0, 0, 0] } },
        ""
      ],
      [
        { content: "Módulo Formativo", styles: { fillColor: [200, 200, 200], textColor: [0, 0, 0], lineWidth: 0.25, lineColor: [0, 0, 0] } },
        { content: data.modulo, styles: { lineWidth: 0.25, lineColor: [0, 0, 0], halign: 'center' } },
        { content: "Periódo de Clase", styles: { fillColor: [200, 200, 200], textColor: [0, 0, 0], lineWidth: 0.25, lineColor: [0, 0, 0] } },
        { content: "POR DEFINIR", styles: { lineWidth: 0.25, lineColor: [0, 0, 0], halign: 'center' } },
        ""
      ],
      [
        { content: "Nivel Formativo", styles: { fillColor: [200, 200, 200], textColor: [0, 0, 0], lineWidth: 0.25, lineColor: [0, 0, 0] } },
        { content: "AUXILIAR TÉCNICO", styles: { lineWidth: 0.25, lineColor: [0, 0, 0] } },
        { content: "Periódo Académico", styles: { fillColor: [200, 200, 200], textColor: [0, 0, 0], lineWidth: 0.25, lineColor: [0, 0, 0] } },
        { content: data.periodo, styles: { lineWidth: 0.25, lineColor: [0, 0, 0], halign: 'center' } },
        ""
      ],
      [
        { content: "Tipo de Plan de estudios", styles: { fillColor: [200, 200, 200], textColor: [0, 0, 0], lineWidth: 0.25, lineColor: [0, 0, 0] } },
        { content: "MODULAR", styles: { lineWidth: 0.25, lineColor: [0, 0, 0], halign: 'center' } },
        { content: "Número de Documento", styles: { fillColor: [200, 200, 200], textColor: [0, 0, 0], lineWidth: 0.25, lineColor: [0, 0, 0] } },
        { content: data.nro_documento, styles: { lineWidth: 0.25, lineColor: [0, 0, 0], halign: 'center' } },
        ""
      ],
      [
        { content: "Nombres y Apellidos", styles: { fillColor: [200, 200, 200], textColor: [0, 0, 0], lineWidth: 0.25, lineColor: [0, 0, 0] } },
        { content: data.estudiante, colSpan: 3, styles: { lineWidth: 0.25, lineColor: [0, 0, 0], halign: 'center' } },
        ""
      ],
    ],
    theme: 'plain',
    styles: {
      fontSize: 10,
      cellPadding: 2,
      halign: 'center',
      valign: 'middle',
      textColor: [0, 0, 0] // Color de texto negro por defecto
    },
    margin: { top: 10, bottom: 10 },
    columnStyles: {
      0: { cellWidth: 'auto' },
      1: { cellWidth: 'auto' },
      2: { cellWidth: 'auto' },
      3: { cellWidth: 'auto' },
      4: { cellWidth: 'auto' }
    }
  });

//   doc.setFontSize(14);
//   doc.text("UNIDADES DIDACTICAS", 105, doc.lastAutoTable ? doc.lastAutoTable.finalY + 10 : 125, { align: "center" });


//   const headerUnits = [
//     { content: 'N°', styles: { fillColor: [200, 200, 200], textColor: [0, 0, 0], halign: 'center', lineWidth: 0.25, lineColor: [0, 0, 0] } },
//     { content: 'UNIDAD DIDÁCTICA', styles: { fillColor: [200, 200, 200], textColor: [0, 0, 0], halign: 'center', lineWidth: 0.25, lineColor: [0, 0, 0] } },
//     { content: 'CRÉDITO', styles: { fillColor: [200, 200, 200], textColor: [0, 0, 0], halign: 'center', lineWidth: 0.25, lineColor: [0, 0, 0] } },
//     { content: 'HORA', styles: { fillColor: [200, 200, 200], textColor: [0, 0, 0], halign: 'center', lineWidth: 0.25, lineColor: [0, 0, 0] } },
//     { content: 'CONDICIÓN', styles: { fillColor: [200, 200, 200], textColor: [0, 0, 0], halign: 'center', lineWidth: 0.25, lineColor: [0, 0, 0] } },
//   ];

//   // 2DA TABLA
//   doc.autoTable({
//     startY: doc.lastAutoTable.finalY + 15,
//     head: [headerUnits],
//     body: data[0].unidades_didacticas.map((unidad, index) => [
//       { content: (index + 1).toString(), styles: { halign: 'center', lineWidth: 0.25, lineColor: [0, 0, 0] } }, // Numeración
//       { content: unidad.nombre_unidad, styles: { halign: 'center', lineWidth: 0.25, lineColor: [0, 0, 0] } },
//       { content: unidad.credito.toString(), styles: { halign: 'center', lineWidth: 0.25, lineColor: [0, 0, 0] } },
//       { content: unidad.hora.toString(), styles: { halign: 'center', lineWidth: 0.25, lineColor: [0, 0, 0] } },
//       { content: 'G', styles: { halign: 'center', lineWidth: 0.25, lineColor: [0, 0, 0] } },
//     ]),
//     theme: 'plain',
//     styles: {
//       fontSize: 10,
//       cellPadding: 2,
//       halign: 'center',
//       valign: 'middle',
//     },
//     margin: { top: 10, bottom: 10 },
//     columnStyles: {
//       0: { cellWidth: 'auto' }, // Configuración de la columna de numeración
//       1: { cellWidth: 'auto' },
//       2: { cellWidth: 'auto' },
//       3: { cellWidth: 'auto' },
//       4: { cellWidth: 'auto' }
//     }
//   });


  // Agregar las líneas para las firmas al final del documento
  const yPosition = doc.lastAutoTable.finalY + 25; // Ajusta el margen según sea necesario

  // Línea para la firma del director
  doc.setFontSize(12);
  doc.setFont("helvetica", "normal");
  doc.text("______________________________", 20, yPosition); // Posición X e Y de la línea
  doc.text("DIRECTOR", 40, yPosition + 10); // Texto debajo de la línea

  // Línea para la firma del estudiante
  doc.text("______________________________", 120, yPosition); // Posición X e Y de la línea
  doc.text("ESTUDIANTE", 145, yPosition + 10); // Texto debajo de la línea

  // Obtener el PDF como un Blob
  const pdfBlob = doc.output('blob');

  // Crear una URL para el Blob
  const pdfUrl = URL.createObjectURL(pdfBlob);

  // Abrir el PDF en una nueva pestaña
  window.open(pdfUrl, '_blank');
}
