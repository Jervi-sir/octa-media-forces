import { type ClassValue, clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function formatMediaUrl(
  path: string | null | undefined,
  base = "http://localhost:8000/storage/"
): string {
  if (!path) return base;

  // If already an absolute URL, return as is
  if (/^https?:\/\//i.test(path)) {
    return path;
  }

  // Otherwise prefix with storage base
  return base.replace(/\/+$/, "") + "/" + path.replace(/^\/+/, "");
}
