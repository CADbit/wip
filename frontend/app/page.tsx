'use client';

import { useEffect, useState } from 'react';
import Link from "next/link";
import Calendar from '@/components/Calendar';
import { getAllReservations, type Reservation } from '@/lib/api';

export default function Home() {
  const [reservations, setReservations] = useState<Reservation[]>([]);
  const [loading, setLoading] = useState(true);
  const [isMenuOpen, setIsMenuOpen] = useState(false);

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
          <div className="flex items-center justify-between w-full desktop:w-auto">
            <h1 className="text-2xl desktop:text-3xl font-bold text-gray-900">
              Panel Administracyjny - Rezerwacje
            </h1>
            {/* Hamburger button - tylko na mobile */}
            <button
              onClick={() => setIsMenuOpen(!isMenuOpen)}
              className="desktop:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors"
              aria-label="Menu"
            >
              <svg
                className="w-6 h-6 text-gray-700"
                fill="none"
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth="2"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                {isMenuOpen ? (
                  <path d="M6 18L18 6M6 6l12 12" />
                ) : (
                  <path d="M4 6h16M4 12h16M4 18h16" />
                )}
              </svg>
            </button>
          </div>
          
          {/* Mobile menu - dropdown */}
          {isMenuOpen && (
            <div className="desktop:hidden w-full bg-white rounded-lg shadow-lg border border-gray-200 py-2 transition-all duration-200">
              <Link
                href="/resources"
                onClick={() => setIsMenuOpen(false)}
                className="block px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-center mx-2 mb-2"
              >
                Sale Konferencyjne
              </Link>
              <Link
                href="/reservations"
                onClick={() => setIsMenuOpen(false)}
                className="block px-4 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-center mx-2"
              >
                Zarządzaj Rezerwacjami
              </Link>
            </div>
          )}

          {/* Desktop menu - normalne przyciski */}
          <div className="hidden desktop:flex flex-row gap-4">
            <Link
              href="/resources"
              className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
              Sale Konferencyjne
            </Link>
            <Link
              href="/reservations"
              className="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
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

