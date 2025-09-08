import React, { useCallback, useRef, useState } from "react";
import { UploadCloud } from "lucide-react";
import { cn } from "@/lib/utils";
import { glass } from "../media-helpers";

export function Dropzone({ onFiles }: { onFiles: (files: FileList) => void }) {
  const [dragOver, setDragOver] = useState(false);
  const fileRef = useRef<HTMLInputElement | null>(null);
  const handle = useCallback((e: React.DragEvent) => { e.preventDefault(); e.stopPropagation(); }, []);
  return (
    <div
      onDragEnter={(e) => { handle(e); setDragOver(true); }}
      onDragOver={handle}
      onDragLeave={(e) => { handle(e); setDragOver(false); }}
      onDrop={(e) => { handle(e); setDragOver(false); if (e.dataTransfer.files?.length) onFiles(e.dataTransfer.files); }}
      className={cn("group relative flex h-24 w-full cursor-pointer items-center justify-center rounded-2xl transition-all", glass, dragOver ? "ring-2 ring-white/50" : "ring-0")}
      onClick={() => fileRef.current?.click()}
    >
      <input ref={fileRef} type="file" accept="video/*" className="hidden"
             onChange={(e) => e.target.files && onFiles(e.target.files)} />
      <div className="flex items-center gap-2 text-sm text-white/80">
        <UploadCloud className="h-4 w-4" />
        <span>Drop or select a video</span>
      </div>
      <div className="pointer-events-none absolute inset-0 rounded-2xl bg-gradient-to-br from-white/10 to-transparent opacity-0 transition-opacity group-hover:opacity-100" />
    </div>
  );
}
