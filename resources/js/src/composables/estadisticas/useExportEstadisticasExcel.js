import useHttpRequest from "../useHttpRequest";

const { index: getCetproData } = useHttpRequest("/cetprodata");

const colLetter = (n) => {
  let s = "";
  while (n > 0) {
    const m = (n - 1) % 26;
    s = String.fromCharCode(65 + m) + s;
    n = Math.floor((n - 1) / 26);
  }
  return s;
};

const applyThinBorder = (cell) => {
  cell.border = {
    top: { style: "thin", color: { argb: "FFC8CDD4" } },
    left: { style: "thin", color: { argb: "FFC8CDD4" } },
    bottom: { style: "thin", color: { argb: "FFC8CDD4" } },
    right: { style: "thin", color: { argb: "FFC8CDD4" } },
  };
};

const applyRangeBorder = (ws, startCell, endCell) => {
  const start = ws.getCell(startCell);
  const end = ws.getCell(endCell);
  for (let r = start.row; r <= end.row; r++) {
    for (let c = start.col; c <= end.col; c++) {
      applyThinBorder(ws.getCell(r, c));
    }
  }
};

const fetchImageBase64 = async (url) => {
  const response = await fetch(url);
  if (!response.ok) return null;
  const blob = await response.blob();
  return await new Promise((resolve) => {
    const reader = new FileReader();
    reader.onloadend = () => resolve(reader.result);
    reader.onerror = () => resolve(null);
    reader.readAsDataURL(blob);
  });
};

const addInstitutionalHeader = async (workbook, ws, opts) => {
  const {
    reportTitle,
    fechaInicio,
    fechaFin,
    totalCols,
    logoCols = 2,
  } = opts;

  const cetpro = await getCetproData();
  const rawName = String(cetpro?.cetpro || "PUNO").trim().toUpperCase();
  const cetproName = rawName.startsWith("CETPRO ") ? rawName : `CETPRO ${rawName}`;

  const endCol = colLetter(totalCols);
  const leftCols = Math.max(1, totalCols - logoCols);
  const leftEnd = colLetter(leftCols);
  const logoStart = colLetter(leftCols + 1);

  ws.mergeCells(`A1:${endCol}1`);
  const title = ws.getCell("A1");
  title.value = reportTitle;
  title.font = { bold: true, size: 16, color: { argb: "FFFFFFFF" } };
  title.alignment = { horizontal: "center", vertical: "middle" };
  title.fill = { type: "pattern", pattern: "solid", fgColor: { argb: "FF0F2747" } };

  ws.mergeCells(`A2:${endCol}2`);
  const subtitle = ws.getCell("A2");
  subtitle.value = `${rawName} | Fecha inicio: ${fechaInicio || "-"} | Fecha fin: ${fechaFin || "-"} | Generado: ${new Date().toLocaleString("es-PE")}`;
  subtitle.font = { size: 10, color: { argb: "FF334155" } };
  subtitle.alignment = { horizontal: "center", vertical: "middle" };
  subtitle.fill = { type: "pattern", pattern: "solid", fgColor: { argb: "FFE2E8F0" } };

  ws.mergeCells(`A3:${leftEnd}3`);
  ws.mergeCells(`A4:${leftEnd}4`);
  ws.mergeCells(`A5:${leftEnd}5`);

  const row3 = ws.getCell("A3");
  row3.value = `Emitido por: ${cetproName}`;
  row3.font = { bold: true, size: 10.5, color: { argb: "FF0F172A" } };
  row3.alignment = { horizontal: "left", vertical: "middle" };
  row3.fill = { type: "pattern", pattern: "solid", fgColor: { argb: "FFF8FAFC" } };

  const row4 = ws.getCell("A4");
  row4.value = `Tipo de gestión: ${cetpro?.tipo_gestion || "-"} | UGEL: ${cetpro?.ugel || "-"} | DRE: ${cetpro?.dre || "-"}`;
  row4.font = { size: 10, color: { argb: "FF334155" } };
  row4.alignment = { horizontal: "left", vertical: "middle" };
  row4.fill = { type: "pattern", pattern: "solid", fgColor: { argb: "FFF8FAFC" } };

  const row5 = ws.getCell("A5");
  row5.value = `Ubicación: ${cetpro?.region || "-"} / ${cetpro?.provincia || "-"} / ${cetpro?.distrito || "-"} | Dirección: ${cetpro?.direccion || "-"} | Año: ${cetpro?.anio || "-"}`;
  row5.font = { size: 10, color: { argb: "FF334155" } };
  row5.alignment = { horizontal: "left", vertical: "middle" };
  row5.fill = { type: "pattern", pattern: "solid", fgColor: { argb: "FFF8FAFC" } };

  applyRangeBorder(ws, "A3", `${leftEnd}3`);
  applyRangeBorder(ws, "A4", `${leftEnd}4`);
  applyRangeBorder(ws, "A5", `${leftEnd}5`);

  ws.mergeCells(`${logoStart}3:${endCol}5`);
  const logoBox = ws.getCell(`${logoStart}3`);
  logoBox.value = "";
  logoBox.fill = { type: "pattern", pattern: "solid", fgColor: { argb: "FFF8FAFC" } };
  logoBox.alignment = { horizontal: "center", vertical: "middle" };
  applyRangeBorder(ws, `${logoStart}3`, `${endCol}5`);

  ws.getRow(1).height = 28;
  ws.getRow(2).height = 20;
  ws.getRow(3).height = 24;
  ws.getRow(4).height = 22;
  ws.getRow(5).height = 22;

  const logoBase64 = await fetchImageBase64("/img/CETPRO_Image.png");
  if (logoBase64) {
    const logoId = workbook.addImage({ base64: logoBase64, extension: "png" });
    const logoStartCol = leftCols + (logoCols === 1 ? 0.1 : 0.45);
    const logoWidth = logoCols === 1 ? 52 : 72;
    ws.addImage(logoId, {
      tl: { col: logoStartCol, row: 2.12 },
      ext: { width: logoWidth, height: 80 },
    });
  }

  return {
    cetpro,
    startRow: 7,
    endColLetter: endCol,
    applyThinBorder: (cell) => applyThinBorder(cell),
    applyRangeBorder: (start, end) => applyRangeBorder(ws, start, end),
  };
};

export default function useExportEstadisticasExcel() {
  return {
    addInstitutionalHeader,
  };
}

