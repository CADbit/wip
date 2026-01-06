'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import { createResource, ApiException } from '@/lib/api';

export default function NewResourcePage() {
  const router = useRouter();
  const [loading, setLoading] = useState(false);
  const [formData, setFormData] = useState({
    name: '',
    description: '',
    status: 'active',
  });
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [errorMessage, setErrorMessage] = useState<string>('');

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setErrors({});
    setErrorMessage('');

    try {
      await createResource({
        type: 'conference_room',
        name: formData.name,
        description: formData.description || undefined,
        status: formData.status,
      });
      router.push('/resources');
    } catch (error) {
      console.error('Błąd tworzenia zasobu:', error);
      setLoading(false);
      if (error instanceof ApiException) {
        setErrorMessage(error.message);
        if (error.errors) {
          const fieldErrors: Record<string, string> = {};
          Object.entries(error.errors).forEach(([key, value]) => {
            fieldErrors[key] = Array.isArray(value) ? value[0] : value;
          });
          setErrors(fieldErrors);
        }
      } else {
        setErrorMessage('Nie udało się utworzyć sali. Spróbuj ponownie.');
      }
    }
  };

  return (
    <div className="min-h-screen p-4 desktop:p-8">
      <div className="max-w-2xl mx-auto">
        <h1 className="text-2xl desktop:text-3xl font-bold text-gray-900 mb-6 desktop:mb-8">Dodaj Salę Konferencyjną</h1>

        <form onSubmit={handleSubmit} className="bg-white rounded-lg shadow p-4 desktop:p-6">
          {errorMessage && (
            <div className="mb-4 p-4 bg-red-50 border border-red-200 rounded-md">
              <div className="text-sm text-red-800">{errorMessage}</div>
            </div>
          )}
          <div className="mb-4">
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Nazwa *
            </label>
            <input
              type="text"
              required
              value={formData.name}
              onChange={(e) => {
                setFormData({ ...formData, name: e.target.value });
                if (errors.name) {
                  setErrors({ ...errors, name: '' });
                }
              }}
              className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 ${
                errors.name 
                  ? 'border-red-300 focus:ring-red-500' 
                  : 'border-gray-300 focus:ring-blue-500'
              }`}
            />
            {errors.name && (
              <p className="mt-1 text-sm text-red-600">{errors.name}</p>
            )}
          </div>

          <div className="mb-4">
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Opis
            </label>
            <textarea
              value={formData.description}
              onChange={(e) => setFormData({ ...formData, description: e.target.value })}
              rows={4}
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div className="mb-6">
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Status *
            </label>
            <select
              value={formData.status}
              onChange={(e) => {
                setFormData({ ...formData, status: e.target.value });
                if (errors.status) {
                  setErrors({ ...errors, status: '' });
                }
              }}
              className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 ${
                errors.status 
                  ? 'border-red-300 focus:ring-red-500' 
                  : 'border-gray-300 focus:ring-blue-500'
              }`}
            >
              <option value="active">Aktywna</option>
              <option value="disabled">Nieaktywna</option>
            </select>
            {errors.status && (
              <p className="mt-1 text-sm text-red-600">{errors.status}</p>
            )}
          </div>

          <div className="flex flex-col desktop:flex-row gap-3 desktop:gap-4">
            <button
              type="submit"
              disabled={loading}
              className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors w-full desktop:w-auto"
            >
              {loading ? 'Zapisywanie...' : 'Zapisz'}
            </button>
            <button
              type="button"
              onClick={() => router.back()}
              className="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors w-full desktop:w-auto"
            >
              Anuluj
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}

