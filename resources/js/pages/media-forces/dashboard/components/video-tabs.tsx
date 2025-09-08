import React, { useContext } from "react";
import { Video as VideoIcon } from "lucide-react";
import { cn } from "@/lib/utils";
import { glassSoft, statusColor, tileBg } from "../media-helpers";
import { MediaDashboardContext } from "../media-dashboard-context";

export function VideoTabs() {
  const ctx = useContext(MediaDashboardContext);
  if (!ctx) return null;
  const { slots, activeIdx, goTo, storeTitle } = ctx;

  return (
    <div className="mt-2 h-28 overflow-x-auto overflow-y-hidden mx-0 md:ml-3">
      <div className="flex flex-nowrap min-w-max gap-5 px-1 pb-4 snap-x snap-mandatory">
        {slots.map((s, idx) => {
          const isCurrent = idx === activeIdx;

          const titleForTab =
            (isCurrent && storeTitle?.trim())
              ? storeTitle.trim()
              : (s.title || null);

          return (
            <button
              key={s.id}
              onClick={() => goTo(idx)}
              className={cn(
                "group snap-start relative inline-flex flex-col items-center gap-1.5 rounded-2xl px-3 py-2 text-xs transition-all duration-300",
                isCurrent ? "translate-y-4" : "translate-y-0 hover:translate-y-0.5",
              )}
              title={titleForTab || s.label}
            >
              <span className="text-center text-[11px] font-medium drop-shadow">{s.label}</span>
              <div
                className={cn(
                  "flex h-12 w-12 items-center justify-center rounded-2xl border transition-all duration-300",
                  glassSoft,
                  tileBg(s.uiStatus, isCurrent),
                  isCurrent && "ring-2 ring-white/50 shadow-[0_20px_60px_-20px_rgba(0,0,0,0.6)]",
                  statusColor(s.dbStatus)
                )}
              >
                <VideoIcon size={20} className="opacity-90" />
              </div>
              {/* {titleForTab ? (
                <span className="max-w-[84px] truncate text-[10px] leading-tight text-white/85">
                  {titleForTab}
                </span>
              ) : (
                <span className="h-3" />
              )} */}
              <span className="pointer-events-none absolute inset-0 rounded-2xl bg-gradient-to-b from-white/15 to-transparent opacity-0 group-hover:opacity-100 transition-opacity" />
            </button>
          );
        })}
      </div>
    </div>
  );
}
