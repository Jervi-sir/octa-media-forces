import React from "react";
import { Gift } from "lucide-react";
import { cn } from "@/lib/utils";
import { glass } from "../media-helpers";

export function RewardCTA() {
  return (
    <div className={cn("mt-6 rounded-3xl p-8 text-center mx-6", glass)}>
      <div className="flex items-center justify-center gap-2 text-white/90">
        <Gift className="h-5 w-5" />
        <span className="text-sm">Claim Reward (280000da) code to OGM</span>
      </div>
    </div>
  );
}
