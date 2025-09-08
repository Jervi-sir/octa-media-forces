import React from "react";
import { usePage, Head } from "@inertiajs/react";
import type { PageProps } from "./media-helpers";
import { MediaDashboardProvider } from "./media-dashboard-context";
import { TopBar } from "./components/top-bar";
import { NumbersRow } from "./components/numbers-row";
import { VideoTabs } from "./components/video-tabs";
import { MainPanel } from "./components/main-panel";
import { RewardCTA } from "./components/reward-cta";
import { ParallaxGlow } from "@/components/parallax-glow";
import { AnimatedSection } from "@/components/animated-section";


export default function MediaDashboard() {
  const { props } = usePage<PageProps>();
  const { mediaForce, videos } = props;

  return (
    <div className="min-h-screen text-white relative overflow-hidden font-mono">
      <Head title="Media Dashboard" />

      {/* Smooth parallax background */}
      <ParallaxGlow />

      <MediaDashboardProvider>
        <AnimatedSection direction="up" delay={0.1}>
          <TopBar
            nameOrEmail={mediaForce?.name || mediaForce.email}
            onChangeLang={() => { }}
          />
        </AnimatedSection>

        <div className="mx-auto w-full max-w-5xl space-y-8">
          <AnimatedSection direction="up" delay={0.2}>
            <NumbersRow videos={videos ?? []} />
          </AnimatedSection>

          <AnimatedSection direction="up" delay={0.3}>
            <VideoTabs />
          </AnimatedSection>

          <AnimatedSection direction="up" delay={0.4}>
            <MainPanel />
          </AnimatedSection>

          <AnimatedSection direction="up" delay={0.55}>
            <RewardCTA />
          </AnimatedSection>
        </div>

        <footer className="py-12" />
      </MediaDashboardProvider>
    </div>
  );
}
