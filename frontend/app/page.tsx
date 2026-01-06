'use client';

import { useEffect, useState } from 'react';
import Link from "next/link";
import Calendar from '@/components/Calendar';
import { getAllReservations, type Reservation } from '@/lib/api';

export default function Home() {
  const [reservations, setReservations] = useState<Reservation[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadReservations();
  }, []);

  const loadReservations = async () => {
    try {
      const data = await getAllReservations();
      setReservations(data);
    } catch (error) {
      console.error('Błąd ładowania rezerwacji:', error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen p-4 desktop:p-8">
      <div className="max-w-7xl mx-auto">
        <div className="flex flex-col desktop:flex-row desktop:justify-between desktop:items-center gap-4 desktop:gap-[10px] mb-6 desktop:mb-8">
          <h1 className="text-2xl desktop:text-3xl font-bold text-gray-900">
            Panel Administracyjny - Rezerwacje
          </h1>
          <div className="flex flex-col desktop:flex-row gap-2 desktop:gap-4 w-full desktop:w-auto">
            <Link
              href="/resources"
              className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-center desktop:text-left"
            >
              Sale Konferencyjne
            </Link>
            <Link
              href="/reservations"
              className="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-center desktop:text-left"
            >
              Zarządzaj Rezerwacjami
            </Link>
          </div>
        </div>

        {loading ? (
          <div className="bg-white rounded-lg shadow p-12 text-center">
            <div className="text-gray-600">Ładowanie kalendarza...</div>
          </div>
        ) : (
          <Calendar reservations={reservations} />
        )}
      </div>
    </div>
  );
}

