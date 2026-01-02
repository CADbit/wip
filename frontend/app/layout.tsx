import type { Metadata } from "next";
import "./globals.css";

export const metadata: Metadata = {
  title: "Panel Administracyjny - Rezerwacje",
  description: "Zarządzanie rezerwacjami zasobów",
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="pl">
      <body className="bg-gray-50">{children}</body>
    </html>
  );
}

