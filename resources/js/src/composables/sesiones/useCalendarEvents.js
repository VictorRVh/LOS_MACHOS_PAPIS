export default function useCalendarEvents(bloquesDeSesiones, selectionEvents, holidays) {
  const buildAllEvents = () => {
    const holidayEvents = holidays.value.map(h => ({
      title: h.title,
      start: h.date,
      allDay: true,
      color: '#6b7280',
      classNames: ['gcal-event']
    }));

    const sessionBlockEvents = bloquesDeSesiones.value.flatMap(bloque =>
      (bloque.dates || []).map(dateStr => ({
        id: `${bloque.id}::${dateStr}`,
        title: bloque.title,
        start: dateStr,
        allDay: true,
        backgroundColor: bloque.color,
      }))
    );

    const selectionTemp = selectionEvents.value.map(e => ({
      ...e,
      id: `temp-${e.start}`,
      title: ''
    }));

    return [...sessionBlockEvents, ...selectionTemp, ...holidayEvents];
  };

  return { buildAllEvents };
}
