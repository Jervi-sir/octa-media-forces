import * as React from "react";
import { Head, router, useForm } from "@inertiajs/react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Textarea } from "@/components/ui/textarea";
import { Button } from "@/components/ui/button";
import { Label } from "@/components/ui/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { AdminLayout } from "../layout/admin-layout";
import MediaForceController from "@/actions/App/Http/Controllers/Admin/MediaForceController";
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";

type Force = { id: number; name: string | null; email: string };
type Video = {
  id: number;
  slot_number: number;
  title: string | null;
  description: string | null;
  file_path: string | null;
  thumbnail_path: string | null;
  duration_seconds: number | null;
  status: "draft" | "submitted" | "approved" | "changes_requested" | "rejected";
  review_feedback: string | null;
  submitted_at: string | null;
  reviewed_at: string | null;
};

export default function Show({
  force,
  videos,
  statusOptions,
}: {
  force: Force;
  videos: Video[];
  statusOptions: string[];
}) {
  return (
    <AdminLayout>
      <Head title={`Media Force #${force.id}`} />
      <div className="px-6 py-6 space-y-6">
        <div>
          <h1 className="text-2xl font-semibold">Media Force #{force.id}</h1>
          <p className="text-sm text-muted-foreground">{force.name ?? "—"} · {force.email}</p>
        </div>

        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
          {videos.map((v) => (
            <VideoCard key={v.id} video={v} statusOptions={statusOptions} />
          ))}
        </div>
      </div>
    </AdminLayout>
  );
}

function VideoCard({ video, statusOptions }: { video: Video; statusOptions: string[] }) {
  const { data, setData, processing } = useForm({
    status: video.status,
    review_feedback: video.review_feedback ?? "",
  });

  const [previewOpen, setPreviewOpen] = React.useState(false);

  const onSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    router.patch(MediaForceController.update({ mediaForceVideo: video.id }).url, data, {
      preserveScroll: true,
      preserveState: true,
      only: ["videos"],
    });
  };

  return (
    <>
      <Card className="flex flex-col">
        <CardHeader>
          <CardTitle className="flex items-center justify-between">
            <span>Slot {video.slot_number}</span>
            {typeof video.duration_seconds === "number" ? (
              <span className="text-xs text-muted-foreground">{formatDuration(video.duration_seconds)}</span>
            ) : null}
          </CardTitle>
        </CardHeader>

        <CardContent className="space-y-3">
          {/* Preview area (thumbnail or “No file”) */}
          <div className="relative rounded-xl overflow-hidden border">
            {!video.file_path ? (
              <div className="w-full aspect-video grid place-items-center text-sm text-muted-foreground">
                No file
              </div>
            ) : (
              <div className="relative">
                {video.thumbnail_path ? (
                  <img
                    src={video.thumbnail_path}
                    alt={`thumb-${video.id}`}
                    className="w-full aspect-video object-cover"
                    loading="lazy"
                  />
                ) : (
                  <div className="w-full aspect-video grid place-items-center text-sm text-muted-foreground">
                    No preview
                  </div>
                )}

                {/* Overlay preview button */}
                <div className="absolute inset-0 flex items-center justify-center bg-black/30">
                  <Button
                    type="button"
                    size="sm"
                    onClick={() => setPreviewOpen(true)}
                    className="rounded-full px-4 py-2 shadow-md"
                  >
                    Preview
                  </Button>
                </div>
              </div>
            )}
          </div>

          <div className="space-y-1">
            <div className="text-sm font-medium">{video.title ?? `Video ${video.slot_number}`}</div>
            {video.description ? (
              <div className="text-xs text-muted-foreground line-clamp-3">{video.description}</div>
            ) : null}
          </div>

          {/* Review form */}
          <form onSubmit={onSubmit} className="space-y-3">
            <div className="space-y-1">
              <Label>Status</Label>
              <Select value={data.status} onValueChange={(val) => setData("status", val as any)}>
                <SelectTrigger className="w-full">
                  <SelectValue placeholder="Select status" />
                </SelectTrigger>
                <SelectContent>
                  {statusOptions.map((s) => (
                    <SelectItem key={s} value={s}>
                      {labelize(s)}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>

            <div className="space-y-1">
              <Label>Feedback</Label>
              <Textarea
                value={data.review_feedback ?? ""}
                onChange={(e) => setData("review_feedback", e.target.value)}
                placeholder="Write feedback for the creator…"
                rows={4}
              />
            </div>

            <div className="flex items-center justify-between text-xs text-muted-foreground">
              <div>
                {video.submitted_at && <>Submitted: {video.submitted_at}</>}
                {video.reviewed_at && (
                  <>
                    <br />
                    Reviewed: {video.reviewed_at}
                  </>
                )}
              </div>
              <Button type="submit" disabled={processing} size="sm">
                {processing ? "Saving…" : "Save"}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>

      {/* Preview Modal */}
      <Dialog open={previewOpen} onOpenChange={setPreviewOpen}>
        <DialogContent className="max-w-4xl p-0 overflow-hidden">
          <DialogHeader className="px-6 pt-6">
            <DialogTitle>
              {video.title ?? `Video ${video.slot_number}`}{" "}
              {typeof video.duration_seconds === "number" ? (
                <span className="ml-2 text-xs font-normal text-muted-foreground">
                  {formatDuration(video.duration_seconds)}
                </span>
              ) : null}
            </DialogTitle>
          </DialogHeader>

          <div className="px-6 pb-6">
            {/* If you have <AspectRatio>, wrap the video with <AspectRatio ratio={16/9}> */}
            <div className="w-full">
              <h1>{video.id}</h1>
              {video.file_path ? (
                <video
                  src={MediaForceController.stream({ mediaForceVideo: video.id }).url}
                  controls
                  playsInline
                  preload="metadata"
                  poster={video.thumbnail_path ?? undefined}
                  className="w-full h-auto rounded-lg"
                  autoPlay
                />
              ) : (
                <div className="w-full aspect-video grid place-items-center text-sm text-muted-foreground">
                  No file
                </div>
              )}
            </div>
          </div>
        </DialogContent>
      </Dialog>
    </>
  );
}

/* helpers (same as your originals) */
function labelize(s: string) {
  return s.replace(/_/g, " ").replace(/\b\w/g, (m) => m.toUpperCase());
}
function formatDuration(sec: number) {
  const m = Math.floor(sec / 60);
  const s = sec % 60;
  return `${m}:${s.toString().padStart(2, "0")}`;
}