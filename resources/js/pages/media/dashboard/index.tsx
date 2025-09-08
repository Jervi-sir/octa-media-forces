import React from "react";
import { usePage, Head } from "@inertiajs/react";
import type { PageProps } from "./media-helpers";
import { MediaDashboardProvider } from "./media-dashboard-context";
import { TopBar } from "./components/top-bar";
import { NumbersRow } from "./components/numbers-row";
import { VideoTabs } from "./components/video-tabs";
import { MainPanel } from "./components/main-panel";
import { RewardCTA } from "./components/reward-cta";

export default function MediaDashboard() {
  const { props } = usePage<PageProps>();
  const { mediaForce, videos } = props;

  return (
    <div className="min-h-screen text-white relative overflow-hidden font-mono">
      <Head title="Media Dashboard" />

      {/* Background */}
      <div className="pointer-events-none absolute inset-0">
        <div className="absolute -top-40 -left-32 h-[520px] w-[520px] rounded-full bg-[radial-gradient(closest-side,rgba(120,119,198,0.45),rgba(120,119,198,0)_70%)] blur-2xl" />
        <div className="absolute -bottom-32 -right-24 h-[520px] w-[520px] rounded-full bg-[radial-gradient(closest-side,rgba(56,189,248,0.45),rgba(56,189,248,0)_70%)] blur-2xl" />
        <div className="absolute inset-0 bg-[linear-gradient(120deg,rgba(255,255,255,0.08),rgba(255,255,255,0.02))]" />
      </div>

      <MediaDashboardProvider>
        <TopBar
          nameOrEmail={mediaForce?.name || mediaForce.email}
          // currentLang={page.props.locale as "en" | "fr" | "ar" ?? "en"}
          onChangeLang={(lang) => { }}
        />

        <div className="mx-auto w-full max-w-5xl">
          <NumbersRow videos={videos ?? []} />
          <VideoTabs />
          <MainPanel />
          <RewardCTA />
        </div>
        <footer className="py-12" />
      </MediaDashboardProvider>
    </div>
  );
}
