import { ApiError } from "@/lib/api";
import { getAdminSession } from "@/lib/admin-auth";

const API_URL = import.meta.env.VITE_API_URL ?? "http://localhost:8000/api";

export interface ValidationErrorBody {
  message: string;
  errors?: Record<string, string[]>;
}

export class AdminApiError extends ApiError {
  errors?: Record<string, string[]>;

  constructor(status: number, message: string, errors?: Record<string, string[]>) {
    super(status, message);
    this.errors = errors;
  }
}

async function request<T>(path: string, init?: RequestInit): Promise<T> {
  const session = getAdminSession();
  const isFormData = init?.body instanceof FormData;

  const res = await fetch(`${API_URL}${path}`, {
    ...init,
    headers: {
      Accept: "application/json",
      ...(isFormData ? {} : { "Content-Type": "application/json" }),
      ...(session ? { Authorization: `Bearer ${session.token}` } : {}),
      ...init?.headers,
    },
  });

  if (!res.ok) {
    let message = `Permintaan gagal (${res.status})`;
    let errors: Record<string, string[]> | undefined;
    try {
      const body = (await res.json()) as ValidationErrorBody;
      message = body.message ?? message;
      errors = body.errors;
    } catch {
      // response wasn't JSON — keep the generic message
    }
    throw new AdminApiError(res.status, message, errors);
  }

  if (res.status === 204) {
    return undefined as T;
  }

  return (await res.json()) as T;
}

export const adminApi = {
  get: <T>(path: string) => request<T>(path),
  post: <T>(path: string, data?: unknown) =>
    request<T>(path, {
      method: "POST",
      body: data instanceof FormData ? data : JSON.stringify(data ?? {}),
    }),
  put: <T>(path: string, data?: unknown) =>
    request<T>(path, {
      method: "PUT",
      body: JSON.stringify(data ?? {}),
    }),
  patch: <T>(path: string, data?: unknown) =>
    request<T>(path, {
      method: "PATCH",
      body: JSON.stringify(data ?? {}),
    }),
  delete: <T>(path: string) => request<T>(path, { method: "DELETE" }),
  /** Laravel needs `_method` spoofing to parse multipart bodies on PUT. */
  postFormAsPut: <T>(path: string, form: FormData) => {
    form.append("_method", "PUT");
    return request<T>(path, { method: "POST", body: form });
  },
};
