export default function useCalendarEvents(sesiones) {
  const buildAllEvents = () => {
    return sesiones.value.flatMap(sesion =>
      (sesion.calendario_admin || []).map(dia => ({
        id: `${sesion.id}-${dia.fecha}`,
        title: sesion.nombre_sesion,
        start: dia.fecha,
        allDay: true,
        backgroundColor: '#3B82F6', // azul
        borderColor: '#2563EB',
      }))
    );
  };

  return { buildAllEvents };
}
