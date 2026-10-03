# Project Rules: Frontend Design & Taste Guidelines (Anti-Slop Standard)

For all frontend views, landing pages, and UI redesigns in this project:

1. **Brief Inference & Design Read**:
   - Always state the design read in one line before outputting UI code:
     *"Reading this as: <page kind> for <audience>, with a <vibe> language, leaning toward <aesthetic family>."*
   - Set the three dials: `DESIGN_VARIANCE`, `MOTION_INTENSITY`, `VISUAL_DENSITY`.

2. **Typography Standards**:
   - Primary font: **Poppins** (with fallback sans-serif).
   - Display headlines: `text-4xl md:text-6xl tracking-tighter leading-none`.
   - Body copy: `text-base leading-relaxed max-w-[65ch]`.
   - Serif font discipline: Only use serifs if explicitly requested.
   - Italic descender clearance: Ensure descenders (`y`, `g`, `j`, `p`, `q`) have proper clearance with `leading-[1.1]` and `pb-1`.

3. **Color & Theme Consistency**:
   - Single accent color locked across the page (Maritime Blue `#1E40AF` / Emerald Green `#10B981` / Tech Blue `#2563EB`).
   - Unified corner-radius scale across all cards, buttons, and inputs.
   - Strict WCAG AA contrast (4.5:1 min for text, 3:1 for large text).

4. **Hard Constraints & Anti-Slop Discipline**:
   - **Zero em-dashes (`—`)**: Always use standard hyphens (`-`) or restructure sentences.
   - **No CTA Button Wrap**: Primary button labels fit on one line (1-3 words max).
   - **Hero Viewport Fit**: Hero headline max 2 lines, subtext max 20 words, CTA visible above the fold.
   - **Hero Top Padding Cap**: Max `pt-24` on desktop.
   - **Hero Stack Discipline**: Max 4 text elements in the hero (eyebrow, headline, subtext, CTA).
   - **Eyebrow Restraint**: Maximum 1 eyebrow per 3 sections.
   - **No Duplicate CTA Intent**: One label per intent across the page.
   - **Real Icon Libraries Only**: FontAwesome / Lucide / Phosphor. Never hand-roll SVG icon paths.
   - **Mobile Responsiveness**: Explicit mobile collapse (`< 768px`) for all layouts.
