import type { ComponentType } from "react";
import { CoupleSection, FooterSection, HomeSection } from "@/components/invitation/sections";
import {
  HonoreeFooterSection,
  HonoreeHomeSection,
  HonoreeSection,
} from "@/components/invitation/honoree-sections";

type HeroComponents = {
  Home: ComponentType;
  People: ComponentType;
  Footer: ComponentType;
};

// Categories celebrating 1 (or N) individual honorees rather than a couple — everything
// else (wedding, anniversary, and any future/unlisted category) falls back to the
// bride/groom-shaped Couple template, since a couple is the closest fit for two people.
const HONOREE_CATEGORIES = new Set(["birthday", "khitan", "aqiqah", "graduation", "corporate"]);

export function isHonoreeCategory(eventCategory: string): boolean {
  return HONOREE_CATEGORIES.has(eventCategory);
}

/** The only 3 sections that structurally assume exactly 2 named people (bride/groom) —
 * every other section (Event/Gallery/LoveStory/Gift/RSVP) is already category-agnostic
 * and stays identical across every `event_category`, including ones not listed here
 * (custom and any future category keep today's wedding-shaped fallback). */
export function resolveHeroComponents(eventCategory: string): HeroComponents {
  if (isHonoreeCategory(eventCategory)) {
    return { Home: HonoreeHomeSection, People: HonoreeSection, Footer: HonoreeFooterSection };
  }

  return { Home: HomeSection, People: CoupleSection, Footer: FooterSection };
}

const HERO_TAB_LABELS: Record<string, string> = {
  khitan: "Yang Dikhitan",
  birthday: "Yang Berulang Tahun",
  aqiqah: "Yang Diaqiqah",
  graduation: "Yang Diwisuda",
  corporate: "Tuan Rumah Acara",
  anniversary: "Pasangan Kami",
};

export function heroTabLabel(eventCategory: string): string {
  return HERO_TAB_LABELS[eventCategory] ?? "Mempelai";
}
