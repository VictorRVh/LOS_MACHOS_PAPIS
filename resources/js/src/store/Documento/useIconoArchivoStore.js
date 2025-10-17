
export function useIconoArchivo() {
  const iconoArchivo = (mimeType) => {
    if (!mimeType) return '/img/iconosArchivo/generico.png'

    mimeType = mimeType.toLowerCase()

    // Documentos de Word
    if (mimeType.includes('word') || mimeType.includes('officedocument.wordprocessingml')) {
      return '/img/iconosArchivo/word.png'
    }

    // PDF
    if (mimeType.includes('pdf')) {
      return '/img/iconosArchivo/pdf.png'
    }

    // Excel o Hojas de cálculo
    if (mimeType.includes('excel') || mimeType.includes('spreadsheet') || mimeType.includes('csv')) {
      return '/img/iconosArchivo/excel.png'
    }

    // PowerPoint
    if (mimeType.includes('presentation') || mimeType.includes('powerpoint')) {
      return '/img/iconosArchivo/power_point.png'
    }

    // Imágenes (png, jpg, jpeg, gif, webp)
    if (mimeType.includes('image')) {
      return '/img/iconosArchivo/imagen.png'
    }

    // Videos (mp4, avi, mov, mkv, webm)
    if (mimeType.includes('video')) {
      return '/img/iconosArchivo/video.png'
    }

    // Archivos comprimidos (zip, rar)
    if (mimeType.includes('zip') || mimeType.includes('rar')) {
      return '/img/iconosArchivo/winrar.png'
    }

    // Si no coincide con ninguno de los anteriores
    return '/img/iconosArchivo/generico.png'
  }

  return { iconoArchivo }
}