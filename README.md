# Handoff: MiRenova — Home page (Reformas integrales en Málaga)

## Overview
One-page marketing site for **MiRenova** (renovation brand of ARVION STUDIO S.L., Málaga / Costa del Sol). The page explains services, process and prices, and collects quote requests through two forms: one in the hero and one in the contact section. All site copy is Spanish and final. Use it verbatim from `sections/*.html`.

## About the design files
The files in this bundle are **design references created in HTML**. They show the intended look and behaviour; they are not production code to copy directly. Rebuild the designs in the target codebase's environment (React/Next, Vue, Astro, etc.) using its established patterns. If no environment exists yet, pick the framework that suits a static marketing site (e.g. Astro or Next.js + CSS Modules/Tailwind) and implement it there.

- `MiRenova Home.dc.html` is the **live, responsive prototype**. Open it in a browser (it needs `support.js`, `_ds/…`, `image-slot.js` and `assets/` next to it) and resize to see every breakpoint. It includes two alternative hero layouts behind the `heroVariant` prop. Only the default **"Слайдер + форма"** (slideshow + form) is final.
- `sections/NN-*.html` holds **one file per section**: a static desktop snapshot (≥1300px) of the final variant, with all inline styles resolved. Custom attributes tell you what the static markup can't:
  - `data-responsive="…"` gives the layout rule per breakpoint for that element.
  - `data-show="desktop >1080px"` / `"mobile/tablet ≤1080px"` / `"≥560px"` marks elements that exist only at that width.
  - `data-style-hover` / `data-style-focus` hold the hover/focus styles to apply.
  - `data-on-click` / `data-on-change` / `data-on-submit` are interaction hooks (see *Interactions*).
  - `hidden` marks an inactive state (closed FAQ panel, closed mobile menu, inactive icon).
  - `data-decor` marks a purely decorative element.
- `static-snapshot.html` contains all sections assembled into one static desktop page, with `base.css` and a tiny `static-snapshot.js` (slideshow + FAQ).

## Fidelity
**High-fidelity.** Colours, type, spacing, imagery and interactions are final. Recreate pixel-accurately. Inline styles in the reference become your component styles/classes; they are not a pattern to keep.

## Global layout
- Page wrapper: `background:#fff; overflow-x:clip` (decor bleeds past the edges and must never cause horizontal scroll).
- Container: `max-width:1300px; margin:0 auto; padding-inline:clamp(20px,4vw,40px)`.
- Section vertical padding: `clamp(80px,12.5vw,180px)` top and bottom (exceptions noted per section).
- Sections alternate light/dark: Header (white) → Hero (dark photo) → Sobre (light) → Equipo y taller (dark) → Servicios (light) → Proceso (dark) → Precios (light) → Zonas (dark) → FAQ (light) → Contacto (dark) → Footer (white).
- Anchor offset for the sticky header: `section[id]{scroll-margin-top:84px}`; `html{scroll-behavior:smooth}`.
- **Breakpoints used:** 1080px (main desktop/mobile switch), 980px (hero 2-col), 760px, 640px, 560px, 480px. Check at **360, 390, 768, 1024, 1280, 1440, 1920**.

### Recurring patterns
- **Eyebrow** (above every H2): flex, gap 12px. A dash of 22×2px with `linear-gradient(90deg,#1F6FFF,#5EC4F8)` (on dark: `#4B95FF→#7CD6F9`), then text 13px / 700 / letter-spacing .18em / uppercase. Text colour is `#1667E0` on light and `#7CD6F9` on dark.
- **H2**: `clamp(34px,3.7vw,56px)` / line-height 1.06 / 800 / letter-spacing -.025em / `text-wrap:balance`, margin-top 24px. One phrase is a gradient-text span: on light `linear-gradient(95deg,#1F6FFF,#3FA0F7)`, on dark `linear-gradient(95deg,#4B95FF,#7CD6F9)` (`background-clip:text; color:transparent`).
- **Brand diamond**: a square rotated 45° with `border-radius` 8–16%. Variants: solid gradient `linear-gradient(135deg,#1F6FFF,#5EC4F8)`, pale fill (`#E4EEFB` / `#EEF3F9` / `#F2F5F9`), or 2px outline (`#CFE4FF` / `#A9D8F7` on light, `rgba(124,214,249,.2–.3)` on dark). Diamonds are used as icon backgrounds (icon centred, diamond `inset` 7–12px inside a 48–80px box), number badges and large background decor. Implement background decor with `::before/::after` or absolutely-positioned elements inside an `overflow:hidden; pointer-events:none` layer.
- **Primary button**: height 46 (header) / 56–60px, padding 0 22–30px, `#1F7BFF`, white 14–16px / 600 / .02em, radius 3px, shadow `0 18px 36px -16px rgba(31,123,255,.8)`; hover `#1667E0`. A trailing arrow icon (18px, stroke 2.2) appears where shown.
- **Outline button**: 1.5px border `#0E2A4F` on light (hover: bg `#0E2A4F`, text white) or `rgba(255,255,255,.7)` on dark (hover: bg white, text `#0E2A4F`).
- **Inputs**: height 50–52px, padding 0 16px, 1px `#D5DEE9`, radius 3px, bg `#F6F8FB`, 15px. Focus: border `#1F7BFF`, bg `#fff`, `box-shadow:0 0 0 3px rgba(31,123,255,.15)`. Labels are 13px / 600 / `#0E2A4F`, gap 8px. Inputs need `width:100%; min-width:0`.
- **Icons**: Lucide-style inline SVG, 24 viewBox, stroke 2–2.6, round caps.

## Sections

### 00 · Header (`sections/00-header.html`)
- Sticky, top 0, z 50. Bg `rgba(255,255,255,.94)` + `backdrop-filter:blur(14px)`, bottom border `1px #E8EDF3`. Inner height 80px, flex space-between, gap 24px.
- Logo `assets/logo-mr.png`, height 34px.
- **>1080px**: nav (Servicios, Proceso, Precios, Zonas, Preguntas, Contacto; 15px/500, gap 36px, hover `#1F7BFF`), phone `+34 652 469 039` (15px/700), button "Solicitar presupuesto".
- **≤1080px**: nav and phone are hidden. A 46×46 burger button (1.5px `#D5DEE9`, radius 3px) toggles a dropdown panel (absolute, `top:100%`, white, shadow `0 30px 50px -30px rgba(10,29,56,.35)`). The panel lists the 6 links (17px/600, rows padding 16px 0, bottom border `#EEF2F7`, a small pale diamond on the right), then the phone (19px/700), then a full-width primary button. Clicking any link closes the menu. The burger icon swaps to an X while open.
- **<560px**: the header CTA button is hidden; it stays in the menu.

### 01 · Hero (`sections/01-hero.html`)
- `position:relative; z-index:3`, bg `#0A1D38`.
- **Background slideshow**: 5 full-bleed layers (cover), in this order: FOTO-01, FOTO-06, FOTO-07, FOTO-08, FOTO-09. Autoplay every **6s** with no controls. Each slide crossfades with `opacity 1.2s ease`; the active slide also scales `1 → 1.06` with `transform 7s linear` (slow Ken Burns).
- Overlays: `linear-gradient(90deg, rgba(10,29,56,.94) 0%, rgba(10,29,56,.78) 40%, rgba(10,29,56,.3) 100%)`, plus a bottom 45% `linear-gradient(0deg, rgba(10,29,56,.92), transparent)`.
- **Corner decor**: the glass diamond EXTRA-01 sits in the top-right corner, partly cropped by the section edge: `right:clamp(-280px,-15vw,-110px); top:clamp(-300px,-17vw,-120px); width:clamp(230px,42vw,640px); opacity:.8; filter:drop-shadow(0 40px 60px rgba(31,111,255,.35))`. A 1.5px outline diamond `rgba(124,214,249,.22)` sits slightly inside it (`right:clamp(-150px,-6vw,-60px); top:clamp(-190px,-9vw,-70px); width:clamp(180px,26vw,400px)`). It's a good candidate for `::before` / `::after` on the hero.
- Content container padding-top `clamp(72px,8vw,120px)`. Grid: `minmax(0,1fr) minmax(0,460px)` above 980px, one column at 980px and below; gap `clamp(40px,4vw,64px)`; `align-items:center`.
  - Left (max 680px): eyebrow "Málaga · Costa del Sol"; H1 `clamp(42px,5vw,74px)` / 1.02 / 800 / -.03em, white, with the gradient span "Reformas integrales"; lead paragraph 18px/1.7 `#D5E0EE`; second paragraph 16px/1.75 `#AFC0D7`; outline button "Llamar al +34 652 469 039" (`tel:+34652469039`).
  - Right: **quote form card**, max 460px, white, radius 4px, padding `clamp(28px,3vw,40px)`, shadow `0 60px 100px -40px rgba(0,0,0,.65), 0 20px 40px -20px rgba(0,0,0,.3)`. Fields grid `repeat(auto-fit,minmax(min(100%,170px),1fr))`, gap 16px. Title "Solicita presupuesto" 24px/800, sub 14px `#5A6A82`. Fields: Nombre + Teléfono (2 cols), Ubicación de la vivienda (full), Tipo de reforma select (full; options: Reforma integral de piso o ático / Reforma de casa o villa / Reforma de cocina / Reforma de baño). Full-width submit "Solicitar presupuesto →" changes to "Solicitud enviada" after submit.
- **Benefits strip**, at the bottom of the hero: container, margin-top `clamp(48px,7vw,104px)`. A grid with 1px gaps on `#EDF1F6` (gives hairline dividers), radius 4px, shadow `0 40px 80px -30px rgba(10,29,56,.35), 0 10px 24px -12px rgba(10,29,56,.15)`.
  - Items (white, padding 30px 32px): diamond icon + title 17px/700 + sub 14px `#5A6A82`. The three items are "Consulta inicial · gratuita", "Visita con mediciones · gratuita" and "Presupuesto detallado · gratuito". The 4th cell is a navy (`#0E2A4F`, hover `#13366A`) phone link with "LLAMAR" 12px/700 `#7CD6F9` above "+34 652 469 039" 20px/700.
  - Columns: 4 above 1080px, 2 from 561 to 1080px, 1 at 560px and below.
  - **Overlap**: `transform:translateY(50%)` above 560px, so the strip straddles the hero and the next section. At 560px and below there's no transform, and margin-bottom is 40px.

### 02 · Sobre nosotros, light (`sections/02-sobre-nosotros.html`)
- Bg white. Padding-top `calc(clamp(96px,11vw,160px) + 70px)` above 560px (room for the strip), 80px at 560px and below. Padding-bottom `calc(clamp(80px,9vw,130px) + clamp(140px,14vw,220px))`: the second term is the space the next section's cards overlap into.
- Background: BG-01 (cover, `object-position:right`, opacity .7) under a white gradient `90deg, #fff 0%, rgba(255,255,255,.94) 42%, rgba(255,255,255,.45) 100%`, plus a top white fade (28% height). Decor: a pale diamond `#EEF3F9` at left −130px (260px wide) and a 90px outline diamond `#CFE4FF`.
- Grid `repeat(auto-fit,minmax(min(100%,460px),1fr))`, gap `clamp(56px,7vw,110px)`.
  - Text (max 580px): eyebrow "Sobre nosotros"; H2 with "MiRenova," as the gradient span; two 17px paragraphs (`#33435B`, `#46566E`).
  - Photo block, max 540px, aspect 5/6, `justify-self:end`:
    - FOTO-03 fills `inset:0 10% 10% 0` (radius 4, shadow `0 60px 90px -40px rgba(10,29,56,.4)`).
    - A 2px `#CFE4FF` frame is offset `left:14%; top:9%; right:0; bottom:0`.
    - A gradient diamond with a house icon (24% width) sits at `left:-5%; bottom:2%`.

### 03 · Equipo y taller, dark (`sections/03-equipo-y-taller.html`)
- Continues section 02. Bg `#0A1D38`, `position:relative; z-index:1; display:flow-root`, padding `0 0 clamp(80px,12.5vw,180px)`. Decor: a radial blue glow, a blurred gradient diamond on the right (opacity .14) and an outline diamond bottom-left.
- The cards grid has **`margin-top:calc(-1 * clamp(140px,14vw,220px))`**, so the top of both cards overlaps the light section above. Grid `repeat(auto-fit,minmax(min(100%,440px),1fr))`, gap 32px.
- Each card: bg `#0F2A4D`, 1px `rgba(124,214,249,.14)` border, radius 4px, shadow `0 50px 80px -40px rgba(0,0,0,.65)`.
  - The image is 16/9 (FOTO-04 / FOTO-05). A 64px gradient diamond icon hangs off the image's bottom-left (`left:36px; bottom:-32px`).
  - Body padding `60px clamp(24px,5vw,40px) 44px`: H3 26px/700 white, paragraphs 16px/1.75 `#B4C4DA`.
  - Card 2 has decor PNG-02 (210×170) at `right:-36px; top:-84px`, **desktop only**.

### 04 · Servicios, light (`sections/04-servicios.html`)
- Bg white. Decor (desktop): a large gradient diamond bleeding off the left, pale diamonds, and PNG-04 (260×240) bottom-left (desktop only).
- Header grid: eyebrow + H2 on the left, 17px intro paragraph on the right (max 520px), `align-items:end`.
- Row 1: 2 large cards (`minmax(min(100%,460px),1fr)`, gap 28px). Each has a 16/10 image (FOTO-06, FOTO-07), a number badge "01"/"02" in a 68px gradient diamond hanging off the image bottom-left, and padding `60px clamp(24px,5vw,40px) 44px`.
- Row 2: 3 cards (`minmax(min(100%,320px),1fr)`): FOTO-08, FOTO-09, FOTO-10 at 4/3, badges 03–05, padding `54px clamp(24px,5vw,32px) 40px`, H3 23px.
- Cards: bg `#F2F5F9`, radius 4px. Hover: `translateY(-6px)` + `box-shadow:0 40px 70px -36px rgba(10,29,56,.35)`, transition .35s ease.

### 05 · Proceso, dark (`sections/05-proceso.html`)
- Bg `#0A1D38`; BG-02 (blueprint) at opacity .14 under `linear-gradient(90deg, rgba(10,29,56,.9), rgba(10,29,56,.35))`. Diamond decor on the right. PNG-03 (280×230) top-right, **desktop only**, because it collides with the H2 on smaller screens.
- Intro (max 720px): eyebrow, H2, 17px paragraph.
- Four steps: 4 columns above 1080px, 2 from 641 to 1080px, 1 at 640px and below; gap 56px 40px.
  - Each step starts with an 80px diamond badge. Step 01 is a solid gradient; 02–03 are `#0F2A4D` with a 2px `#4B95FF` border; 04 has a check icon and a `#7CD6F9` border.
  - Then H3 23px white and 15.5px paragraphs `#AFC0D7`.
  - Desktop only: a 2px gradient connector line from each badge to the next (`left:92px; right:-40px; top:39px`).

### 06 · Precios, light (`sections/06-precios.html`)
- Bg white, with the lower 48% `#F2F5F9`. Decor: diamonds on the left; PNG-06 (250px) desktop only.
- Header grid: eyebrow + H2 on the left, 2 paragraphs on the right.
- **Tier cards**: 3 columns above 760px, 1 column at 760px and below; gap 24px; `align-items:center`.
  - Básica and Premium: `#F2F5F9`, padding 48px 40px.
  - Confort (highlighted): `#0E2A4F`, padding 64px 40px, shadow, with a corner gradient diamond.
  - Price text `clamp(36px,3.4vw,46px)`/800, "€/m²" 17px, "sin IVA" 14px.
- **Calculator card** (white, radius 4, padding `clamp(32px,5vw,44px) clamp(22px,5vw,40px)`, shadow + 1px ring `#E3E9F1`):
  - Title "Calcula tu reforma" 22px/700.
  - Row "Superficie interior" with a stepper: 38px −/+ buttons (1px `#D5DEE9`, hover border/colour `#1F7BFF`) around the value "{m2} m²" 22px/800. The row wraps on small screens.
  - **Custom range slider**, height 30px:
    - Track: 6px, `#E3E9F1`, radius 3px.
    - Fill: `width:{pct}%`, gradient `90deg #1F6FFF→#5EC4F8`.
    - Thumb: a 22px diamond (rotated square, radius 5px, white, 3px `#1F7BFF` border, shadow `0 8px 18px -6px rgba(31,123,255,.65)`) at `left:{pct}%`.
    - A native `<input type=range min=30 max=250 step=5>` sits on top at opacity 0 to handle input and keyboard.
    - Labels "30 m²" and "250 m²" under the track.
  - **Tier segmented control**: container `#F2F5F9`, padding 4px, gap 4px; 3 columns above 480px, 1 column at 480px and below. Each option shows the label (14px/700) with an 8px diamond dot, and the rate (12px) below.
    - Active: bg `#0E2A4F`, white label, gradient dot, rate `#7CD6F9`, shadow `0 12px 24px -12px rgba(10,29,56,.6)`.
    - Inactive: transparent, `#0E2A4F` label, dot `#C9D6E6`, rate `#7A8AA0`.
  - **Result box**: `linear-gradient(120deg,#0E2A4F,#13366A)`, padding `clamp(20px,5vw,28px)`. Shows "ESTIMACIÓN ORIENTATIVA · {rate}", the range "{from} – {to} €" at `clamp(26px,2.6vw,34px)`/800, and "sin IVA".
- **Examples table card**: same card style. The table scrolls horizontally on small screens (`min-width:420px` inside `overflow-x:auto`). Header row bg `#F2F5F9`, row rules `#E3E9F1`.
- **"Qué influye en el precio de las reformas"**: a full-width navy card with a photo background.
  - Bg `#0E2A4F` with FOTO-04 (cover, `center 40%`) under `linear-gradient(90deg, rgba(10,29,56,.62), rgba(10,29,56,.8) 45%, rgba(10,29,56,.93))`, plus an outline diamond bottom-right.
  - Padding `clamp(44px,5vw,72px) clamp(36px,4vw,56px)`. Grid `minmax(min(100%,340px),1fr)`, gap 24px 56px, `align-items:start`.
  - H3 `clamp(26px,2.4vw,34px)`/800 white; paragraphs 16px `#D5E0EE`.

### 07 · Zonas, dark (`sections/07-zonas.html`)
- Bg `#0A1D38`, BG-03 (Málaga aerial) full-bleed under `linear-gradient(90deg, rgba(10,29,56,.97), rgba(10,29,56,.86) 45%, rgba(10,29,56,.55))`. PNG-07 (palm, 300px) top-right, **desktop only**, so it never sits under the H2 on mobile.
- Two columns (`minmax(min(100%,460px),1fr)`):
  - Text: eyebrow, H2, 2 paragraphs.
  - Two glass cards: `rgba(15,42,77,.62)` + `backdrop-filter:blur(16px)`, 1px `rgba(124,214,249,.18)`, padding `clamp(24px,5vw,36px)`. Card titles are "Málaga" and "Costa del Sol y provincia". Chips: map-pin icon + name, 1px `rgba(124,214,249,.3)`, radius 3px, padding 10px 16px, 15px/500, flex-wrap gap 10px.

### 08 · Preguntas / FAQ, light (`sections/08-faq.html`)
- Two columns (`minmax(min(100%,360px),1fr)`).
  - Left: eyebrow + H2, `position:sticky; top:120px` above 1080px and static at 1080px and below. PNG-05 (keys, 300px square) sits under the heading, desktop only.
  - Right: accordion of 7 items. Items have a top border on the list and bottom borders `#E3E9F1`.
    - Question button: full-width, padding `clamp(22px,3vw,30px) 0`, `clamp(17px,2vw,20px)`/600, hover `#1667E0`.
    - Icon: a 40px pale diamond with a plus icon, which becomes a minus when open.
    - Answer: padding `0 clamp(0px,5vw,64px) 32px 0`, 16px/1.75 `#46566E`.
- **Behaviour**: one item open at a time; item 1 is open by default; clicking the open item closes it.

### 09 · Contacto, dark (`sections/09-contacto.html`)
- Bg `#0A1D38`; BG-04 at opacity .18; radial glow. EXTRA-01 glass diamond (760px, right −300px) at **opacity .12, saturate(.6)**, so it is barely visible behind the form.
- Two columns (`minmax(min(100%,440px),1fr)`):
  - Left: eyebrow, H2, paragraph, then contact rows (52px diamond icon + label 13px `#8FA4C0` + value 19px/700). The rows are phone, email `grupo.arvion@gmail.com` (`overflow-wrap:anywhere`) and opening hours.
  - Right: form card (padding `clamp(32px,4vw,52px)`, grid `minmax(min(100%,200px),1fr)`, gap 18px). Fields: Nombre, Teléfono, Correo (full), Ubicación, Superficie m² (number), Tipo de reforma (select, full), Qué reformas necesitas (textarea, full). Submit "Solicitar presupuesto de reformas →" changes to "Solicitud enviada".

### 10 · Footer (`sections/10-footer.html`)
- White, padding-top `clamp(80px,9vw,120px)`.
- Grid `minmax(min(100%,240px),1fr)`, gap 48px:
  - Logo (40px) + "MiRenova es la marca de reformas de ARVION STUDIO S.L."
  - Nav links.
  - Phone / email / hours.
- Bottom bar: top border `#E8EDF3`, 13px `#6B7A90`, "© 2026 MiRenova · ARVION STUDIO S.L." and "Todos los derechos reservados".

## Interactions & behaviour
| Hook (`data-on-*`) | Behaviour |
|---|---|
| `toggleMenu` / `closeMenu` | Opens/closes the mobile menu (≤1080px). Close on link click, and also on Esc / outside click / resize past 1080px. |
| Hero slideshow (no hook) | `setInterval` 6000ms, cycles 5 slides. Pause when the tab is hidden; respect `prefers-reduced-motion` (no scale, instant swap). |
| `onHeroSubmit`, `onSubmit` | The prototype only prevents default and swaps the button label to "Solicitud enviada". Wire to the real backend/CRM. Add validation: Nombre and Teléfono required; email format; m² positive. |
| `onM2`, `m2Minus`, `m2Plus` | Area 30–250 m², step 5, default 80. |
| `pickTier0–2` | Tier: Básica 800–1.000, Confort 1.000–1.300 (default), Premium 1.300–2.000 €/m². |
| Calculator output | `from = m2 × min`, `to = m2 × max`, formatted with `.` thousands separators ("80.000"), no decimals, "sin IVA". |
| `t0–t6` | FAQ accordion: one open at a time, item 0 open by default. |
| `data-style-hover` | Hover states: cards lift −6px; buttons darken; links turn `#1F7BFF`. |
| Focus | `:focus-visible{outline:2px solid #1F7BFF; outline-offset:2px}` on every interactive element. |

## State
`menuOpen:boolean`, `slide:number (0–4)`, `faqOpen:number (-1…6)`, `m2:number`, `tier:0|1|2`, `heroFormSent:boolean`, `contactFormSent:boolean`.

## Design tokens
**Colours**
- Navy: `#0A1D38` (dark section bg), `#0E2A4F` (primary text, navy cards), `#0F2A4D` (dark cards), `#13366A` (hover / gradient end)
- Blue: `#1F7BFF` (primary), `#1667E0` (hover, eyebrow and link on light), `#1F6FFF`, `#3FA0F7`, `#4B95FF`, `#5EC4F8`
- Cyan: `#7CD6F9` (accent on dark)
- Tints: `#E4EEFB`, `#CFE4FF`, `#A9D8F7`, `#EEF3F9`, `#F2F5F9`, `#F6F8FB`, `#EDF1F6`
- Lines: `#E3E9F1`, `#E8EDF3`, `#D5DEE9`, `#EEF2F7`
- Text on light: `#33435B`, `#46566E`, `#5A6A82`, `#6B7A90`, `#7A8AA0`
- Text on dark: `#D5E0EE`, `#C3D1E4`, `#B4C4DA`, `#AFC0D7`, `#8FA4C0`

**Gradients**
- Brand: `linear-gradient(135deg,#1F6FFF,#5EC4F8)`
- Heading text, light: `95deg,#1F6FFF→#3FA0F7`
- Heading text, dark: `95deg,#4B95FF→#7CD6F9`

**Typography**
- **Onest** (Google Fonts, 400/500/600/700/800) for everything. Base body 15px / 1.55.
- Scale: H1 `clamp(42px,5vw,74px)` · H2 `clamp(34px,3.7vw,56px)` · H3 26 / 23 / 22px · lead 17–18px · body 16px · small 14–15.5px · eyebrow 13px.

**Radius**
- 3px for buttons, inputs and chips; 4px for cards and images.
- Diamonds use 8–16% radius on a 45°-rotated square.

**Shadows**
- Card on dark: `0 50px 80px -40px rgba(0,0,0,.65)`
- Card on light: `0 40px 80px -40px rgba(10,29,56,.35)`
- Card hover: `0 40px 70px -36px rgba(10,29,56,.35)`
- Primary button: `0 18px 36px -16px rgba(31,123,255,.8)`

**Base reset**: see `base.css`.

## Assets (`assets/`)
All images are final WebP files. PNG-* and EXTRA-01 have real alpha.

| Code | File | Used in |
|---|---|---|
| FOTO-01 | img/FOTO-01-hero-malaga-living-room.webp | Hero slide 1 |
| FOTO-02 | img/FOTO-02-mediterranean-staircase.webp | Alternative hero only |
| FOTO-03 | img/FOTO-03-team-on-site.webp | 02 Sobre nosotros |
| FOTO-04 | img/FOTO-04-porcelain-tile-quality-check.webp | 03 card 1; 06 "Qué influye" background |
| FOTO-05 | img/FOTO-05-porcelain-workshop.webp | 03 card 2 |
| FOTO-06…10 | img/FOTO-06…FOTO-10 | Hero slides 2–5 (06–09); Servicios cards 01–05 |
| BG-00 | img/BG-00-bright-hero-background.webp | Alternative hero only |
| BG-01 | img/BG-01-architectural-light-shadow.webp | 02 background |
| BG-02 | img/BG-02-architectural-blueprint.webp | 05 background |
| BG-03 | img/BG-03-malaga-aerial-coast.webp | 07 background |
| BG-04 | img/BG-04-contact-light-interior.webp | 09 background |
| PNG-01 | img/PNG-01-olive-branch-vase.webp | Alternative hero only |
| PNG-02…07 | img/PNG-02…PNG-07 | Decor in 03, 05, 04, 08, 06, 07 (desktop only) |
| EXTRA-01 | img/EXTRA-01-blue-frosted-glass-diamond.webp | Hero top-right corner; 09 (faint) |
| EXTRA-02 | img/EXTRA-02-olive-window-shadow.webp | 08 FAQ background (`mix-blend-mode:multiply; opacity:.3`) |
| Logo | logo-mr.png | Header, footer |

Alt texts (Spanish) for content photos are on the `<img>` tags in `sections/`. Decorative images have `alt=""`.

## Files
- `MiRenova Home.dc.html`: live responsive prototype (source of truth for behaviour). Needs `support.js`, `image-slot.js` and `_ds/` next to it.
- `sections/00-header.html … 10-footer.html`: per-section markup (desktop snapshot + responsive annotations).
- `static-snapshot.html` + `base.css` + `static-snapshot.js`: all sections on one static page.
- `assets/`: images and logo.
