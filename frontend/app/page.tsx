import Link from "next/link";

export default function Home() {
  return (
    <div className="min-h-screen p-8">
      <div className="max-w-7xl mx-auto">
        <h1 className="text-3xl font-bold text-gray-900 mb-8">
          Panel Administracyjny - Rezerwacje
        </h1>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          <Link
            href="/resources"
            className="block p-6 bg-white rounded-lg shadow hover:shadow-md transition-shadow"
          >
            <h2 className="text-xl font-semibold text-gray-800 mb-2">
              Sale Konferencyjne
            </h2>
            <p className="text-gray-600">
              Zarządzaj salami konferencyjnymi
            </p>
          </Link>
          <Link
            href="/reservations"
            className="block p-6 bg-white rounded-lg shadow hover:shadow-md transition-shadow"
          >
            <h2 className="text-xl font-semibold text-gray-800 mb-2">
              Kalendarz Rezerwacji
            </h2>
            <p className="text-gray-600">
              Przeglądaj i zarządzaj rezerwacjami
            </p>
          </Link>
        </div>
      </div>
    </div>
  );
}

