'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import { getResources, getReservationsByResource, getReservationsByResourceAndDate, createReservation, cancelReservation, type Resource, type Reservation, ApiException } from '@/lib/api';
import DateTimePicker from '@/components/DateTimePicker';

export default function ReservationsPage() {
  const [resources, setResources] = useState<Resource[]>([]);
  const [selectedResource, setSelectedResource] = useState<string>('');
  const [reservations, setReservations] = useState<Reservation[]>([]);
  const [loading, setLoading] = useState(true);
  const [showForm, setShowForm] = useState(false);
  const [formData, setFormData] = useState({
    resourceId: '',
    reservedBy: '',
    startDate: '',
    endDate: '',
  });
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [errorMessage, setErrorMessage] = useState<string>('');
  const [dayReservations, setDayReservations] = useState<Reservation[]>([]);
  const [loadingDayReservations, setLoadingDayReservations] = useState(false);

  useEffect(() => {
    loadResources();
  }, []);

  useEffect(() => {
    if (selectedResource) {
      loadReservations(selectedResource);
    } else {
      setReservations([]);
    }
  }, [selectedResource]);

  const loadResources = async () => {
    try {
      const data = await getResources();
      setResources(data);
      if (data.length > 0 && !selectedResource) {
        setSelectedResource(data[0].id);
      }
    } catch (error) {
      console.error('Błąd ładowania zasobów:', error);
    } finally {
      setLoading(false);
    }
  };

  const loadReservations = async (resourceId: string) => {
    try {
      const data = await getReservationsByResource(resourceId);
      setReservations(data);
    } catch (error) {
      console.error('Błąd ładowania rezerwacji:', error);
    }
  };

  const loadDayReservations = async (resourceId: string, startDateString?: string, endDateString?: string) => {
    if (!resourceId) {
      setDayReservations([]);
      return;
    }

    // Jeśli mamy obie daty, pobierz rezerwacje dla wszystkich dni w zakresie
    const datesToCheck: string[] = [];
    
    if (startDateString) {
      const startDate = startDateString.split('T')[0];
      if (startDate) datesToCheck.push(startDate);
    }
    
    if (endDateString) {
      const endDate = endDateString.split('T')[0];
      if (endDate && !datesToCheck.includes(endDate)) {
        datesToCheck.push(endDate);
      }
    }

    // Jeśli nie ma dat, wyczyść
    if (datesToCheck.length === 0) {
      setDayReservations([]);
      return;
    }

    setLoadingDayReservations(true);
    try {
      // Pobierz rezerwacje dla wszystkich dni i połącz je
      const allReservations: Reservation[] = [];
      for (const date of datesToCheck) {
        const data = await getReservationsByResourceAndDate(resourceId, date);
        // Usuń duplikaty (rezerwacje mogą być w wielu dniach)
        data.forEach(reservation => {
          if (!allReservations.find(r => r.id === reservation.id)) {
            allReservations.push(reservation);
          }
        });
      }
      setDayReservations(allReservations);
    } catch (error) {
      console.error('Błąd ładowania rezerwacji dla dnia:', error);
      setDayReservations([]);
    } finally {
      setLoadingDayReservations(false);
    }
  };

  const isTimeSlotOccupied = (dateTimeString: string, isStart: boolean): boolean => {
    if (!dateTimeString || dayReservations.length === 0) return false;
    
    const selectedDate = new Date(dateTimeString);
    
    return dayReservations.some(reservation => {
      const start = new Date(reservation.startDate);
      const end = new Date(reservation.endDate);
      
      if (isStart) {
        // Sprawdź czy wybrana data rozpoczęcia koliduje z istniejącą rezerwacją
        return selectedDate >= start && selectedDate < end;
      } else {
        // Sprawdź czy wybrana data zakończenia koliduje z istniejącą rezerwacją
        return selectedDate > start && selectedDate <= end;
      }
    });
  };

  const isTimeRangeOccupied = (startDateString: string, endDateString: string): boolean => {
    if (!startDateString || !endDateString || dayReservations.length === 0) return false;
    
    const selectedStart = new Date(startDateString);
    const selectedEnd = new Date(endDateString);
    
    // Sprawdź czy wybrany zakres koliduje z jakąkolwiek rezerwacją
    return dayReservations.some(reservation => {
      const start = new Date(reservation.startDate);
      const end = new Date(reservation.endDate);
      
      // Sprawdź czy zakresy się nakładają: selectedStart < end && selectedEnd > start
      return selectedStart < end && selectedEnd > start;
    });
  };

  const getOccupiedTimeSlots = (): string => {
    if (dayReservations.length === 0) return '';
    
    return dayReservations.map(r => {
      const start = new Date(r.startDate);
      const end = new Date(r.endDate);
      return `${start.toLocaleTimeString('pl-PL', { hour: '2-digit', minute: '2-digit' })} - ${end.toLocaleTimeString('pl-PL', { hour: '2-digit', minute: '2-digit' })} (${r.reservedBy})`;
    }).join(', ');
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrors({});
    setErrorMessage('');
    
    try {
      // Konwersja formatu daty z datetime-local (YYYY-MM-DDTHH:mm) na format backendu (YYYY-MM-DD HH:mm:ss)
      const formatDateForBackend = (dateString: string) => {
        return dateString.replace('T', ' ') + ':00';
      };
      
      await createReservation({
        resourceId: formData.resourceId,
        reservedBy: formData.reservedBy,
        startDate: formatDateForBackend(formData.startDate),
        endDate: formatDateForBackend(formData.endDate),
      });
      setShowForm(false);
      setFormData({
        resourceId: selectedResource,
        reservedBy: '',
        startDate: '',
        endDate: '',
      });
      setErrors({});
      setErrorMessage('');
      await loadReservations(selectedResource);
    } catch (error) {
      console.error('Błąd tworzenia rezerwacji:', error);
      if (error instanceof ApiException) {
        setErrorMessage(error.message);
        if (error.errors) {
          const fieldErrors: Record<string, string> = {};
          Object.entries(error.errors).forEach(([key, value]) => {
            if (key !== 'conflicts') {
              fieldErrors[key] = Array.isArray(value) ? value[0] : value;
            }
          });
          setErrors(fieldErrors);
          
          // Jeśli są konflikty, wyświetl je w szczegółowej wiadomości
          if (error.errors.conflicts) {
            const conflicts = Array.isArray(error.errors.conflicts) 
              ? error.errors.conflicts 
              : [error.errors.conflicts];
            const conflictsText = conflicts.map((c: any) => 
              `${c.reservedBy}: ${new Date(c.startDate).toLocaleString('pl-PL')} - ${new Date(c.endDate).toLocaleString('pl-PL')}`
            ).join('\n');
            setErrorMessage(`${error.message}\n\nKonfliktujące rezerwacje:\n${conflictsText}`);
          }
        } else {
          setErrorMessage(error.message);
        }
      } else {
        setErrorMessage('Nie udało się utworzyć rezerwacji. Spróbuj ponownie.');
      }
    }
  };

  const handleDelete = async (id: string) => {
    if (!confirm('Czy na pewno chcesz anulować tę rezerwację?')) return;
    try {
      await cancelReservation(id);
      await loadReservations(selectedResource);
    } catch (error) {
      console.error('Błąd anulowania:', error);
      if (error instanceof ApiException) {
        alert(`Błąd: ${error.message}`);
      } else {
        alert('Nie udało się anulować rezerwacji');
      }
    }
  };

  const openForm = () => {
    setFormData({
      resourceId: selectedResource,
      reservedBy: '',
      startDate: '',
      endDate: '',
    });
    setDayReservations([]);
    setShowForm(true);
  };

  if (loading) {
    return (
      <div className="min-h-screen p-4 desktop:p-8 flex items-center justify-center">
        <div className="text-gray-600">Ładowanie...</div>
      </div>
    );
  }

  return (
    <div className="min-h-screen p-4 desktop:p-8">
      <div className="max-w-7xl mx-auto">
        <div className="flex flex-col desktop:flex-row desktop:justify-between desktop:items-center gap-4 desktop:gap-0 mb-6 desktop:mb-8">
          <h1 className="text-2xl desktop:text-3xl font-bold text-gray-900">Kalendarz Rezerwacji</h1>
          <button
            onClick={openForm}
            disabled={!selectedResource}
            className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors w-full desktop:w-auto"
          >
            + Dodaj Rezerwację
          </button>
        </div>

        <div className="mb-6">
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Wybierz Salę
          </label>
          <select
            value={selectedResource}
            onChange={(e) => setSelectedResource(e.target.value)}
            className="w-full md:w-64 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">-- Wybierz salę --</option>
            {resources.map((resource) => (
              <option key={resource.id} value={resource.id}>
                {resource.name}
              </option>
            ))}
          </select>
        </div>

        {showForm && (
          <div className="bg-white rounded-lg shadow p-4 desktop:p-6 mb-6">
            <h2 className="text-lg desktop:text-xl font-semibold mb-4">Nowa Rezerwacja</h2>
            {errorMessage && (
              <div className="mb-4 p-3 desktop:p-4 bg-red-50 border border-red-200 rounded-md">
                <div className="text-xs desktop:text-sm text-red-800 whitespace-pre-line">{errorMessage}</div>
              </div>
            )}
            <form onSubmit={handleSubmit}>
              <div className="grid grid-cols-1 desktop:grid-cols-2 gap-4 mb-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Rezerwujący *
                  </label>
                  <input
                    type="text"
                    required
                    value={formData.reservedBy}
                    onChange={(e) => {
                      setFormData({ ...formData, reservedBy: e.target.value });
                      if (errors.reservedBy) {
                        setErrors({ ...errors, reservedBy: '' });
                      }
                    }}
                    className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 ${
                      errors.reservedBy 
                        ? 'border-red-300 focus:ring-red-500' 
                        : 'border-gray-300 focus:ring-blue-500'
                    }`}
                  />
                  {errors.reservedBy && (
                    <p className="mt-1 text-sm text-red-600">{errors.reservedBy}</p>
                  )}
                </div>
                <div>
                  <DateTimePicker
                    label="Data rozpoczęcia *"
                    value={formData.startDate}
                    onChange={async (newStartDate) => {
                      setFormData({ ...formData, startDate: newStartDate });
                      if (errors.startDate) {
                        setErrors({ ...errors, startDate: '' });
                      }
                      // Pobierz rezerwacje dla wybranego zakresu dat
                      if (formData.resourceId) {
                        await loadDayReservations(formData.resourceId, newStartDate, formData.endDate);
                      }
                    }}
                    reservations={dayReservations}
                    error={errors.startDate}
                    timeStep={30}
                  />
                  {loadingDayReservations && (
                    <p className="mt-1 text-xs text-gray-500">Sprawdzanie dostępności...</p>
                  )}
                  {formData.startDate && formData.endDate && isTimeRangeOccupied(formData.startDate, formData.endDate) && (
                    <p className="mt-1 text-sm text-red-600">
                      ⚠️ Wybrany zakres czasu koliduje z istniejącą rezerwacją!
                    </p>
                  )}
                </div>
                <div>
                  <DateTimePicker
                    label="Data zakończenia *"
                    value={formData.endDate}
                    onChange={async (newEndDate) => {
                      setFormData({ ...formData, endDate: newEndDate });
                      if (errors.endDate) {
                        setErrors({ ...errors, endDate: '' });
                      }
                      // Pobierz rezerwacje dla wybranego zakresu dat
                      if (formData.resourceId) {
                        await loadDayReservations(formData.resourceId, formData.startDate, newEndDate);
                      }
                    }}
                    reservations={dayReservations}
                    min={formData.startDate || undefined}
                    error={errors.endDate}
                    timeStep={30}
                  />
                </div>
              </div>
              <div className="flex flex-col desktop:flex-row gap-3 desktop:gap-4">
                <button
                  type="submit"
                  className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors w-full desktop:w-auto"
                >
                  Zapisz
                </button>
                <button
                  type="button"
                  onClick={() => {
                    setShowForm(false);
                    setErrors({});
                    setErrorMessage('');
                    setDayReservations([]);
                  }}
                  className="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors w-full desktop:w-auto"
                >
                  Anuluj
                </button>
              </div>
            </form>
          </div>
        )}

        {selectedResource && (
          <div className="bg-white rounded-lg shadow overflow-hidden">
            <div className="px-4 desktop:px-6 py-3 desktop:py-4 bg-gray-50 border-b border-gray-200">
              <h2 className="text-base desktop:text-lg font-semibold text-gray-900">
                Rezerwacje dla: {resources.find(r => r.id === selectedResource)?.name}
              </h2>
            </div>
            {reservations.length === 0 ? (
              <div className="px-4 desktop:px-6 py-6 desktop:py-8 text-center text-gray-500 text-sm desktop:text-base">
                Brak rezerwacji dla tej sali
              </div>
            ) : (
              <>
                {/* Mobile: Karty */}
                <div className="desktop:hidden divide-y divide-gray-200">
                  {reservations.map((reservation) => (
                    <div key={reservation.id} className="p-4">
                      <div className="mb-3">
                        <div className="text-sm font-medium text-gray-900 mb-1">
                          {reservation.reservedBy}
                        </div>
                        <div className="text-xs text-gray-500 space-y-1">
                          <div>Od: {new Date(reservation.startDate).toLocaleString('pl-PL')}</div>
                          <div>Do: {new Date(reservation.endDate).toLocaleString('pl-PL')}</div>
                        </div>
                      </div>
                      <button
                        onClick={() => handleDelete(reservation.id)}
                        className="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm"
                      >
                        Anuluj
                      </button>
                    </div>
                  ))}
                </div>

                {/* Desktop: Tabela */}
                <div className="hidden desktop:block overflow-x-auto">
                  <table className="min-w-full divide-y divide-gray-200">
                    <thead className="bg-gray-50">
                      <tr>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Rezerwujący
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Data rozpoczęcia
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Data zakończenia
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Akcje
                        </th>
                      </tr>
                    </thead>
                    <tbody className="bg-white divide-y divide-gray-200">
                      {reservations.map((reservation) => (
                        <tr key={reservation.id} className="hover:bg-gray-50">
                          <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {reservation.reservedBy}
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {new Date(reservation.startDate).toLocaleString('pl-PL')}
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {new Date(reservation.endDate).toLocaleString('pl-PL')}
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button
                              onClick={() => handleDelete(reservation.id)}
                              className="text-red-600 hover:text-red-900"
                            >
                              Anuluj
                            </button>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </>
            )}
          </div>
        )}

        <div className="mt-6 desktop:mt-4">
          <Link
            href="/"
            className="text-blue-600 hover:text-blue-800 text-sm desktop:text-base"
          >
            ← Powrót do głównej
          </Link>
        </div>
      </div>
    </div>
  );
}

