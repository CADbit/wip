'use client';

import { useEffect, useState } from 'react';
import Link from 'next/link';
import { getResources, deleteResource, type Resource, ApiException } from '@/lib/api';

export default function ResourcesPage() {
  const [resources, setResources] = useState<Resource[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadResources();
  }, []);

  const loadResources = async () => {
    try {
      const data = await getResources();
      setResources(data);
    } catch (error) {
      console.error('Błąd ładowania zasobów:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async (id: string) => {
    if (!confirm('Czy na pewno chcesz usunąć tę salę?')) return;
    try {
      await deleteResource(id);
      await loadResources();
    } catch (error) {
      console.error('Błąd usuwania:', error);
      if (error instanceof ApiException) {
        alert(`Błąd: ${error.message}`);
      } else {
        alert('Nie udało się usunąć sali');
      }
    }
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
          <h1 className="text-2xl desktop:text-3xl font-bold text-gray-900">Sale Konferencyjne</h1>
          <Link
            href="/resources/new"
            className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-center desktop:text-left w-full desktop:w-auto"
          >
            + Dodaj Salę
          </Link>
        </div>

        {/* Mobile: Karty */}
        <div className="desktop:hidden space-y-4">
          {resources.length === 0 ? (
            <div className="bg-white rounded-lg shadow p-6 text-center text-gray-500">
              Brak sal konferencyjnych
            </div>
          ) : (
            resources.map((resource) => (
              <div key={resource.id} className="bg-white rounded-lg shadow p-4">
                <div className="flex justify-between items-start mb-3">
                  <h3 className="text-lg font-semibold text-gray-900">{resource.name}</h3>
                  <span
                    className={`px-2 py-1 text-xs rounded-full whitespace-nowrap ${
                      resource.status === 'active'
                        ? 'bg-green-100 text-green-800'
                        : 'bg-red-100 text-red-800'
                    }`}
                  >
                    {resource.status === 'active' ? 'Aktywna' : 'Nieaktywna'}
                  </span>
                </div>
                {resource.description && (
                  <p className="text-sm text-gray-500 mb-4">{resource.description}</p>
                )}
                <div className="flex gap-3">
                  <Link
                    href={`/resources/${resource.id}/edit`}
                    className="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-center text-sm"
                  >
                    Edytuj
                  </Link>
                  <button
                    onClick={() => handleDelete(resource.id)}
                    className="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm"
                  >
                    Usuń
                  </button>
                </div>
              </div>
            ))
          )}
        </div>

        {/* Desktop: Tabela */}
        <div className="hidden desktop:block bg-white rounded-lg shadow overflow-hidden">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Nazwa
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Opis
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Akcje
                </th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {resources.length === 0 ? (
                <tr>
                  <td colSpan={4} className="px-6 py-4 text-center text-gray-500">
                    Brak sal konferencyjnych
                  </td>
                </tr>
              ) : (
                resources.map((resource) => (
                  <tr key={resource.id} className="hover:bg-gray-50">
                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      {resource.name}
                    </td>
                    <td className="px-6 py-4 text-sm text-gray-500">
                      {resource.description || '-'}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <span
                        className={`px-2 py-1 text-xs rounded-full ${
                          resource.status === 'active'
                            ? 'bg-green-100 text-green-800'
                            : 'bg-red-100 text-red-800'
                        }`}
                      >
                        {resource.status === 'active' ? 'Aktywna' : 'Nieaktywna'}
                      </span>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <Link
                        href={`/resources/${resource.id}/edit`}
                        className="text-blue-600 hover:text-blue-900 mr-4"
                      >
                        Edytuj
                      </Link>
                      <button
                        onClick={() => handleDelete(resource.id)}
                        className="text-red-600 hover:text-red-900"
                      >
                        Usuń
                      </button>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>

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

