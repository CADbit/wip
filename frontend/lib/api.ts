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

export interface ApiError {
  error: boolean;
  message: string;
  errors?: Record<string, string | string[]>;
}

export interface ApiSuccessResponse<T> {
  data?: T;
  message?: string;
}

export class ApiException extends Error {
  constructor(
    public message: string,
    public statusCode: number,
    public errors?: Record<string, string | string[]>
  ) {
    super(message);
    this.name = 'ApiException';
  }
}

async function handleResponse<T>(response: Response): Promise<T> {
  const contentType = response.headers.get('content-type');
  const isJson = contentType && contentType.includes('application/json');
  
  if (!isJson) {
    if (!response.ok) {
      throw new ApiException(
        `Błąd serwera: ${response.statusText}`,
        response.status
      );
    }
    return {} as T;
  }

  const data = await response.json();

  if (!response.ok) {
    const errorData = data as ApiError;
    throw new ApiException(
      errorData.message || 'Wystąpił błąd',
      response.status,
      errorData.errors
    );
  }

  // Success response
  const successData = data as ApiSuccessResponse<T>;
  return (successData.data ?? successData) as T;
}

export async function getResources(): Promise<Resource[]> {
  const response = await fetch(`${API_URL}/api/resources/conference-rooms`);
  return handleResponse<Resource[]>(response);
}

export async function getResource(id: string): Promise<Resource> {
  const response = await fetch(`${API_URL}/api/resources/${id}`);
  return handleResponse<Resource>(response);
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
  return handleResponse<Resource>(response);
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
  return handleResponse<Resource>(response);
}

export async function deleteResource(id: string): Promise<void> {
  const response = await fetch(`${API_URL}/api/resources/${id}`, {
    method: 'DELETE',
  });
  await handleResponse<void>(response);
}

export async function getAllReservations(): Promise<Reservation[]> {
  const response = await fetch(`${API_URL}/api/reservations`);
  const data = await handleResponse<Reservation[]>(response);
  return Array.isArray(data) ? data : [];
}

export async function getReservationsByResource(resourceId: string): Promise<Reservation[]> {
  const response = await fetch(`${API_URL}/api/reservations/resource/${resourceId}`);
  const data = await handleResponse<Reservation[]>(response);
  return Array.isArray(data) ? data : [];
}

export async function getReservationsByResourceAndDate(resourceId: string, date: string): Promise<Reservation[]> {
  // date format: YYYY-MM-DD
  const response = await fetch(`${API_URL}/api/reservations/resource/${resourceId}/date/${date}`);
  const data = await handleResponse<Reservation[]>(response);
  return Array.isArray(data) ? data : [];
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
  return handleResponse<Reservation>(response);
}

export async function cancelReservation(id: string): Promise<void> {
  const response = await fetch(`${API_URL}/api/reservations/${id}`, {
    method: 'DELETE',
  });
  await handleResponse<void>(response);
}

