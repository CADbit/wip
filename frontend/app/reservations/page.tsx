'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import { getResources, getReservationsByResource, createReservation, cancelReservation, type Resource, type Reservation } from '@/lib/api';

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

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
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
      await loadReservations(selectedResource);
    } catch (error) {
      console.error('Błąd tworzenia rezerwacji:', error);
      alert('Nie udało się utworzyć rezerwacji');
    }
  };

  const handleDelete = async (id: string) => {
    if (!confirm('Czy na pewno chcesz anulować tę rezerwację?')) return;
    try {
      await cancelReservation(id);
      await loadReservations(selectedResource);
    } catch (error) {
      console.error('Błąd anulowania:', error);
      alert('Nie udało się anulować rezerwacji');
    }
  };

  const openForm = () => {
    setFormData({
      resourceId: selectedResource,
      reservedBy: '',
      startDate: '',
      endDate: '',
    });
    setShowForm(true);
  };

  if (loading) {
    return (
      <div className="min-h-screen p-8 flex items-center justify-center">
        <div className="text-gray-600">Ładowanie...</div>
      </div>
    );
  }

  return (
    <div className="min-h-screen p-8">
      <div className="max-w-7xl mx-auto">
        <div className="flex justify-between items-center mb-8">
          <h1 className="text-3xl font-bold text-gray-900">Kalendarz Rezerwacji</h1>
          <button
            onClick={openForm}
            disabled={!selectedResource}
            className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
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
          <div className="bg-white rounded-lg shadow p-6 mb-6">
            <h2 className="text-xl font-semibold mb-4">Nowa Rezerwacja</h2>
            <form onSubmit={handleSubmit}>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Rezerwujący *
                  </label>
                  <input
                    type="text"
                    required
                    value={formData.reservedBy}
                    onChange={(e) => setFormData({ ...formData, reservedBy: e.target.value })}
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Data rozpoczęcia *
                  </label>
                  <input
                    type="datetime-local"
                    required
                    value={formData.startDate}
                    onChange={(e) => setFormData({ ...formData, startDate: e.target.value })}
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Data zakończenia *
                  </label>
                  <input
                    type="datetime-local"
                    required
                    value={formData.endDate}
                    onChange={(e) => setFormData({ ...formData, endDate: e.target.value })}
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
              </div>
              <div className="flex gap-4">
                <button
                  type="submit"
                  className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                >
                  Zapisz
                </button>
                <button
                  type="button"
                  onClick={() => setShowForm(false)}
                  className="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors"
                >
                  Anuluj
                </button>
              </div>
            </form>
          </div>
        )}

        {selectedResource && (
          <div className="bg-white rounded-lg shadow overflow-hidden">
            <div className="px-6 py-4 bg-gray-50 border-b border-gray-200">
              <h2 className="text-lg font-semibold text-gray-900">
                Rezerwacje dla: {resources.find(r => r.id === selectedResource)?.name}
              </h2>
            </div>
            {reservations.length === 0 ? (
              <div className="px-6 py-8 text-center text-gray-500">
                Brak rezerwacji dla tej sali
              </div>
            ) : (
              <div className="overflow-x-auto">
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
            )}
          </div>
        )}

        <div className="mt-4">
          <Link
            href="/"
            className="text-blue-600 hover:text-blue-800"
          >
            ← Powrót do głównej
          </Link>
        </div>
      </div>
    </div>
  );
}

