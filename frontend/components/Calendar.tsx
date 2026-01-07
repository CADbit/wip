'use client';

import { useState, useMemo, useEffect } from 'react';
import { Calendar as BigCalendar, momentLocalizer, View, ToolbarProps } from 'react-big-calendar';
import moment from 'moment';
import 'moment/locale/pl';
import 'react-big-calendar/lib/css/react-big-calendar.css';
import { Reservation } from '@/lib/api';

// Ustawienie polskiej lokalizacji dla moment.js
moment.locale('pl');

// Lokalizator dla react-big-calendar
const localizer = momentLocalizer(moment);

interface CalendarEvent {
  id: string;
  title: string;
  start: Date;
  end: Date;
  resource: {
    id: string;
    name: string;
    reservedBy: string;
  };
}

interface CalendarProps {
  reservations: Reservation[];
}

export default function Calendar({ reservations }: CalendarProps) {
  // Ustaw początkowy widok na podstawie rozmiaru ekranu
  const getInitialView = (): View => {
    if (typeof window !== 'undefined') {
      return window.innerWidth < 1024 ? 'day' : 'week';
    }
    return 'week';
  };

  const [currentView, setCurrentView] = useState<View>(getInitialView);
  const [currentDate, setCurrentDate] = useState(new Date());
  const [calendarHeight, setCalendarHeight] = useState(600);

  useEffect(() => {
    const updateLayout = () => {
      const isMobile = window.innerWidth < 1024;
      setCalendarHeight(isMobile ? 400 : 600);
      // Automatycznie ustaw widok na 'day' dla mobile, 'week' dla desktop
      setCurrentView(prevView => {
        if (isMobile && prevView !== 'day') {
          return 'day';
        } else if (!isMobile && prevView === 'day') {
          return 'week';
        }
        return prevView;
      });
    };
    
    updateLayout();
    window.addEventListener('resize', updateLayout);
    return () => window.removeEventListener('resize', updateLayout);
  }, []);

  const events: CalendarEvent[] = useMemo(() => {
    return reservations.map((reservation) => ({
      id: reservation.id,
      title: `${reservation.resourceName} - ${reservation.reservedBy}`,
      start: new Date(reservation.startDate),
      end: new Date(reservation.endDate),
      resource: {
        id: reservation.resourceId,
        name: reservation.resourceName,
        reservedBy: reservation.reservedBy,
      },
    }));
  }, [reservations]);

  const handleSelectEvent = (event: CalendarEvent) => {
    alert(
      `Rezerwacja:\n\n` +
      `Sala: ${event.resource.name}\n` +
      `Rezerwujący: ${event.resource.reservedBy}\n` +
      `Od: ${moment(event.start).format('DD.MM.YYYY HH:mm')}\n` +
      `Do: ${moment(event.end).format('DD.MM.YYYY HH:mm')}`
    );
  };

  const CustomToolbar = (props: ToolbarProps<CalendarEvent, object>) => {
    const { label, onNavigate, onView, view } = props;
    return (
      <div className="mb-4 space-y-3 desktop:space-y-[10px]">
        {/* Mobile: Title i Navigation */}
        <div className="flex flex-col desktop:flex-row desktop:items-center desktop:justify-between gap-3 desktop:gap-0">
          <h2 className="text-lg desktop:text-xl font-semibold text-gray-800 text-center desktop:text-left">{label}</h2>
          <div className="flex items-center justify-center desktop:justify-start gap-2">
            <button
              onClick={() => onNavigate('PREV')}
              className="px-2 desktop:px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded text-gray-700 text-sm desktop:text-base"
            >
              ← Poprzedni
            </button>
            <button
              onClick={() => onNavigate('TODAY')}
              className="px-2 desktop:px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-sm desktop:text-base"
            >
              Dzisiaj
            </button>
            <button
              onClick={() => onNavigate('NEXT')}
              className="px-2 desktop:px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded text-gray-700 text-sm desktop:text-base"
            >
              Następny →
            </button>
          </div>
        </div>
        {/* View buttons */}
        <div className="flex gap-2 justify-center desktop:justify-end">
          <button
            onClick={() => onView('day')}
            className={`px-2 desktop:px-3 py-1 rounded text-sm desktop:text-base ${
              view === 'day'
                ? 'bg-blue-600 text-white'
                : 'bg-gray-200 hover:bg-gray-300 text-gray-700'
            }`}
          >
            Dzień
          </button>
          <button
            onClick={() => onView('week')}
            className={`px-2 desktop:px-3 py-1 rounded text-sm desktop:text-base ${
              view === 'week'
                ? 'bg-blue-600 text-white'
                : 'bg-gray-200 hover:bg-gray-300 text-gray-700'
            }`}
          >
            Tydzień
          </button>
          <button
            onClick={() => onView('month')}
            className={`px-2 desktop:px-3 py-1 rounded text-sm desktop:text-base ${
              view === 'month'
                ? 'bg-blue-600 text-white'
                : 'bg-gray-200 hover:bg-gray-300 text-gray-700'
            }`}
          >
            Miesiąc
          </button>
        </div>
      </div>
    );
  };

  const eventStyleGetter = (event: CalendarEvent) => {
    const colors = [
      '#3174ad',
      '#7cb342',
      '#f57c00',
      '#7b1fa2',
      '#c2185b',
      '#0097a7',
    ];
    const colorIndex = event.resource.id.charCodeAt(0) % colors.length;
    const backgroundColor = colors[colorIndex];

    return {
      style: {
        backgroundColor,
        borderRadius: '4px',
        opacity: 0.8,
        color: 'white',
        border: '0px',
        display: 'block',
        padding: '2px 4px',
      },
    };
  };

  return (
    <div className="bg-white rounded-lg shadow p-3 desktop:p-6">
      <BigCalendar
        localizer={localizer}
        events={events}
        startAccessor="start"
        endAccessor="end"
        view={currentView}
        onView={setCurrentView}
        date={currentDate}
        onNavigate={setCurrentDate}
        onSelectEvent={handleSelectEvent}
        components={{
          toolbar: CustomToolbar as any,
        }}
        eventPropGetter={eventStyleGetter}
        messages={{
          next: 'Następny',
          previous: 'Poprzedni',
          today: 'Dzisiaj',
          month: 'Miesiąc',
          week: 'Tydzień',
          day: 'Dzień',
          agenda: 'Agenda',
          date: 'Data',
          time: 'Czas',
          event: 'Wydarzenie',
          noEventsInRange: 'Brak rezerwacji w tym okresie',
        }}
        style={{ height: calendarHeight }}
      />
    </div>
  );
}

