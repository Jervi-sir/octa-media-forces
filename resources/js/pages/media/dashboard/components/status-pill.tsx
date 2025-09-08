import React from "react";
import { cn } from "@/lib/utils";
import type { ServerVideo } from "../media-helpers";

export function StatusPill({ dbStatus }: { dbStatus: ServerVideo["status"] }) {
  const base = "rounded-full px-2.5 py-0.5 text-[10px] font-semibold tracking-wide";
  switch (dbStatus) {
    case "approved":
      return <span className={cn(base, "bg-emerald-500/20 border border-emerald-400/40 text-emerald-50")}>APPROVED</span>;
    case "submitted":
      return <span className={cn(base, "bg-white/15 border border-white/25 text-white/90")}>SUBMITTED</span>;
    case "changes_requested":
      return <span className={cn(base, "bg-amber-500/20 border border-amber-400/40 text-amber-50")}>CHANGES</span>;
    case "rejected":
      return <span className={cn(base, "bg-rose-500/20 border border-rose-400/40 text-rose-50")}>REJECTED</span>;
    default:
      return <span className={cn(base, "bg-slate-200/15 border border-white/20 text-white/80")}>DRAFT</span>;
  }
}
