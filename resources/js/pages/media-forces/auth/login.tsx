import React, { useMemo, useState } from "react";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Separator } from "@/components/ui/separator";
import { cn } from "@/lib/utils";
import { Mail, Lock, User, Eye, EyeOff, ArrowRight, Loader2 } from "lucide-react";
import MediaAuthController from "@/actions/App/Http/Controllers/MediaForce/MediaAuthController";
import { useForm } from "@inertiajs/react";
import { ParallaxGlow } from "@/components/parallax-glow";

// Reuse glass styles from MediaForceScreen (paste if not globally shared)
const glass = "backdrop-blur-xl bg-white/3 border border-white/20 shadow-[0_1px_0_rgba(255,255,255,0.55),0_10px_30px_-12px_rgba(0,0,0,0.35)]";
const glassSoft = "backdrop-blur-xl bg-white/3 border border-white/15 shadow-[0_1px_0_rgba(255,255,255,0.55),0_8px_24px_-14px_rgba(0,0,0,0.30)]";

export default function AuthGlassScreen() {
  const { data, setData, submit, processing, errors, reset } = useForm({
    email: "",
    password: "",
  });
  const [mode, setMode] = useState<"login" | "register">("login");
  const [showPw, setShowPw] = useState(false);
  const [showPw2, setShowPw2] = useState(false);

  // form state (demo only)
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [password2, setPassword2] = useState("");
  const [fullName, setFullName] = useState("");
  const [loading, setLoading] = useState(false);

  const canSubmit = useMemo(() => {
    if (mode === "login") return data.email && data.password;
    return data.email && data.password;
    // return email && password && password2 && fullName;
  }, [mode, data]);

  const onSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    // Wire up to your auth endpoint here (Inertia/Laravel, etc.)
    if (mode === "login") {
      submit(MediaAuthController.login(), {
        onSuccess: () => setLoading(false),
        onError: () => setLoading(false),
      });
    } else {
      submit(MediaAuthController.register(), {
        onSuccess: () => setLoading(false),
        onError: () => setLoading(false),
      });
    }
    setLoading(false);
  };

  return (
    <div className="min-h-screen text-white relative overflow-hidden">
      {/* iOS 2025 gradient orbs */}
      <ParallaxGlow />

      {/* Centered auth card */}
      <div className="mx-auto flex min-h-screen w-full max-w-6xl items-center justify-center px-6 py-12">
        <div className="grid w-full max-w-5xl grid-cols-1 gap-6 md:grid-cols-2">
          {/* Left: Brand / Pitch */}
          <div className={cn("hidden md:flex flex-col justify-between rounded-3xl p-8", glass)}>
            <div>
              <div className="inline-flex items-baseline gap-2 rounded-2xl px-4 py-2 border border-white/20 bg-white/10">
                <span className="text-sm tracking-tight opacity-90">Media Force</span>
              </div>
              <h1 className="mt-6 text-3xl/tight font-semibold tracking-tight drop-shadow-sm">
                Welcome back 👋
              </h1>
              <p className="mt-2 text-white/80">
                Log in to upload, track approvals, and manage your videos with our new iOS‑style glass interface.
              </p>
            </div>
            <div className="mt-10 grid grid-cols-3 gap-3 text-center">
              <div className={cn("rounded-2xl p-4", glassSoft)}>
                <div className="text-2xl font-semibold">112</div>
                <div className="text-xs text-white/70">Videos done</div>
              </div>
              <div className={cn("rounded-2xl p-4", glassSoft)}>
                <div className="text-2xl font-semibold">18</div>
                <div className="text-xs text-white/70">Team</div>
              </div>
              <div className={cn("rounded-2xl p-4", glassSoft)}>
                <div className="text-2xl font-semibold">A+</div>
                <div className="text-xs text-white/70">Quality</div>
              </div>
            </div>
          </div>

          {/* Right: Form */}
          <div className={cn("rounded-3xl p-8", glass)}>
            {/* Mode toggle */}
            <div className="mb-6 inline-flex rounded-2xl border border-white/20 bg-white/10 p-1 backdrop-blur-xl">
              <button
                className={cn(
                  "px-4 py-2 text-sm rounded-xl transition-all",
                  mode === "login" ? "bg-white/80 text-black" : "text-white/80"
                )}
                onClick={() => setMode("login")}
              >
                Log in
              </button>
              <button
                className={cn(
                  "px-4 py-2 text-sm rounded-xl transition-all",
                  mode === "register" ? "bg-white/80 text-black" : "text-white/80"
                )}
                onClick={() => setMode("register")}
              >
                Register
              </button>
            </div>

            <form onSubmit={onSubmit} className="space-y-4">
              {/* {mode === "register" && (
                <div>
                  <label className="mb-1 block text-xs text-white/80">Full name</label>
                  <div className={cn("rounded-xl p-2", glassSoft)}>
                    <div className="flex items-center gap-2">
                      <User className="h-4 w-4 opacity-80" />
                      <Input
                        value={fullName}
                        onChange={(e) => setFullName(e.target.value)}
                        placeholder="e.g., Abdelmadjid Riah"
                        className="h-11 flex-1 bg-transparent border-0 text-white placeholder:text-white/50 focus-visible:ring-0"
                        autoComplete="name"
                      />
                    </div>
                  </div>
                </div>
              )} */}

              <div>
                <label className="mb-1 block text-xs text-white/80">Email</label>
                <div className={cn("rounded-xl p-2", glassSoft)}>
                  <div className="flex items-center gap-2 pl-2">
                    <Mail className="h-4 w-4 opacity-80" />
                    <Input
                      type="email"
                      value={data.email}
                      onChange={(e) => setData('email', e.target.value)}
                      placeholder="you@example.com"
                      className="h-11 flex-1 bg-transparent border-0 text-white placeholder:text-white/50 focus-visible:ring-0"
                      autoComplete="email"
                    />
                  </div>
                </div>
              </div>

              <div>
                <label className="mb-1 block text-xs text-white/80">Password</label>
                <div className={cn("rounded-xl p-2", glassSoft)}>
                  <div className="flex items-center gap-2 pl-2">
                    <Lock className="h-4 w-4 opacity-80" />
                    <Input
                      type={showPw ? "text" : "password"}
                      value={data.password}
                      onChange={(e) => setData('password', e.target.value)}
                      placeholder="••••••••"
                      className="h-11 flex-1 bg-transparent border-0 text-white placeholder:text-white/50 focus-visible:ring-0"
                      autoComplete={mode === "login" ? "current-password" : "new-password"}
                    />
                    <button
                      type="button"
                      onClick={() => setShowPw((s) => !s)}
                      className="p-2 rounded-lg hover:bg-white/10"
                    >
                      {showPw ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                    </button>
                  </div>
                </div>
              </div>
              {/* 
              {mode === "register" && (
                <div>
                  <label className="mb-1 block text-xs text-white/80">Confirm password</label>
                  <div className={cn("rounded-xl p-2", glassSoft)}>
                    <div className="flex items-center gap-2">
                      <Lock className="h-4 w-4 opacity-80" />
                      <Input
                        type={showPw2 ? "text" : "password"}
                        value={password2}
                        onChange={(e) => setPassword2(e.target.value)}
                        placeholder="••••••••"
                        className="h-11 flex-1 bg-transparent border-0 text-white placeholder:text-white/50 focus-visible:ring-0"
                        autoComplete="new-password"
                      />
                      <button
                        type="button"
                        onClick={() => setShowPw2((s) => !s)}
                        className="p-2 rounded-lg hover:bg-white/10"
                      >
                        {showPw2 ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                      </button>
                    </div>
                  </div>
                </div>
              )} */}

              {/* Submit */}
              <Button
                type="submit"
                disabled={!canSubmit}
                className={cn(
                  "w-full h-11 rounded-xl font-semibold",
                  "bg-white/80 hover:bg-white text-black",
                  !canSubmit && "opacity-60 cursor-not-allowed"
                )}
              >
                {
                  loading
                    ? <Loader2 className="animate-spin" />
                    :
                    mode === "login" ? (
                      <span className="inline-flex items-center gap-2">Log in <ArrowRight className="h-4 w-4" /></span>
                    ) : (
                      <span className="inline-flex items-center gap-2">Create account <ArrowRight className="h-4 w-4" /></span>
                    )
                }
              </Button>

              {/* Divider */}
              {/* <div className="flex items-center gap-3">
                <div className="h-px flex-1 bg-white/20" />
                <span className="text-xs text-white/60">or</span>
                <div className="h-px flex-1 bg-white/20" />
              </div> */}

              {/* Socials (placeholders) */}
              {/* <div className="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <button type="button" className={cn("h-11 rounded-xl border text-sm", "border-white/20 bg-white/10 hover:bg-white/20")}>Google</button>
                <button type="button" className={cn("h-11 rounded-xl border text-sm", "border-white/20 bg-white/10 hover:bg-white/20")}>Facebook</button>
                <button type="button" className={cn("h-11 rounded-xl border text-sm", "border-white/20 bg-white/10 hover:bg-white/20")}>Apple</button>
              </div> */}

              {/* Meta */}
              <p className="text-xs text-white/70">
                By continuing you agree to our <a href="#" className="underline hover:text-white">Terms</a> and <a href="#" className="underline hover:text-white">Privacy Policy</a>.
              </p>
            </form>
          </div>
        </div>
      </div>
    </div>
  );
}
