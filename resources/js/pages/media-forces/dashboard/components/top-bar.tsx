// resources/js/Pages/media/components/TopBar.tsx
import React from "react";
import { Globe, User2, ChevronDown, Check, LogOut } from "lucide-react";
import { cn } from "@/lib/utils";
import { glass } from "../media-helpers";
import { Button } from "@/components/ui/button";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuGroup,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { Link, router, useForm } from "@inertiajs/react";
import { useMobileNavigation } from "@/hooks/use-mobile-navigation";
import MediaAuthController from "@/actions/App/Http/Controllers/MediaForce/MediaAuthController";

type Lang = "en" | "fr" | "ar";

type Props = {
  nameOrEmail: string;
  currentLang?: Lang;
  onChangeLang?: (lang: Lang) => void;
  onProfile?: () => void;
  onSettings?: () => void;
  onLogout?: () => void;
};

export function TopBar({
  nameOrEmail,
  currentLang = "en",
  onChangeLang,
}: Props) {
  const langs: { code: Lang; label: string }[] = [
    { code: "en", label: "English" },
    { code: "fr", label: "Français" },
    { code: "ar", label: "العربية" },
  ];

  const { submit } = useForm();

  const cleanup = useMobileNavigation();
  const handleLogout = () => {
    cleanup();
    router.flushAll();
  };

  return (
    <header
      className={cn(
        "sticky top-0 z-30",
        "mx-auto flex w-full max-w-5xl items-center justify-between px-6 py-4",
        "flex-col gap-3 md:flex-row md:gap-0"
      )}
    >
      {/* Brand */}
      <div className={cn("px-4 py-2 rounded-2xl", glass)}>
        <div className="text-base font-semibold tracking-tight">Media Force</div>
      </div>

      {/* Actions: Language + Account */}
      <div
        className={cn(
          "flex items-center gap-3 text-sm",
          "px-3 py-1 rounded-2xl",
          glass
        )}
      >
        {/* Language dropdown */}
        <DropdownMenu>
          <DropdownMenuTrigger asChild>
            <Button
              variant="ghost"
              className="h-8 px-2 text-white/85 hover:text-white hover:bg-white/10"
            >
              <div className="flex items-center gap-2">
                <Globe className="h-4 w-4" />
                <span className="hidden sm:inline">Language</span>
                <ChevronDown className="h-4 w-4 opacity-70" />
              </div>
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent className="w-44 bg-zinc-800" align="start" sideOffset={8}>
            <DropdownMenuLabel>Language</DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuGroup>
              {langs.map((l) => (
                <DropdownMenuItem
                  key={l.code}
                  onClick={() => onChangeLang?.(l.code)}
                  className="flex items-center justify-between"
                >
                  <span dir={l.code === "ar" ? "rtl" : "ltr"}>{l.label}</span>
                  {currentLang === l.code ? (
                    <Check className="h-4 w-4 opacity-80" />
                  ) : null}
                </DropdownMenuItem>
              ))}
            </DropdownMenuGroup>
          </DropdownMenuContent>
        </DropdownMenu>

        <div className="h-5 w-px bg-white/20" />

        {/* Account dropdown */}
        <DropdownMenu>
          <DropdownMenuTrigger asChild>
            <Button
              variant="ghost"
              className="h-8 px-2 text-white/85 hover:text-white hover:bg-white/10"
            >
              <div className="flex items-center gap-2">
                <User2 className="h-4 w-4 opacity-90" />
                <span className="max-w-[100px] truncate sm:inline sm:max-w-[160px] ">{nameOrEmail}</span>
                <ChevronDown className="h-4 w-4 opacity-70" />
              </div>
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent className="w-56 bg-zinc-800" align="end" sideOffset={8}>
            <DropdownMenuLabel>{nameOrEmail}</DropdownMenuLabel>
            <Link className="block w-full" href={MediaAuthController.logout()} as="button" onClick={handleLogout}>
              <DropdownMenuItem className="text-red-400 focus:text-red-200" >
                Log out
              </DropdownMenuItem>
            </Link>
          </DropdownMenuContent>
        </DropdownMenu>
      </div>
    </header>
  );
}
