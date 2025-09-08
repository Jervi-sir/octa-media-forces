// resources/js/Pages/media/dashboard.tsx
import React, { useCallback, useEffect, useMemo, useRef, useState } from "react";
import { Head, router, usePage } from "@inertiajs/react";
import axios from "axios";

import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert";
import { Separator } from "@/components/ui/separator";
import { Globe, User2, ChevronDown, UploadCloud, Gift, AlertCircleIcon, Video as VideoIcon } from "lucide-react";
import { cn } from "@/lib/utils";

/** -------------------------------- Types -------------------------------- */
export type StepStatus = "current" | "success" | "failed" | "pending";

export type ServerVideo = {
  id: number;
  slot_number: number; // 1..11
  title?: string | null;
  description?: string | null;
  file_path?: string | null;
  public_url?: string | null; // added in controller via accessor
  status: "draft" | "submitted" | "approved" | "changes_requested" | "rejected";
  submitted_at?: string | null;
  reviewed_at?: string | null;
  feedback?: string | null;
};

type PageProps = {
  mediaForce: { id: number; name?: string | null; email: string };
  videos: ServerVideo[];
};

/** ------------------------------- Styles -------------------------------- */
const glass =
  "backdrop-blur-xl bg-white/10 border border-white/20 shadow-[0_1px_0_rgba(255,255,255,0.55),0_10px_30px_-12px_rgba(0,0,0,0.35)]";
const glassSoft =
  "backdrop-blur-xl bg-white/8 border border-white/15 shadow-[0_1px_0_rgba(255,255,255,0.55),0_8px_24px_-14px_rgba(0,0,0,0.30)]";

/** ------------------------------- Helpers ------------------------------- */
function mapStatusToUI(s: ServerVideo["status"]): StepStatus {
  switch (s) {
    case "approved":
      return "success";
    case "rejected":
    case "changes_requested":
      return "failed";
    case "submitted":
      return "pending";
    default:
      return "current"; // draft
  }
}

function tileBg(status: StepStatus, isCurrent: boolean) {
  if (isCurrent) return "bg-white/10 border-white/30";
  switch (status) {
    case "success":
      return "bg-emerald-400/15 border-emerald-300/30";
    case "failed":
      return "bg-rose-400/15 border-rose-300/30";
    default:
      return "bg-white/10 border-white/20";
  }
}

function StatusPill({ dbStatus }: { dbStatus: ServerVideo["status"] }) {
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

/**
 * AXIOS endpoints — avoid importing server controllers in the client.
 * If you have Wayfinder, swap these with wayfinder('route.name').
 */
const ENDPOINTS = {
  upload: "/media/videos/upload",
  store: "/media/videos/store",
};

/** -------------------------------- Page --------------------------------- */
export default function MediaDashboard() {
  const { props } = usePage<PageProps>();
  const { mediaForce, videos } = props;

  /**
   * Build a flat array of 11 slots. This is the *single source of truth*
   * for navigation and rendering.
   */
  const slots = useMemo(() => {
    const bySlot = new Map<number, ServerVideo>((videos ?? []).map((v) => [v.slot_number, v]));

    const arr = Array.from({ length: 11 }, (_, i) => {
      const slot = i + 1;
      const v = bySlot.get(slot);
      const dbStatus: ServerVideo["status"] = v?.status ?? "draft";
      const uiStatus = mapStatusToUI(dbStatus);
      return {
        // immutable view-model for the UI
        id: `slot_${slot}`,
        slot,
        label: slot === 1 ? "Test1" : `Video ${slot}`,
        dbStatus,
        uiStatus,
        public_url: v?.public_url ?? null,
        title: v?.title ?? null,
        server: v ?? null,
      } as const;
    });

    // Ensure the first actionable slot becomes "current" visually
    const firstIdx = arr.findIndex((s) => s.dbStatus === "draft" || s.dbStatus === "changes_requested");
    if (firstIdx >= 0) arr[firstIdx] = { ...arr[firstIdx], uiStatus: "current" };

    return arr;
  }, [videos]);

  // Expose data array for ad-hoc debugging if needed
  // ;(window as any).__MEDIA_SLOTS__ = slots;

  /** Navigation state uses *index*, not ids */
  const [activeIdx, setActiveIdx] = useState<number>(() => Math.max(0, slots.findIndex((s) => s.uiStatus === "current")));
  useEffect(() => {
    // Re-sync when videos change
    const idx = Math.max(0, slots.findIndex((s) => s.uiStatus === "current"));
    setActiveIdx(idx >= 0 ? idx : 0);
  }, [slots]);

  const active = slots[activeIdx] ?? slots[0];

  // Upload state for the *current* slot only
  const [files, setFiles] = useState<File[]>([]);
  const [storeTitle, setStoreTitle] = useState("");
  const [titleDirty, setTitleDirty] = useState(false);

  const [uploadProgress, setUploadProgress] = useState<number | null>(null);
  const [uploaded, setUploaded] = useState<{ file_path: string; public_url: string } | null>(null);
  const [saving, setSaving] = useState(false);
  const abortRef = useRef<AbortController | null>(null);

  const next = useCallback(() => setActiveIdx((i) => Math.min(slots.length - 1, i + 1)), [slots.length]);
  const prev = useCallback(() => setActiveIdx((i) => Math.max(0, i - 1)), []);
  const goTo = useCallback((idx: number) => setActiveIdx(Math.max(0, Math.min(idx, slots.length - 1))), [slots.length]);

  // Keyboard navigation (Left/Right arrows)
  useEffect(() => {
    const onKey = (e: KeyboardEvent) => {
      if (e.key === "ArrowRight") next();
      if (e.key === "ArrowLeft") prev();
    };
    window.addEventListener("keydown", onKey);
    return () => window.removeEventListener("keydown", onKey);
  }, [next, prev]);

  // Upload when files change (for the *active* slot). Aborts on tab switch.
  useEffect(() => {
    const run = async () => {
      if (!files.length) return;
      const file = files[0];

      // Reset state
      setUploadProgress(0);
      setUploaded(null);

      // Cancel any in-flight request when switching slots
      abortRef.current?.abort();
      const ac = new AbortController();
      abortRef.current = ac;

      const form = new FormData();
      form.append("file", file);

      try {
        const { data } = await axios.post(ENDPOINTS.upload, form, {
          signal: ac.signal,
          headers: { "Content-Type": "multipart/form-data" },
          onUploadProgress: (evt) => {
            if (!evt.total) return;
            const p = Math.round((evt.loaded / evt.total) * 100);
            setUploadProgress(p);
          },
        });
        setUploaded({ file_path: data.file_path, public_url: data.public_url });
      } catch (e: any) {
        if (e?.name === "CanceledError" || e?.code === "ERR_CANCELED") return; // switched tab
        console.error(e);
        setUploadProgress(null);
        setUploaded(null);
        alert("Upload failed. Please try again.");
      }
    };
    run();

    return () => {
      // cleanup if files changes again before finishing
      abortRef.current?.abort();
    };
  }, [files]);

  // Clear *slot-local* UI state on active change
  useEffect(() => {
    abortRef.current?.abort();
    setFiles([]);
    setUploadProgress(null);
    setUploaded(null);
    // PREFILL from server
    setStoreTitle(active.title ?? "");
    setTitleDirty(false);
  }, [activeIdx]); // or [active.id]

  // if videos prop refreshes (after save/review), and user hasn't typed, refresh the title
  useEffect(() => {
    if (titleDirty) return;
    setStoreTitle(active.title ?? "");
  }, [videos, active.id, titleDirty]);
  // when user types, mark dirty
  const onTitleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (!titleDirty) setTitleDirty(true);
    setStoreTitle(e.target.value);
  };

  const handleClear = () => {
    abortRef.current?.abort();
    setFiles([]);
    setUploadProgress(null);
    setUploaded(null);
    setStoreTitle(active.title ?? ""); // or "" if you want empty after Clear
    setTitleDirty(false);
  };

  const handleDone = async () => {
    if (!uploaded) {
      alert("Please upload a video first.");
      return;
    }
    setSaving(true);
    try {
      const payload = {
        slot_number: active.slot,
        title: storeTitle || active.title || `Video ${active.slot}`,
        description: "",
        file_path: uploaded.file_path,
        status: "submitted" as const,
      };
      await axios.post(ENDPOINTS.store, payload);
      // Refresh only the videos prop
      router.reload({ only: ["videos"] });
      alert("Submitted! We will review it shortly.");
    } catch (e) {
      console.error(e);
      alert("Save failed. Please try again.");
    } finally {
      setSaving(false);
    }
  };

  /** -------------------------------- Render -------------------------------- */
  return (
    <div className="min-h-screen text-white relative overflow-hidden font-mono">
      <Head title="Media Dashboard" />

      {/* Background */}
      <div className="pointer-events-none absolute inset-0">
        <div className="absolute -top-40 -left-32 h-[520px] w-[520px] rounded-full bg-[radial-gradient(closest-side,rgba(120,119,198,0.45),rgba(120,119,198,0)_70%)] blur-2xl" />
        <div className="absolute -bottom-32 -right-24 h-[520px] w-[520px] rounded-full bg-[radial-gradient(closest-side,rgba(56,189,248,0.45),rgba(56,189,248,0)_70%)] blur-2xl" />
        <div className="absolute inset-0 bg-[linear-gradient(120deg,rgba(255,255,255,0.08),rgba(255,255,255,0.02))]" />
      </div>

      {/* Top bar */}
      <header className={cn("sticky top-0 z-30", "mx-auto flex w-full max-w-5xl items-center justify-between px-6 py-4")}>
        <div className={cn("px-4 py-2 rounded-2xl", glass)}>
          <div className="text-base font-semibold tracking-tight">Media Force</div>
        </div>
        <div className={cn("flex items-center gap-4 text-sm", "px-3 py-2 rounded-2xl", glass)}>
          <div className="flex items-center gap-2 text-white/80">
            <Globe className="h-4 w-4" />
            <span>Language</span>
          </div>
          <Separator orientation="vertical" className="h-5 bg-white/20" />
          <div className="flex items-center gap-2">
            <User2 className="h-4 w-4 opacity-90" />
            <span className="opacity-90">{mediaForce?.name || mediaForce.email}</span>
            <ChevronDown className="h-4 w-4 opacity-70" />
          </div>
        </div>
      </header>

      {/* Container */}
      <div className="mx-auto w-full max-w-5xl">
        {/* Numbers row */}
        <div className={cn("flex items-center justify-between text-sm mb-5 px-4 py-3 rounded-2xl mx-6", glass)}>
          <div className="font-medium">Actual numbers :</div>
          <div className="flex items-center gap-6">
            <span className="opacity-90">
              Team: <span className="font-semibold">18</span>
            </span>
            <span className="opacity-90">
              Videos done:{" "}
              <span className="font-semibold">{(videos ?? []).filter((r) => r.status === "approved" || r.status === "submitted").length}</span>
            </span>
          </div>
        </div>

        {/* Tabs */}
        <div className="mt-2 h-36 overflow-x-auto overflow-y-hidden ">
          <div className="flex flex-nowrap min-w-max gap-5 px-1 pb-4 snap-x snap-mandatory">
            {slots.map((s, idx) => {
              const isCurrent = idx === activeIdx;

              // pick the title to show on the tab:
              // - for the active tab, prefer the in-progress storeTitle (even before saving)
              // - otherwise use the server title if available
              const titleForTab = (isCurrent && storeTitle?.trim())
                ? storeTitle.trim()
                : (s.title || null);

              let btnColor = "bg-gray-100/20 text-white"; // default draft
              if (s.dbStatus === "submitted") btnColor = "bg-green-600 text-white";
              if (s.dbStatus === "approved") btnColor = "bg-green-700 text-white"; // success
              if (s.dbStatus === "rejected" || s.dbStatus === "changes_requested") btnColor = "bg-red-600 text-white";

              return (
                <button
                  key={s.id}
                  onClick={() => goTo(idx)}
                  className={cn(
                    "group snap-start relative inline-flex flex-col items-center gap-1.5 rounded-2xl px-3 py-2 text-xs transition-all duration-300",
                    isCurrent ? "translate-y-4" : "translate-y-0 hover:translate-y-0.5",
                  )}
                  title={titleForTab || s.label} // tooltip for longer names
                >
                  <span className="text-center text-[11px] font-medium drop-shadow">{s.label}</span>

                  <div
                    className={cn(
                      "flex h-12 w-12 items-center justify-center rounded-2xl border transition-all duration-300",
                      glassSoft,
                      tileBg(s.uiStatus, isCurrent),
                      isCurrent && "ring-2 ring-white/50 shadow-[0_20px_60px_-20px_rgba(0,0,0,0.6)]",
                      btnColor
                    )}
                  >
                    <VideoIcon size={20} className="opacity-90" />
                  </div>

                  {/* NEW: store title under the icon */}
                  {titleForTab ? (
                    <span className="max-w-[84px] truncate text-[10px] leading-tight text-white/85">
                      {titleForTab}
                    </span>
                  ) : (
                    <span className="h-3" /> // keep heights consistent when no title
                  )}

                  <span className="pointer-events-none absolute inset-0 rounded-2xl bg-gradient-to-b from-white/15 to-transparent opacity-0 group-hover:opacity-100 transition-opacity" />
                </button>
              );
            })}

          </div>
        </div>

        {/* Main panel */}
        <div className={cn("mt-2 rounded-3xl p-6 mx-6", glass)}>
          {/* Slot header */}
          <div className="mb-2 flex items-center justify-between">
            <div className="text-sm font-semibold">
              Slot #{active.slot} — <span className="opacity-80">{active.label}</span>
            </div>
            <div className="text-xs opacity-80 flex items-center gap-3">
              {(() => {
                const row = (videos ?? []).find((r) => r.slot_number === active.slot);
                if (!row) return null;
                return (
                  <>
                    <StatusPill dbStatus={row.status} />
                    {row.public_url ? (
                      <a className="underline decoration-dotted" href={row.public_url} target="_blank" rel="noreferrer">
                        View current file
                      </a>
                    ) : null}
                  </>
                );
              })()}
            </div>
          </div>

          {/* Upload */}
          <div className="space-y-3">
            <div className="text-xs text-white/80">Upload your video</div>
            <Dropzone key={active.id} onFiles={(list) => setFiles(Array.from(list))} />

            {/* Progress */}
            {uploadProgress !== null && (
              <div className="mt-3">
                <div className="h-2 w-full rounded-full bg-white/10 overflow-hidden">
                  <div className="h-2 bg-white/70 transition-all" style={{ width: `${uploadProgress}%` }} />
                </div>
                <div className="mt-1 text-xs text-white/80">{uploadProgress}%</div>
              </div>
            )}

            {/* Preview */}
            {(() => {
              const rowForSlot = (videos ?? []).find((r) => r.slot_number === active.slot);
              const existingUrl = rowForSlot?.public_url || null;
              const linkUrl = uploaded?.public_url ?? existingUrl;
              if (!linkUrl) return null;
              const label = uploaded ? "Uploaded" : "Existing";
              return (
                <div className="text-xs text-white/80">
                  {label}:{" "}
                  <a href={linkUrl} className="underline" target="_blank" rel="noreferrer">
                    preview
                  </a>
                  <div className="opacity-70">Slot #{active.slot}</div>
                </div>
              );
            })()}
          </div>

          {/* Store name */}
          <div className="space-y-2 mt-5">
            <div className="text-xs text-white/80">Store's name (used as title)</div>
            <div className={cn("rounded-2xl overflow-hidden", glassSoft)}>
              <Input
                value={storeTitle}
                onChange={onTitleChange}
                placeholder="Store name"
                className={cn(
                  "h-11 bg-white/5 border-white/20 text-white placeholder:text-white/50",
                  "focus-visible:ring-white/40 focus-visible:border-white/40 rounded-2xl"
                )}
              />
            </div>
          </div>

          {/* Feedback */}
          {(() => {
            const row = (videos ?? []).find((r) => r.slot_number === active.slot);
            if (!row) return null;
            if (row.status === "changes_requested" || row.status === "rejected") {
              return (
                <Alert className={cn("mt-5 rounded-2xl", "bg-white/10 border-white/20 text-white")}>
                  <AlertCircleIcon className="h-4 w-4" />
                  <AlertTitle>{row.status === "rejected" ? "Rejected" : "Changes requested"}</AlertTitle>
                  <AlertDescription className="whitespace-pre-wrap">{row.feedback || "Please update and resubmit this slot."}</AlertDescription>
                </Alert>
              );
            }
            return null;
          })()}

          {/* Actions */}
          <div className="flex items-center justify-center gap-4 pt-6">
            <Button
              variant="secondary"
              onClick={handleClear}
              className={cn("h-10 px-6 rounded-xl text-white", "bg-white/10 hover:bg-white/20 border border-white/20 backdrop-blur-xl")}
              disabled={saving}
            >
              Clear
            </Button>
            <Button
              onClick={handleDone}
              disabled={!uploaded || saving}
              className={cn("h-10 px-8 rounded-xl font-semibold", "bg-white/80 hover:bg-white text-black transition-colors")}
            >
              {saving ? "Saving…" : "Done"}
            </Button>
          </div>

          {/* Prev / Next */}
          <div className="flex items-center justify-between mt-6">
            <Button variant="ghost" onClick={prev} className="text-white/80 hover:text-white">← Previous</Button>
            <Button variant="ghost" onClick={next} className="text-white/80 hover:text-white">Next →</Button>
          </div>
        </div>

        {/* Reward CTA */}
        <div className={cn("mt-6 rounded-3xl p-8 text-center mx-6", glass)}>
          <div className="flex items-center justify-center gap-2 text-white/90">
            <Gift className="h-5 w-5" />
            <span className="text-sm">Claim Reward (280000da) code to OGM</span>
          </div>
        </div>
      </div>

      <footer className="py-12" />
    </div>
  );
}

/** ---------------------------- Local Components ---------------------------- */
function Dropzone({ onFiles }: { onFiles: (files: FileList) => void }) {
  const [dragOver, setDragOver] = useState(false);
  const fileRef = useRef<HTMLInputElement | null>(null);

  const handle = useCallback((e: React.DragEvent) => {
    e.preventDefault();
    e.stopPropagation();
  }, []);

  return (
    <div
      onDragEnter={(e) => { handle(e); setDragOver(true); }}
      onDragOver={handle}
      onDragLeave={(e) => { handle(e); setDragOver(false); }}
      onDrop={(e) => { handle(e); setDragOver(false); if (e.dataTransfer.files?.length) onFiles(e.dataTransfer.files); }}
      className={cn(
        "group relative flex h-24 w-full cursor-pointer items-center justify-center rounded-2xl transition-all",
        glass,
        dragOver ? "ring-2 ring-white/50" : "ring-0",
      )}
      onClick={() => fileRef.current?.click()}
    >
      <input
        ref={fileRef}
        type="file"
        accept="video/*"
        className="hidden"
        onChange={(e) => e.target.files && onFiles(e.target.files)}
      />
      <div className="flex items-center gap-2 text-sm text-white/80">
        <UploadCloud className="h-4 w-4" />
        <span>Drop or select a video</span>
      </div>
      <div className="pointer-events-none absolute inset-0 rounded-2xl bg-gradient-to-br from-white/10 to-transparent opacity-0 transition-opacity group-hover:opacity-100" />
    </div>
  );
}
