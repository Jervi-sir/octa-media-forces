import React from "react";
import { cn } from "@/lib/utils";
import { glass, ServerVideo } from "../media-helpers";

export function NumbersRow({ videos }: { videos: ServerVideo[] }) {
  const done = (videos ?? []).filter((r) => r.status === "approved" || r.status === "submitted").length;
  return (
    <div className={cn("flex items-center justify-between text-sm mb-5 px-4 py-3 rounded-2xl mx-6", glass)}>
      <div className="font-medium">Actual numbers :</div>
      <div className="flex items-center gap-6">
        <span className="opacity-90">
          Team: <span className="font-semibold">18</span>
        </span>
        <span className="opacity-90">
          Videos done: <span className="font-semibold">{done}</span>
        </span>
      </div>
    </div>
  );
}
