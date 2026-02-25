import jsPDF from "jspdf";
import autoTable from "jspdf-autotable";
import useHttpRequest from "../composables/useHttpRequest";

const { index: getCetproData } = useHttpRequest("/cetprodata");

let cetproCache = null;
let cetproPromise = null;

async function getCetproCached() {
  if (cetproCache) return cetproCache;
  if (!cetproPromise) {
    cetproPromise = getCetproData()
      .then((res) => {
        cetproCache = res || {};
        return cetproCache;
      })
      .catch(() => ({}))
      .finally(() => {
        cetproPromise = null;
      });
  }
  return cetproPromise;
}

function fechaLargaEs(fecha = new Date()) {
  const meses = [
    "enero", "febrero", "marzo", "abril", "mayo", "junio",
    "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre",
  ];
  return `${fecha.getDate()} de ${meses[fecha.getMonth()]} de ${fecha.getFullYear()}`;
}

function getNombreCompleto(estudiante = {}) {
  if (estudiante?.nombre_completo) return estudiante.nombre_completo;
  return [estudiante?.apellido_paterno, estudiante?.apellido_materno, estudiante?.nombre]
    .filter(Boolean)
    .join(" ");
}

function drawHeader(doc, cetpro, pageW, margin) {
  const nombreCetpro = String(cetpro?.cetpro || "CETPRO").toUpperCase();
  const rdAut = String(cetpro?.rd_autorizacion || "-");
  const rdConv = String(cetpro?.rd_conversion || "-");
  const anio = String(cetpro?.anio || "-");

  // Logos (sin bordes)
  const leftLogoW = 22;
  const rightLogoW = 22;

  try {
    doc.addImage("/img/cetproLOGOO.png", "PNG", margin + 4.1, 11.1, 13.8, 13.8, undefined, "FAST");
  } catch (e) {
    try {
      doc.addImage("/img/CetproLOGOO.png", "PNG", margin + 4.1, 11.1, 13.8, 13.8, undefined, "FAST");
    } catch (err) {}
  }

  try {
    doc.addImage("/img/insignia.png", "PNG", pageW - margin - 17.3, 11.3, 12.8, 12.8, undefined, "FAST");
  } catch (e) {
    try {
      doc.addImage("/img/CetproLOGOO.png", "PNG", pageW - margin - 17.3, 11.3, 12.8, 12.8, undefined, "FAST");
    } catch (err) {}
  }

  // Bloque central del membrete
  const xL = margin + leftLogoW + 3;
  const xR = pageW - margin - rightLogoW - 3;
  const center = (xL + xR) / 2;

  doc.setDrawColor(182, 190, 202);
  doc.setLineWidth(0.2);
  doc.line(xL, 10.2, xR, 10.2);
  doc.line(xL, 24.8, xR, 24.8);

  doc.setTextColor(22, 36, 58);
  // Encabezado compacto de 3 filas
  doc.setFont("helvetica", "bold");
  doc.setFontSize(9.5);
  doc.text(`CETPRO ${nombreCetpro} - REPORTE ACADÉMICO POR ESPECIALIDAD`, center, 14.8, { align: "center" });

  doc.setFont("helvetica", "normal");
  doc.setFontSize(7.9);
  doc.text(`R.D. Autorización: ${rdAut} | R.D. Conversión: ${rdConv}`, center, 19.2, { align: "center" });

  doc.setFont("helvetica", "italic");
  doc.setFontSize(7.6);
  doc.text(`"${anio}"`, center, 23.4, { align: "center" });

  // Líneas institucionales
  doc.setDrawColor(22, 36, 58);
  doc.setLineWidth(0.6);
  doc.line(margin, 27.0, pageW - margin, 27.0);
  doc.setDrawColor(165, 176, 193);
  doc.setLineWidth(0.2);
  doc.line(margin, 28.3, pageW - margin, 28.3);
}

function drawFooter(doc, cetpro, pageW, pageH, margin, page, totalPages) {
  const region = String(cetpro?.region || "-").toUpperCase();
  const provincia = String(cetpro?.provincia || "-").toUpperCase();
  const distrito = String(cetpro?.distrito || "-").toUpperCase();
  const lugar = String(cetpro?.lugar || "-").toUpperCase();
  const direccion = String(cetpro?.direccion || "-").toUpperCase();

  doc.setDrawColor(165, 176, 193);
  doc.setLineWidth(0.3);
  doc.line(margin, pageH - 14.5, pageW - margin, pageH - 14.5);

  doc.setTextColor(70, 83, 105);
  doc.setFont("helvetica", "normal");
  doc.setFontSize(7.4);
  doc.text(`Ubicación: ${region} / ${provincia} / ${distrito} / ${lugar}`, margin, pageH - 10);
  doc.text(`Dirección: ${direccion}`, margin, pageH - 6.5);
  doc.text(`Página ${page} de ${totalPages}`, pageW - margin, pageH - 6.5, { align: "right" });
}

function getRowsNotas(especialidad) {
  const rows = [];
  const periodos = Array.isArray(especialidad?.periodos) ? especialidad.periodos : [];

  periodos.forEach((periodo) => {
    const modulos = Array.isArray(periodo?.modulos) ? periodo.modulos : [];
    modulos.forEach((item) => {
      const modulo = `M${item?.modulo?.numero || "-"} - ${item?.modulo?.descripcion || "-"}`;
      const estado = item?.matricula?.matriculado ? "Matriculado" : "Reserva";
      const promedio = item?.promedio_notas ?? "-";
      const unidades = Array.isArray(item?.notas_unidades) ? item.notas_unidades : [];

      if (!unidades.length) {
        rows.push([periodo?.nombre || "-", modulo, "-", "-", String(promedio), estado]);
        return;
      }

      unidades.forEach((u) => {
        rows.push([
          periodo?.nombre || "-",
          modulo,
          `U${u?.numero_unidad || "-"} ${u?.nombre_unidad || ""}`.trim(),
          u?.nota ?? "-",
          String(promedio),
          estado,
        ]);
      });
    });
  });

  return rows;
}

function getRowsAsistencia(especialidad) {
  const rows = [];
  const periodos = Array.isArray(especialidad?.periodos) ? especialidad.periodos : [];

  periodos.forEach((periodo) => {
    const modulos = Array.isArray(periodo?.modulos) ? periodo.modulos : [];
    modulos.forEach((item) => {
      const a = item?.asistencia_resumen || {};
      rows.push([
        periodo?.nombre || "-",
        `M${item?.modulo?.numero || "-"} - ${item?.modulo?.descripcion || "-"}`,
        a?.asistio ?? 0,
        a?.tardanzas ?? 0,
        a?.faltas ?? 0,
        a?.permisos ?? 0,
        a?.porcentaje_asistencia != null ? `${a.porcentaje_asistencia}%` : "-",
      ]);
    });
  });

  return rows;
}

export async function generateReporteEspecialidadEstudiante(estudiante, especialidad) {
  const previewWindow = window.open("", "_blank");

  const cetpro = (await getCetproCached()) || {};
  const doc = new jsPDF({ orientation: "portrait", unit: "mm", format: "a4", compress: true });
  const pageW = doc.internal.pageSize.getWidth();
  const pageH = doc.internal.pageSize.getHeight();
  const margin = 12;
  const colorAccent = [12, 95, 138];
  const colorHeader2 = [30, 58, 95];
  const colorGrid = [186, 198, 213];
  const colorText = [30, 41, 59];

  const nombreEspecialidad = String(especialidad?.nombre || "-").toUpperCase();
  const nombreEstudiante = getNombreCompleto(estudiante).toUpperCase();
  const dni = estudiante?.nro_documento || "-";
  const fechaEmision = `${String(cetpro?.lugar || "PUNO").toUpperCase()}, ${fechaLargaEs()}`;
  const gestion = String(cetpro?.tipo_gestion || "-").toUpperCase();
  const ugel = String(cetpro?.ugel || "-").toUpperCase();
  const dre = String(cetpro?.dre || "-").toUpperCase();

  drawHeader(doc, cetpro, pageW, margin);

  doc.setTextColor(...colorText);
  doc.setFont("helvetica", "normal");
  doc.setFontSize(8.6);
  doc.text(`Gestión: ${gestion} | UGEL: ${ugel} | DRE: ${dre}`, margin, 39.2);

  autoTable(doc, {
    startY: 41.5,
    margin: { left: margin, right: margin, top: 40, bottom: 18 },
    theme: "grid",
    body: [
      ["Especialidad", nombreEspecialidad, "DNI", String(dni)],
      ["Estudiante", nombreEstudiante, "Fecha", fechaEmision],
    ],
    bodyStyles: {
      fontSize: 8.4,
      textColor: colorText,
      lineColor: colorGrid,
      lineWidth: 0.1,
      cellPadding: 1.5,
    },
    columnStyles: {
      0: { cellWidth: 27, fontStyle: "bold" },
      1: { cellWidth: 66 },
      2: { cellWidth: 27, fontStyle: "bold" },
      3: { cellWidth: 66 },
    },
  });

  const notas = getRowsNotas(especialidad);
  const yNotas = (doc.lastAutoTable?.finalY || 50) + 4;
  doc.setTextColor(...colorText);
  doc.setFont("helvetica", "bold");
  doc.setFontSize(10.3);
  doc.text("NOTAS POR UNIDAD DIDÁCTICA", margin, yNotas);
  doc.setDrawColor(...colorGrid);
  doc.line(margin, yNotas + 0.7, pageW - margin, yNotas + 0.7);

  autoTable(doc, {
    startY: yNotas + 2,
    margin: { left: margin, right: margin, top: 40, bottom: 18 },
    head: [["Periodo", "Módulo", "Unidad didáctica", "Nota", "Prom.", "Estado"]],
    body: notas.length ? notas : [["-", "-", "-", "-", "-", "-"]],
    theme: "grid",
    headStyles: {
      fillColor: colorAccent,
      textColor: [255, 255, 255],
      fontStyle: "bold",
      fontSize: 8.4,
      halign: "center",
    },
    bodyStyles: {
      fontSize: 8.1,
      textColor: colorText,
      lineColor: colorGrid,
      lineWidth: 0.1,
      cellPadding: 1.4,
      valign: "middle",
    },
    alternateRowStyles: { fillColor: [248, 250, 252] },
    columnStyles: {
      0: { cellWidth: 24 },
      1: { cellWidth: 50 },
      2: { cellWidth: 60 },
      3: { cellWidth: 16, halign: "center" },
      4: { cellWidth: 16, halign: "center" },
      5: { cellWidth: 20, halign: "center" },
    },
  });

  const asistencia = getRowsAsistencia(especialidad);
  const yAsistencia = (doc.lastAutoTable?.finalY || yNotas + 40) + 6;
  doc.setFont("helvetica", "bold");
  doc.setFontSize(10.3);
  doc.text("RESUMEN DE ASISTENCIA POR MÓDULO", margin, yAsistencia);
  doc.setDrawColor(...colorGrid);
  doc.line(margin, yAsistencia + 0.7, pageW - margin, yAsistencia + 0.7);

  autoTable(doc, {
    startY: yAsistencia + 2,
    margin: { left: margin, right: margin, top: 40, bottom: 18 },
    head: [["Periodo", "Módulo", "Asistió", "Tard.", "Faltas", "Perm.", "% Asist."]],
    body: asistencia.length ? asistencia : [["-", "-", "0", "0", "0", "0", "-"]],
    theme: "grid",
    headStyles: {
      fillColor: colorHeader2,
      textColor: [255, 255, 255],
      fontStyle: "bold",
      fontSize: 8.4,
      halign: "center",
    },
    bodyStyles: {
      fontSize: 8.1,
      textColor: colorText,
      lineColor: colorGrid,
      lineWidth: 0.1,
      cellPadding: 1.4,
      valign: "middle",
    },
    alternateRowStyles: { fillColor: [248, 250, 252] },
    columnStyles: {
      0: { cellWidth: 24 },
      1: { cellWidth: 78 },
      2: { cellWidth: 16, halign: "center" },
      3: { cellWidth: 16, halign: "center" },
      4: { cellWidth: 16, halign: "center" },
      5: { cellWidth: 16, halign: "center" },
      6: { cellWidth: 20, halign: "center" },
    },
  });

  const totalPages = doc.getNumberOfPages();
  for (let p = 1; p <= totalPages; p += 1) {
    doc.setPage(p);
    drawHeader(doc, cetpro, pageW, margin);
    drawFooter(doc, cetpro, pageW, pageH, margin, p, totalPages);
  }

  const blobUrl = URL.createObjectURL(doc.output("blob"));
  if (previewWindow) {
    previewWindow.location.href = blobUrl;
  } else {
    window.open(blobUrl, "_blank");
  }
}
