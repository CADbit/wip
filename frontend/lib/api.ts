const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8080';

export interface Resource {
  id: string;
  type: string;
  name: string;
  description: string | null;
  status: string;
  unavailability: string | null;
  createdAt: string;
}

export interface Reservation {
  id: string;
  resourceId: string;
  resourceName: string;
  reservedBy: string;
  startDate: string;
  endDate: string;
  createdAt: string;
}

export async function getResources(): Promise<Resource[]> {
  const response = await fetch(`${API_URL}/api/resources/conference-rooms`);
  const data = await response.json();
  return data.data || [];
}

export async function getResource(id: string): Promise<Resource> {
  const response = await fetch(`${API_URL}/api/resources/${id}`);
  const data = await response.json();
  return data.data;
}

export async function createResource(resource: {
  type: string;
  name: string;
  description?: string;
  status: string;
}): Promise<Resource> {
  const response = await fetch(`${API_URL}/api/resources`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(resource),
  });
  const data = await response.json();
  return data.data;
}

export async function updateResource(
  id: string,
  resource: {
    name?: string;
    description?: string;
    status?: string;
  }
): Promise<Resource> {
  const response = await fetch(`${API_URL}/api/resources/${id}`, {
    method: 'PATCH',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(resource),
  });
  const data = await response.json();
  return data.data;
}

export async function deleteResource(id: string): Promise<void> {
  await fetch(`${API_URL}/api/resources/${id}`, {
    method: 'DELETE',
  });
}

export async function getReservationsByResource(resourceId: string): Promise<Reservation[]> {
  const response = await fetch(`${API_URL}/api/reservations/resource/${resourceId}`);
  const data = await response.json();
  return data.data || [];
}

export async function createReservation(reservation: {
  resourceId: string;
  reservedBy: string;
  startDate: string;
  endDate: string;
}): Promise<Reservation> {
  const response = await fetch(`${API_URL}/api/reservations`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(reservation),
  });
  const data = await response.json();
  return data.data;
}

export async function cancelReservation(id: string): Promise<void> {
  await fetch(`${API_URL}/api/reservations/${id}`, {
    method: 'DELETE',
  });
}

