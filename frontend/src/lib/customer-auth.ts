import { useEffect, useState } from "react";
import { useNavigate } from "@tanstack/react-router";

const SESSION_KEY = "undangan-customer-session";

export interface CustomerUser {
  id: number;
  name: string;
  email: string;
  role: string;
  email_verified_at: string | null;
}

export interface CustomerSession {
  token: string;
  user: CustomerUser;
}

export function getCustomerSession(): CustomerSession | null {
  if (typeof window === "undefined") return null;
  const raw = window.localStorage.getItem(SESSION_KEY);
  if (!raw) return null;
  try {
    return JSON.parse(raw) as CustomerSession;
  } catch {
    return null;
  }
}

export function setCustomerSession(session: CustomerSession): void {
  window.localStorage.setItem(SESSION_KEY, JSON.stringify(session));
}

export function clearCustomerSession(): void {
  window.localStorage.removeItem(SESSION_KEY);
}

/** Guards a /dashboard/* page — redirects to login if there's no session. */
export function useRequireCustomerAuth(): { session: CustomerSession | null; ready: boolean } {
  const navigate = useNavigate();
  const [session, setSession] = useState<CustomerSession | null>(null);
  const [ready, setReady] = useState(false);

  useEffect(() => {
    const current = getCustomerSession();
    if (!current) {
      navigate({ to: "/dashboard/login" });
      return;
    }
    setSession(current);
    setReady(true);
  }, [navigate]);

  return { session, ready };
}
