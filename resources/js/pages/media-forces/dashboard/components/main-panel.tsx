import React, { useContext } from "react";
import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { cn, formatMediaUrl } from "@/lib/utils";
import { AlertCircleIcon, CheckCircle2, Hourglass } from "lucide-react";
import { glass, glassSoft } from "../media-helpers";
import { MediaDashboardContext } from "../media-dashboard-context";
import { StatusPill } from "./status-pill";
import { Dropzone } from "./dropzone";

/** Color system for the feedback panel by status */
const panelClassFor = (status: "draft" | "submitted" | "approved" | "changes_requested" | "rejected") => {
  switch (status) {
    case "approved":
      return "bg-emerald-500/10 border-emerald-400/30 text-emerald-100";
    case "submitted":
      return "bg-amber-500/10 border-amber-400/30 text-amber-100";
    case "changes_requested":
      return "bg-orange-500/10 border-orange-400/30 text-orange-100";
    case "rejected":
      return "bg-rose-500/10 border-rose-400/30 text-rose-100";
    default:
      return "bg-white/10 border-white/20 text-white";
  }
};

const titleFor = (status: "draft" | "submitted" | "approved" | "changes_requested" | "rejected") => {
  switch (status) {
    case "approved":
      return "Approved";
    case "submitted":
      return "Submitted — Under review";
    case "changes_requested":
      return "Changes requested";
    case "rejected":
      return "Rejected";
    default:
      return "Draft";
  }
};

const iconFor = (status: "draft" | "submitted" | "approved" | "changes_requested" | "rejected") => {
  if (status === "approved") return <CheckCircle2 className="h-4 w-4" />;
  if (status === "submitted") return <Hourglass className="h-4 w-4" />;
  return <AlertCircleIcon className="h-4 w-4" />;
};

export function MainPanel() {
  const ctx = useContext(MediaDashboardContext);
  if (!ctx) return null;

  const {
    videos, active, uploadProgress, uploaded, storeTitle, setStoreTitle,
    titleDirty, setTitleDirty, handleClear, handleDone, saving, prev, next, setFiles
  } = ctx;

  const rowForSlot = (videos ?? []).find((r) => r.slot_number === active.slot);
  const linkUrl = uploaded?.file_path || rowForSlot?.file_path || null;
  const thumbnailPreviewUrl = uploaded?.thumbnail_path || rowForSlot?.thumbnail_path || null;

  // Support both `review_feedback` and `feedback`
  const reviewFeedback: string | null =
    ((rowForSlot as any)?.review_feedback ?? rowForSlot?.feedback ?? null) || null;

  const onTitleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (!titleDirty) setTitleDirty(true);
    setStoreTitle(e.target.value);
  };

  return (
    <div className={cn("mt-2 rounded-3xl p-6 mx-6", glass)}>
      {/* Slot header */}
      <div className="mb-2 flex items-center justify-between">
        <div className="text-sm font-semibold">
          Slot #{active.slot} — <span className="opacity-80">{active.label}</span>
        </div>
        <div className="text-xs opacity-80 flex items-center gap-3">
          {rowForSlot && (
            <>
              <StatusPill dbStatus={rowForSlot.status} />
              {rowForSlot.file_path ? (
                <a
                  className="underline decoration-dotted"
                  href={formatMediaUrl(rowForSlot.file_path!)}
                  target="_blank"
                  rel="noreferrer"
                >
                  View current file
                </a>
              ) : null}
            </>
          )}
        </div>
      </div>

      {/* Upload */}
      <div className="space-y-3">
        <div className="text-xs text-white/80">Upload your video</div>
        <Dropzone key={active.id} onFiles={(list) => setFiles(Array.from(list))} />

        {uploadProgress !== null && (
          <div className="mt-3">
            <div className="h-2 w-full rounded-full bg-white/10 overflow-hidden">
              <div className="h-2 bg-white/70 transition-all" style={{ width: `${uploadProgress}%` }} />
            </div>
            <div className="mt-1 text-xs text-white/80">{uploadProgress}%</div>
          </div>
        )}

        {(linkUrl || thumbnailPreviewUrl) && (
          <div className="text-xs text-white/80 flex flex-col gap-2">
            <div>
              {uploaded ? "Uploaded" : "Existing"}:{" "}
              <a className="underline" href={formatMediaUrl(linkUrl!)} target="_blank" rel="noreferrer">
                preview
              </a>
              <div className="opacity-70">Slot #{active.slot}</div>
            </div>
            {thumbnailPreviewUrl && (
              <img
                src={formatMediaUrl(thumbnailPreviewUrl)}
                alt="Thumbnail"
                className="max-h-40 w-auto rounded-lg border border-white/15"
              />
            )}
          </div>
        )}
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

      {/* Review / status panel */}
      {rowForSlot && (reviewFeedback || rowForSlot.status !== "draft") && (
        <Alert className={cn("mt-5 rounded-2xl", panelClassFor(rowForSlot.status), "border")}>
          {iconFor(rowForSlot.status)}
          <AlertTitle className="font-semibold">{titleFor(rowForSlot.status)}</AlertTitle>
          <AlertDescription className="whitespace-pre-wrap">
            {reviewFeedback
              ? reviewFeedback
              : rowForSlot.status === "submitted"
              ? "Your video is waiting for review."
              : rowForSlot.status === "approved"
              ? "This video has been approved."
              : rowForSlot.status === "draft"
              ? "Not submitted yet."
              : "Please update and resubmit this slot."}
          </AlertDescription>
        </Alert>
      )}

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
  );
}
