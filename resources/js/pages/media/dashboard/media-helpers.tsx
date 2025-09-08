import { cn } from "@/lib/utils";

export type StepStatus = "current" | "success" | "failed" | "pending";

export type ServerVideo = {
  id: number;
  slot_number: number; // 1..11
  title?: string | null;
  description?: string | null;
  file_path?: string | null;
  public_url?: string | null;
  status: "draft" | "submitted" | "approved" | "changes_requested" | "rejected";
  submitted_at?: string | null;
  reviewed_at?: string | null;
  feedback?: string | null;
};

export type PageProps = {
  mediaForce: { id: number; name?: string | null; email: string };
  videos: ServerVideo[];
};

export const glass =
  "backdrop-blur-xl bg-white/10 border border-white/20 shadow-[0_1px_0_rgba(255,255,255,0.55),0_10px_30px_-12px_rgba(0,0,0,0.35)]";
export const glassSoft =
  "backdrop-blur-xl bg-white/8 border border-white/15 shadow-[0_1px_0_rgba(255,255,255,0.30)]";

export function mapStatusToUI(s: ServerVideo["status"]): StepStatus {
  switch (s) {
    case "approved":
      return "success";
    case "rejected":
    case "changes_requested":
      return "failed";
    case "submitted":
      return "pending";
    default:
      return "current";
  }
}

export function tileBg(status: StepStatus, isCurrent: boolean) {
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

export function statusColor(dbStatus: ServerVideo["status"]) {
  if (dbStatus === "submitted") return "bg-yellow-600 text-white";
  if (dbStatus === "approved") return "bg-green-700 text-white";
  if (dbStatus === "rejected" || dbStatus === "changes_requested") return "bg-red-600 text-white";
  return "bg-gray-100/20 text-white";
}

export const ENDPOINTS = {
  upload: "/media/videos/upload",
  store: "/media/videos/store",
};
