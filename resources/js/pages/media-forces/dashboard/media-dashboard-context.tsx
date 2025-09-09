import React, {
  createContext,
  useCallback,
  useEffect,
  useMemo,
  useRef,
  useState,
} from "react";
import { router, usePage } from "@inertiajs/react";
import axios from "axios";
import {
  ENDPOINTS,
  PageProps,
  ServerVideo,
  StepStatus,
  mapStatusToUI,
} from "./media-helpers";

/** ---------------- Types ---------------- */
type SlotVM = {
  id: string;
  slot: number;
  label: string;
  dbStatus: ServerVideo["status"];
  uiStatus: StepStatus;
  file_path: string | null;
  thumbnail_path: string | null;
  title: string | null;
  server: ServerVideo | null;
};

type Draft = {
  /** Selected file(s) for this slot (in-memory only) */
  files: File[];
  /** Draft title shown in UI */
  storeTitle: string;
  /** User edited the title at least once */
  titleDirty: boolean;
  /** Upload progress for this slot (0..100) */
  uploadProgress: number | null;
  /** Result of successful upload for this slot */
  uploaded: { file_path: string; thumbnail_path: string | null } | null;
};

type Ctx = {
  mediaForce: PageProps["mediaForce"];
  videos: ServerVideo[];
  slots: ReadonlyArray<SlotVM>;
  activeIdx: number;
  setActiveIdx: (idx: number) => void;
  active: SlotVM;

  files: File[];
  setFiles: (f: File[]) => void;
  uploadProgress: number | null;
  uploaded: { file_path: string; thumbnail_path: string | null } | null;

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

/** ---------------- Persistence (sessionStorage) ---------------- */
const DRAFTS_KEY = "media_drafts_v2";

type PersistShape = Omit<Draft, "files">;
type PersistMap = Record<string, PersistShape>;

const safeParse = <T,>(raw: string | null, fallback: T): T => {
  try {
    return raw ? (JSON.parse(raw) as T) : fallback;
  } catch {
    return fallback;
  }
};

const loadPersisted = (): PersistMap =>
  typeof window === "undefined"
    ? {}
    : safeParse<PersistMap>(sessionStorage.getItem(DRAFTS_KEY), {});

/** Debounced writer so we don't thrash storage on every keystroke */
function useDebouncedPersist(draftsRef: React.MutableRefObject<Record<string, Draft>>) {
  const timer = useRef<number | null>(null);
  const persistNow = useCallback(() => {
    const serializable: PersistMap = {};
    for (const [k, v] of Object.entries(draftsRef.current)) {
      serializable[k] = {
        storeTitle: v.storeTitle,
        titleDirty: v.titleDirty,
        uploadProgress: v.uploadProgress,
        uploaded: v.uploaded,
      };
    }
    try {
      sessionStorage.setItem(DRAFTS_KEY, JSON.stringify(serializable));
    } catch {
      /* ignore */
    }
  }, [draftsRef]);

  const schedule = useCallback(() => {
    if (timer.current) window.clearTimeout(timer.current);
    timer.current = window.setTimeout(persistNow, 200);
  }, [persistNow]);

  useEffect(() => () => timer.current && window.clearTimeout(timer.current), []);

  return schedule;
}

/** ---------------- Provider ---------------- */
export function MediaDashboardProvider({ children }: { children: React.ReactNode }) {
  const { props } = usePage<PageProps>();
  const { mediaForce, videos } = props;

  /** Build slots (server → UI model) */
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
        file_path: v?.file_path ?? null,
        thumbnail_path: v?.thumbnail_path ?? null,
        title: v?.title ?? null,
        server: v ?? null,
      } as const;
    });
    const firstIdx = arr.findIndex(
      (s) => s.dbStatus === "draft" || s.dbStatus === "changes_requested"
    );
    if (firstIdx >= 0) arr[firstIdx] = { ...arr[firstIdx], uiStatus: "current" };
    return arr;
  }, [videos]);

  /** Active tab */
  const [activeIdx, setActiveIdx] = useState<number>(() =>
    Math.max(0, slots.findIndex((s) => s.uiStatus === "current"))
  );
  useEffect(() => {
    const idx = Math.max(0, slots.findIndex((s) => s.uiStatus === "current"));
    setActiveIdx(idx >= 0 ? idx : 0);
  }, [slots]);
  const active = slots[activeIdx] ?? slots[0];

  /** Per-slot drafts (single source of truth) */
  const [drafts, setDrafts] = useState<Record<string, Draft>>({});
  const draftsRef = useRef(drafts);
  draftsRef.current = drafts;

  const schedulePersist = useDebouncedPersist(draftsRef);
  const persisted = useRef(loadPersisted());

  /** Initialize drafts once per slot id; DO NOT overwrite existing drafts */
  useEffect(() => {
    setDrafts((prev) => {
      const next = { ...prev };
      for (const s of slots) {
        if (!next[s.id]) {
          const seedFromServer: Draft = {
            files: [],
            storeTitle:
              // if we have a persisted value, prefer it
              persisted.current[s.id]?.storeTitle ??
              s.title ??
              "",
            titleDirty: persisted.current[s.id]?.titleDirty ?? false,
            uploadProgress: persisted.current[s.id]?.uploadProgress ?? null,
            uploaded: persisted.current[s.id]?.uploaded ?? null,
          };
          next[s.id] = seedFromServer;
        }
      }
      // Do not delete drafts for slots that disappeared; safe to keep
      return next;
    });
  }, [slots.map((s) => s.id).join("|")]);

  /** Upload controllers per slot to avoid cross-cancellation */
  const uploadControllers = useRef<Record<string, AbortController | null>>({});

  /** Helpers to read/write the **current** slot draft */
  const readActiveDraft = (): Draft => draftsRef.current[active.id]!;
  const patchActiveDraft = useCallback((patch: Partial<Draft>) => {
    setDrafts((prev) => {
      const cur = prev[active.id]!;
      const merged = { ...cur, ...patch };
      const next = { ...prev, [active.id]: merged };
      draftsRef.current = next;
      schedulePersist();
      return next;
    });
  }, [active.id, schedulePersist]);

  /** Context fields (derived directly from the draft — no mirror states) */
  const files = readActiveDraft()?.files ?? [];
  const uploadProgress = readActiveDraft()?.uploadProgress ?? null;
  const uploaded = readActiveDraft()?.uploaded ?? null;
  const storeTitle = readActiveDraft()?.storeTitle ?? "";
  const titleDirty = readActiveDraft()?.titleDirty ?? false;

  /** Setters update the single source of truth */
  const setFiles = useCallback((f: File[]) => {
    patchActiveDraft({ files: f });
    // Start upload immediately when files set
    if (!f?.length) {
      // If user cleared selection, also clear progress for this slot
      patchActiveDraft({ uploadProgress: null, uploaded: null });
      return;
    }
    const file = f[0];

    // Cancel previous upload for this slot if any
    uploadControllers.current[active.id]?.abort();
    const ac = new AbortController();
    uploadControllers.current[active.id] = ac;

    // Reset progress & uploaded atomically
    patchActiveDraft({ uploadProgress: 0, uploaded: null });

    const run = async () => {
      const form = new FormData();
      form.append("file", file);
      try {
        const { data } = await axios.post(ENDPOINTS.upload, form, {
          signal: ac.signal,
          headers: { "Content-Type": "multipart/form-data" },
          onUploadProgress: (evt) => {
            if (!evt.total) return;
            const pct = Math.max(0, Math.min(100, Math.round((evt.loaded / evt.total) * 100)));
            // Guard against late events after slot switch
            patchActiveDraft((draftsRef.current[active.id] ? { uploadProgress: pct } : {}) as Partial<Draft>);
          },
        });
        // Only commit if this controller is still current for the same slot
        if (uploadControllers.current[active.id] === ac) {
          patchActiveDraft({
            uploadProgress: 100,
            uploaded: { file_path: data.file_path, thumbnail_path: data.thumbnail_path },
          });
        }
      } catch (e: any) {
        if (e?.name === "CanceledError" || e?.code === "ERR_CANCELED") return;
        console.error(e);
        // Only clear if still current controller
        if (uploadControllers.current[active.id] === ac) {
          patchActiveDraft({ uploadProgress: null, uploaded: null });
          alert("Upload failed. Please try again.");
        }
      }
    };
    run();
  }, [active.id, patchActiveDraft]);

  const setStoreTitle = useCallback((s: string) => {
    patchActiveDraft({ storeTitle: s, titleDirty: true });
  }, [patchActiveDraft]);

  const setTitleDirty = useCallback((b: boolean) => {
    patchActiveDraft({ titleDirty: b });
  }, [patchActiveDraft]);

  /** Navigation */
  const next = useCallback(
    () => setActiveIdx((i) => Math.min(slots.length - 1, i + 1)),
    [slots.length]
  );
  const prev = useCallback(() => setActiveIdx((i) => Math.max(0, i - 1)), []);
  const goTo = useCallback(
    (idx: number) => setActiveIdx(Math.max(0, Math.min(idx, slots.length - 1))),
    [slots.length]
  );

  /** Keyboard navigation */
  useEffect(() => {
    const onKey = (e: KeyboardEvent) => {
      if (e.key === "ArrowRight") next();
      if (e.key === "ArrowLeft") prev();
    };
    window.addEventListener("keydown", onKey);
    return () => window.removeEventListener("keydown", onKey);
  }, [next, prev]);

  /** If videos change, seed title only for non-dirty drafts that are empty */
  useEffect(() => {
    setDrafts((prev) => {
      let mutated = false;
      const next = { ...prev };
      for (const s of slots) {
        const d = next[s.id];
        if (!d) continue;
        if (!d.titleDirty && (!d.storeTitle || d.storeTitle.trim() === "")) {
          next[s.id] = { ...d, storeTitle: s.title ?? "" };
          mutated = true;
        }
      }
      if (mutated) {
        draftsRef.current = next;
        schedulePersist();
      }
      return mutated ? next : prev;
    });
  }, [slots, schedulePersist]);

  /** Helpers */
  const refreshVideos = () => router.reload({ only: ["videos"] });

  const [saving, setSaving] = useState(false);

  const handleClear = () => {
    // Cancel ongoing upload for this slot
    uploadControllers.current[active.id]?.abort();
    uploadControllers.current[active.id] = null;

    patchActiveDraft({
      files: [],
      uploadProgress: null,
      uploaded: null,
      storeTitle: active.title ?? "",
      titleDirty: false,
    });
  };

  const handleDone = async () => {
    const d = readActiveDraft();
    if (!d.uploaded) {
      alert("Please upload a video first.");
      return;
    }
    setSaving(true);
    try {
      const payload = {
        slot_number: active.slot,
        title: d.storeTitle || active.title || `Video ${active.slot}`,
        description: "",
        file_path: d.uploaded.file_path,
        thumbnail_path: d.uploaded.thumbnail_path,
        status: "submitted" as const,
      };
      await axios.post(ENDPOINTS.store, payload);

      // Clear draft for this slot after submit
      setDrafts((prev) => {
        const cur = prev[active.id]!;
        const next = {
          ...prev,
          [active.id]: {
            ...cur,
            files: [],
            uploadProgress: null,
            uploaded: null,
            // keep the title for the tab label UX; mark not dirty (server owns it now)
            titleDirty: false,
          },
        };
        draftsRef.current = next;
        schedulePersist();
        return next;
      });

      refreshVideos();
    } catch (e) {
      console.error(e);
      alert("Save failed. Please try again.");
    } finally {
      setSaving(false);
    }
  };

  /** Provide context */
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
