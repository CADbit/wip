'use client';

import { useState, useEffect, useMemo } from 'react';
import { Reservation } from '@/lib/api';

interface DateTimePickerProps {
  label: string;
  value: string; // format: YYYY-MM-DDTHH:mm
  onChange: (value: string) => void;
  reservations: Reservation[];
  min?: string; // format: YYYY-MM-DDTHH:mm
  error?: string;
  timeStep?: number; // krok w minutach (domyślnie 30)
}

export default function DateTimePicker({
  label,
  value,
  onChange,
  reservations,
  min,
  error,
  timeStep = 30,
}: DateTimePickerProps) {
  const [date, setDate] = useState(value ? value.split('T')[0] : '');
  const [time, setTime] = useState(value ? value.split('T')[1] : '');

  useEffect(() => {
    if (value) {
      const parts = value.split('T');
      setDate(parts[0] || '');
      setTime(parts[1] || '');
    } else {
      setDate('');
      setTime('');
    }
  }, [value]);

  // Generuj dostępne godziny (co timeStep minut)
  const availableHours = useMemo(() => {
    const hours: string[] = [];
    for (let h = 0; h < 24; h++) {
      for (let m = 0; m < 60; m += timeStep) {
        const hour = String(h).padStart(2, '0');
        const minute = String(m).padStart(2, '0');
        hours.push(`${hour}:${minute}`);
      }
    }
    return hours;
  }, [timeStep]);

  // Sprawdź czy dana godzina jest zajęta
  const isTimeSlotOccupied = (dateStr: string, timeStr: string): boolean => {
    if (!dateStr || !timeStr || reservations.length === 0) return false;

    const selectedDateTime = new Date(`${dateStr}T${timeStr}`);
    
    return reservations.some((reservation) => {
      const start = new Date(reservation.startDate);
      const end = new Date(reservation.endDate);
      
      // Sprawdź czy wybrana data i godzina koliduje z rezerwacją
      return selectedDateTime >= start && selectedDateTime < end;
    });
  };

  // Sprawdź czy zakres czasu jest zajęty (dla walidacji)
  const isTimeRangeOccupied = (dateStr: string, timeStr: string, durationMinutes: number = 60): boolean => {
    if (!dateStr || !timeStr || reservations.length === 0) return false;

    const startDateTime = new Date(`${dateStr}T${timeStr}`);
    const endDateTime = new Date(startDateTime.getTime() + durationMinutes * 60000);
    
    return reservations.some((reservation) => {
      const resStart = new Date(reservation.startDate);
      const resEnd = new Date(reservation.endDate);
      
      // Sprawdź czy zakresy się nakładają
      return startDateTime < resEnd && endDateTime > resStart;
    });
  };

  const handleDateChange = (newDate: string) => {
    setDate(newDate);
    const newValue = newDate && time ? `${newDate}T${time}` : newDate;
    onChange(newValue);
  };

  const handleTimeChange = (newTime: string) => {
    setTime(newTime);
    const newValue = date && newTime ? `${date}T${newTime}` : '';
    onChange(newValue);
  };

  // Filtruj dostępne godziny na podstawie min (jeśli jest ustawione)
  const filteredHours = useMemo(() => {
    if (!min || !date) return availableHours;
    
    const minDate = min.split('T')[0];
    const minTime = min.split('T')[1];
    
    if (minDate === date && minTime) {
      return availableHours.filter((hour) => hour >= minTime);
    }
    
    return availableHours;
  }, [availableHours, min, date]);

  return (
    <div>
      <label className="block text-sm font-medium text-gray-700 mb-2">
        {label}
      </label>
      
      <div className="grid grid-cols-1 desktop:grid-cols-2 gap-4">
        {/* Date picker */}
        <div>
          <input
            type="date"
            value={date}
            onChange={(e) => handleDateChange(e.target.value)}
            min={min ? min.split('T')[0] : undefined}
            className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 ${
              error
                ? 'border-red-300 focus:ring-red-500' 
                : 'border-gray-300 focus:ring-blue-500'
            }`}
          />
        </div>

        {/* Time picker */}
        <div>
          <select
            value={time}
            onChange={(e) => handleTimeChange(e.target.value)}
            disabled={!date}
            className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 ${
              error
                ? 'border-red-300 focus:ring-red-500' 
                : 'border-gray-300 focus:ring-blue-500'
            } ${!date ? 'bg-gray-100 cursor-not-allowed' : ''}`}
          >
            <option value="">-- Wybierz godzinę --</option>
            {filteredHours.map((hour) => {
              const isOccupied = date ? isTimeSlotOccupied(date, hour) : false;
              return (
                <option
                  key={hour}
                  value={hour}
                  disabled={isOccupied}
                  style={isOccupied ? { 
                    color: '#9ca3af', 
                    backgroundColor: '#f3f4f6',
                    textDecoration: 'line-through'
                  } : {}}
                >
                  {hour} {isOccupied ? '🔒 zajęte' : ''}
                </option>
              );
            })}
          </select>
        </div>
      </div>

      {error && (
        <p className="mt-1 text-sm text-red-600">{error}</p>
      )}

      {/* Pokaż zajęte godziny dla wybranego dnia */}
      {date && reservations.length > 0 && (
        <div className="mt-2 p-2 bg-yellow-50 border border-yellow-200 rounded-md">
          <p className="text-xs font-medium text-yellow-800 mb-1">
            ⚠️ Zajęte godziny w tym dniu (nie można wybrać):
          </p>
          <div className="flex flex-wrap gap-1">
            {reservations
              .filter((r) => {
                const resDate = new Date(r.startDate).toISOString().split('T')[0];
                return resDate === date;
              })
              .map((r, idx) => {
                const start = new Date(r.startDate);
                const end = new Date(r.endDate);
                const startTime = start.toLocaleTimeString('pl-PL', { hour: '2-digit', minute: '2-digit' });
                const endTime = end.toLocaleTimeString('pl-PL', { hour: '2-digit', minute: '2-digit' });
                return (
                  <span key={idx} className="text-xs text-yellow-800 bg-yellow-200 px-2 py-1 rounded font-medium">
                    {startTime} - {endTime} ({r.reservedBy})
                  </span>
                );
              })}
          </div>
        </div>
      )}
    </div>
  );
}

