import React, { createContext, useCallback, useEffect, useMemo, useRef, useState } from "react";
import { Head, router, usePage } from "@inertiajs/react";
import axios from "axios";
import { ENDPOINTS, PageProps, ServerVideo, StepStatus, mapStatusToUI } from "./media-helpers";

type SlotVM = {
  id: string;
  slot: number;
  label: string;
  dbStatus: ServerVideo["status"];
  uiStatus: StepStatus;
  public_url: string | null;
  title: string | null;
  server: ServerVideo | null;
};

type Ctx = {
  mediaForce: PageProps["mediaForce"];
  videos: ServerVideo[];
  slots: ReadonlyArray<SlotVM>;
  activeIdx: number;
  setActiveIdx: (idx: number) => void;
  active: SlotVM;

  // Upload/title state – scoped to active slot
  files: File[];
  setFiles: (f: File[]) => void;
  uploadProgress: number | null;
  uploaded: { file_path: string; public_url: string } | null;

  storeTitle: string;
  setStoreTitle: (s: string) => void;
  titleDirty: boolean;
  setTitleDirty: (b: boolean) => void;

  saving: boolean;
  handleClear: () => void;
  handleDone: () => Promise<void>;
  next: () => void;
  prev: () => void;
  goTo: (idx: number) => void;
  refreshVideos: () => void;
};

export const MediaDashboardContext = createContext<Ctx | null>(null);

export function MediaDashboardProvider({ children }: { children: React.ReactNode }) {
  const { props } = usePage<PageProps>();
  const { mediaForce, videos } = props;

  const slots = useMemo<ReadonlyArray<SlotVM>>(() => {
    const bySlot = new Map<number, ServerVideo>((videos ?? []).map((v) => [v.slot_number, v]));
    const arr = Array.from({ length: 11 }, (_, i) => {
      const slot = i + 1;
      const v = bySlot.get(slot);
      const dbStatus: ServerVideo["status"] = v?.status ?? "draft";
      const uiStatus = mapStatusToUI(dbStatus);
      return {
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
    const firstIdx = arr.findIndex((s) => s.dbStatus === "draft" || s.dbStatus === "changes_requested");
    if (firstIdx >= 0) arr[firstIdx] = { ...arr[firstIdx], uiStatus: "current" };
    return arr;
  }, [videos]);

  const [activeIdx, setActiveIdx] = useState<number>(() => Math.max(0, slots.findIndex((s) => s.uiStatus === "current")));
  useEffect(() => {
    const idx = Math.max(0, slots.findIndex((s) => s.uiStatus === "current"));
    setActiveIdx(idx >= 0 ? idx : 0);
  }, [slots]);
  const active = slots[activeIdx] ?? slots[0];

  // Upload / title state (active slot only)
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

  // Keyboard navigation
  useEffect(() => {
    const onKey = (e: KeyboardEvent) => {
      if (e.key === "ArrowRight") next();
      if (e.key === "ArrowLeft") prev();
    };
    window.addEventListener("keydown", onKey);
    return () => window.removeEventListener("keydown", onKey);
  }, [next, prev]);

  // Upload whenever files changes (current slot)
  useEffect(() => {
    const run = async () => {
      if (!files.length) return;
      const file = files[0];

      setUploadProgress(0);
      setUploaded(null);
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
            setUploadProgress(Math.round((evt.loaded / evt.total) * 100));
          },
        });
        setUploaded({ file_path: data.file_path, public_url: data.public_url });
      } catch (e: any) {
        if (e?.name === "CanceledError" || e?.code === "ERR_CANCELED") return;
        console.error(e);
        setUploadProgress(null);
        setUploaded(null);
        alert("Upload failed. Please try again.");
      }
    };
    run();
    return () => abortRef.current?.abort();
  }, [files]);

  // Reset slot-local UI state on active change
  useEffect(() => {
    abortRef.current?.abort();
    setFiles([]);
    setUploadProgress(null);
    setUploaded(null);
    setStoreTitle(active.title ?? "");
    setTitleDirty(false);
  }, [activeIdx, active.title]);

  // If videos prop refreshes and title not dirty, refresh title from server
  useEffect(() => {
    if (!titleDirty) setStoreTitle(active.title ?? "");
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [videos, active.id, titleDirty]);

  const refreshVideos = () => router.reload({ only: ["videos"] });

  const handleClear = () => {
    abortRef.current?.abort();
    setFiles([]);
    setUploadProgress(null);
    setUploaded(null);
    setStoreTitle(active.title ?? "");
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
      refreshVideos();
      // alert("Submitted! We will review it shortly.");
    } catch (e) {
      console.error(e);
      alert("Save failed. Please try again.");
    } finally {
      setSaving(false);
    }
  };

  return (
    <MediaDashboardContext.Provider
      value={{
        mediaForce,
        videos,
        slots,
        activeIdx,
        setActiveIdx,
        active,

        files,
        setFiles,
        uploadProgress,
        uploaded,

        storeTitle,
        setStoreTitle,
        titleDirty,
        setTitleDirty,

        saving,
        handleClear,
        handleDone,
        next,
        prev,
        goTo,
        refreshVideos,
      }}
    >
      {children}
    </MediaDashboardContext.Provider>
  );
}
