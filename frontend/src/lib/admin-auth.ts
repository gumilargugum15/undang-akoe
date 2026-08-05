import { useEffect, useState } from "react";
import { useNavigate } from "@tanstack/react-router";

const SESSION_KEY = "undangan-admin-session";

export interface AdminUser {
  id: number;
  name: string;
  email: string;
  role: string;
}

export interface AdminSession {
  token: string;
  user: AdminUser;
}

export function getAdminSession(): AdminSession | null {
  if (typeof window === "undefined") return null;
  const raw = window.localStorage.getItem(SESSION_KEY);
  if (!raw) return null;
  try {
    return JSON.parse(raw) as AdminSession;
  } catch {
    return null;
  }
}

export function setAdminSession(session: AdminSession): void {
  window.localStorage.setItem(SESSION_KEY, JSON.stringify(session));
}

export function clearAdminSession(): void {
  window.localStorage.removeItem(SESSION_KEY);
}

export function isAdminSession(session: AdminSession | null): session is AdminSession {
  return !!session && session.user.role === "admin";
}

/** Guards an /admin/* page — redirects to login if there's no valid admin session. */
export function useRequireAdmin(): { session: AdminSession | null; ready: boolean } {
  const navigate = useNavigate();
  const [session, setSession] = useState<AdminSession | null>(null);
  const [ready, setReady] = useState(false);

  useEffect(() => {
    const current = getAdminSession();
    if (!isAdminSession(current)) {
      navigate({ to: "/admin/login" });
      return;
    }
    setSession(current);
    setReady(true);
  }, [navigate]);

  return { session, ready };
}
