---
name: design-taste-frontend
description: Anti-slop frontend skill for landing pages, portfolios, and redesigns. The agent reads the brief, infers the right design direction, and ships interfaces that do not look templated. Real design systems when applicable, audit-first on redesigns, strict pre-flight check.
---

# tasteskill: Anti-Slop Frontend Skill

> Landing pages, portfolios, and redesigns. Not dashboards, not data tables, not multi-step product UI.
> Every rule below is **contextual**. None of it fires automatically. First read the brief, then pull only what fits.

---

## 0. BRIEF INFERENCE (Read the Room Before Anything Else)

Before touching code or tweaking dials, **infer what the user actually wants**. Most LLM design output is bad because the model jumps to a default aesthetic instead of reading the room.

### 0.A Read these signals first
1. **Page kind** - landing (SaaS / consumer / agency / event), portfolio (dev / designer / creative studio), redesign (preserve vs overhaul), editorial / blog.
2. **Vibe words** the user used - "minimalist", "calm", "Linear-style", "Awwwards", "brutalist", "premium consumer", "Apple-y", "playful", "serious B2B", "editorial", "agency-y", "glassy", "dark tech".
3. **Reference signals** - URLs they linked, screenshots they pasted, products they named, brands they're competing with.
4. **Audience** - B2B procurement panel vs. design-conscious consumer vs. recruiter scanning a portfolio. The audience picks the aesthetic, not your taste.
5. **Brand assets that already exist** - logo, color, type, photography. For redesigns, these are starting material, not optional input (see Section 11).
6. **Quiet constraints** - accessibility-first audiences, public-sector, regulated industries, trust-first commerce, kids' products. These constraints OVERRIDE aesthetic preference.

### 0.B Output a one-line "Design Read" before generating
Before any code, state in one line: **"Reading this as: \<page kind> for \<audience>, with a \<vibe> language, leaning toward \<design system or aesthetic family>."**

Example reads:
- *"Reading this as: B2B SaaS landing for technical buyers, with a Linear-style minimalist language, leaning toward Tailwind utilities + Geist + restrained motion."*
- *"Reading this as: solo designer portfolio for hiring managers, with an editorial / kinetic-type language, leaning toward native CSS + scroll-driven animation + custom typography."*
- *"Reading this as: redesign of a public-sector service site, with a trust-first language, leaning toward GOV.UK Frontend or USWDS."*

### 0.C If the brief is ambiguous, ask one question, do not guess
Ask exactly **one** clarifying question - never a multi-question dump - and only when the design read genuinely diverges. Example: *"Should this feel closer to Linear-clean or Awwwards-experimental?"*

If you can confidently infer from context, **do not ask**. Just declare the design read and proceed.

### 0.D Anti-Default Discipline
Do not default to: AI-purple gradients, centered hero over dark mesh, three equal feature cards, generic glassmorphism on everything, infinite-loop micro-animations everywhere, Inter + slate-900. These are the LLM defaults. Reach past them deliberately based on the design read.

---

## 1. THE THREE DIALS (Core Configuration)

After the design read, set three dials. Every layout, motion, and density decision below is gated by these.

* **`DESIGN_VARIANCE: 8`** - 1 = Perfect Symmetry, 10 = Artsy Chaos
* **`MOTION_INTENSITY: 6`** - 1 = Static, 10 = Cinematic / Physics
* **`VISUAL_DENSITY: 4`** - 1 = Art Gallery / Airy, 10 = Cockpit / Packed Data

**Baseline:** `8 / 6 / 4`. Use these unless the design read overrides them. Do not ask the user to edit this file - overrides happen conversationally.

### 1.A Dial Inference (design read → dial values)
| Signal | VARIANCE | MOTION | DENSITY |
|---|---|---|---|
| "minimalist / clean / calm / editorial / Linear-style" | 5-6 | 3-4 | 2-3 |
| "premium consumer / Apple-y / luxury / brand" | 7-8 | 5-7 | 3-4 |
| "playful / wild / Dribbble / Awwwards / experimental / agency" | 9-10 | 8-10 | 3-4 |
| "landing page / portfolio / marketing site (default)" | 7-9 | 6-8 | 3-5 |
| "trust-first / public-sector / regulated / accessibility-critical" | 3-4 | 2-3 | 4-5 |
| "redesign - preserve" | match existing | +1 | match existing |
| "redesign - overhaul" | +2 | +2 | match existing |

---

## 2. BRIEF → DESIGN SYSTEM MAP

Once you have the design read (Section 0) and dials (Section 1), pick the right foundation. Do not invent CSS for things that have an official package. Do not pretend an aesthetic trend is an official system.

### 2.A When to reach for a real design system (use official packages)
| Brief reads as… | Reach for | Why |
|---|---|---|
| Microsoft / enterprise SaaS / dashboards | `@fluentui/react-components` or `@fluentui/web-components` | Official Fluent UI, Microsoft tokens, accessibility done |
| Google-ish UI, Material-flavored product | `@material/web` + Material 3 tokens | Official, theme-able via Material Theming |
| IBM-style B2B / enterprise analytics | `@carbon/react` + `@carbon/styles` | Official Carbon, mature data-density patterns |
| Public-sector UK / US service | `govuk-frontend` / `uswds` | Legally / regulatorily expected |
| Fast local-business / enterprise portal | Bootstrap 5.3 | Boring, fast, works |
| Modern accessible React foundation | `@radix-ui/themes` | Primitives + polished theme |
| Modern SaaS where you own the components | shadcn/ui | You own the code, easy to customise; never ship default state |
| Tailwind-based modern SaaS / AI marketing | Tailwind v4 utilities + `dark:` variant | Default for indie + small team builds |

---

## 3. DESIGN ENGINEERING DIRECTIVES (Bias Correction)

### 3.1 Typography
* Display / Headlines: Default `text-4xl md:text-6xl tracking-tighter leading-none`.
* Body / Paragraphs: Default `text-base text-gray-600 leading-relaxed max-w-[65ch]`.
* Sans font choice: Poppins / Geist / Outfit / Satoshi.
* **SERIF DISCIPLINE:** Serif is only acceptable when the brand brief explicitly names a serif or the aesthetic family is genuinely editorial/vintage. Never default to serif for creative/modern briefs.
* **ITALIC DESCENDER CLEARANCE:** When italic is used in display type with `y g j p q`, use `leading-[1.1]` min + `pb-1` reserve.

### 3.2 Color Calibration
* Max 1 accent color. Saturation < 80% by default.
* **THE LILA RULE:** No automatic purple button glows, no random neon gradients. Use neutral bases with high-contrast singular accents.
* **COLOR CONSISTENCY LOCK:** One accent color locked across the whole page.
* **SHAPE CONSISTENCY LOCK:** One corner-radius scale for the whole page.

### 3.3 Interactive UI States & Hard Constraints
* **BUTTON CONTRAST CHECK:** Ensure WCAG AA contrast ratio minimum (4.5:1 for body, 3:1 for large text).
* **CTA BUTTON WRAP BAN:** Primary button text MUST fit on one line at desktop (3 words max).
* **NO DUPLICATE CTA INTENT:** One label per intent on a page.
* **FORM CONTRAST CHECK:** Inputs, placeholders, focus rings, labels all pass WCAG AA.
* **HERO VIEWPORT FIT:** Headline max 2 lines, subtext max 20 words, CTA visible without scroll.
* **HERO TOP PADDING CAP:** Max `pt-24` at desktop.
* **HERO STACK DISCIPLINE:** Max 4 text elements total in hero.
* **EYEBROW RESTRAINT:** Max 1 eyebrow per 3 sections.
* **SPLIT-HEADER BAN:** No left-headline + right-floating-explainer pattern.
* **ZIGZAG ALTERNATION CAP:** Max 2 sections in a row with image+text split.
* **EM-DASH BAN (`—`):** Banned completely. Use regular hyphen `-` with spaces or restructure sentences.
