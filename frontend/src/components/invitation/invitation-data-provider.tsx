import { createContext, useContext, type ReactNode } from "react";
import type { LegacyInvitation } from "@/lib/invitation-adapter";

/**
 * Replaces the static `import { invitation } from "@/lib/invitation-data"`
 * with a Context so the SSR server (handling many requests concurrently) can
 * serve a different couple's data per request instead of one shared
 * module-level object — a static import would leak one guest's undangan
 * content into another's response under load.
 */
const InvitationDataCtx = createContext<LegacyInvitation | null>(null);

export function InvitationDataProvider({
  data,
  children,
}: {
  data: LegacyInvitation;
  children: ReactNode;
}) {
  return <InvitationDataCtx.Provider value={data}>{children}</InvitationDataCtx.Provider>;
}

export function useInvitationData(): LegacyInvitation {
  const ctx = useContext(InvitationDataCtx);
  if (!ctx) throw new Error("useInvitationData must be used inside InvitationDataProvider");
  return ctx;
}
