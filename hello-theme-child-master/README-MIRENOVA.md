# MiRenova child theme

Child theme of **Hello Elementor** with custom Elementor widgets for the MiRenova site.
Requires: WordPress 6.3+, Elementor 3.24+ (tested on 4.3.5), Contact Form 7 (tested on 6.2). PHP 7.4+.

## First run

1. Install **Hello Elementor** (parent), **Elementor**, **Contact Form 7**. Upload and activate this theme.
2. **MiRenova → Setup → Generate Home page.** It imports the images into the Media Library, creates the two
   Contact Form 7 forms, the header/footer menus, the header/footer templates (set as global) and the Home page
   with all the final content. Safe to run again: it reuses images, forms, menus and templates and creates a new page.
3. **MiRenova → Setup → Apply global colors & fonts** writes the palette, Onest typography presets and theme style
   into Elementor → Site Settings (backup + “Restore previous Site Settings”).
4. **MiRenova → Settings**: phone, e-mail, WhatsApp, hours, address, social networks, global header/footer.
5. Check the mail recipient of both forms in Contact → Contact Forms (default: the site admin e-mail).
6. When the site is ready, remove the setup tools: delete `inc/setup.php` and its `require` in `functions.php`.

## Widgets (Elementor panel → category “MiRenova”, first in the list)

| Widget | What it is |
|---|---|
| MR Header | Logo, WordPress menu, global phone, CTA, social links. Burger menu at ≤1199px. Sticky option. |
| MR Hero | Background slideshow (slide 1 = LCP, others load on first interaction), H1, Contact Form 7 card, benefits strip that overlaps the next section. |
| MR About | Text + framed photo with diamond badge. Photo right or left (inverse). |
| MR Feature Cards | Photo cards with icon badge, any number of columns, overlap into the previous section. |
| MR Services | Numbered service cards (01, 02… automatic): large first row + grid. |
| MR Process Steps | Numbered steps with connector lines; columns fit the number of steps. |
| MR Pricing | Price tiers (numbers → formatted automatically), calculator, examples table (calculated), info banner. |
| MR Locations | Text + glass cards; each location links to a page (autocomplete) or any URL. “Group title” items start a new card. |
| MR FAQ | Accordion (repeater), first open, one at a time, FAQPage JSON-LD, intro left or on top (1–2 columns). |
| MR Contact | Text, contact rows (global phone / e-mail / hours), Contact Form 7 card (form left or right). |
| MR Footer | Column repeater (logo + text / menu / global contacts / text), social links, bottom bar with automatic year. |

Every widget has: **Section** (anchor ID, light/dark scheme, padding, content width), **Section background**
(color, image with object-fit / position / opacity / blend / filters, two overlays like native containers),
**Decor** (repeater of brand diamonds, glows and images from the Media Library, with per-device visibility),
typography/color controls for every text and **Buttons** (repeater).

Each page is a stack of **full-width containers without padding**, one MiRenova widget per container
(that is how the generated Home page is built). Copy one of those containers when adding new sections.

### Text
- Every text is a rich-text (WYSIWYG) field: bold, links, lists.
- In titles, **bold words get the brand gradient** (Style → Title → Highlight: gradient / solid color / just bold).
- Every heading has an **HTML tag** selector (H1–H6, div, p) for SEO. Use one H1 per page (Hero).

### Global tokens
Work in all MiRenova texts and button labels: `{phone}` `{tel}` `{email}` `{whatsapp}` `{hours}` `{address}` `{year}` `{site}`.
Buttons have a **Link** type: Custom link / Phone (global) / E-mail (global) / WhatsApp (global) / No link.
Example: text `Llamar al {phone}` + link “Phone (global)”.

### Columns and empty cells
Columns are set per device (desktop / tablet / mobile = Elementor breakpoints). When the last row is not full
(e.g. 3 cards in 2 columns, or 1 card), a brand **filler cell** appears automatically on that device
(Content → Empty cells filler: image, diamonds, optional text).

## Header & footer
- Build with **MR Header / MR Footer**, save as Elementor templates, choose them in **MiRenova → Settings**.
- Per page: Elementor → Page Settings → **MiRenova Header & Footer** (Global / Disabled / any template),
  or the same box in the WordPress page sidebar.
- Footer copyright: `© {year} …` + switch “Insert the current year automatically” (off → static year field).

## Multilingual (WPML / Polylang)
- `wpml-config.xml` registers all widget texts, repeater fields and links for translation, plus the global
  contact fields (MiRenova → Settings) as admin strings.
- Header/footer templates, menus and Contact Form 7 forms are mapped to the current language automatically
  (`wpml_object_id`).

## Performance notes
- One CSS file (`assets/css/mirenova.css`) in `<head>`, one small deferred JS file (no jQuery), self-hosted
  Onest (variable font, Latin subset preloaded), inline SVG icons.
- Hero: slide 1 `fetchpriority="high"`; slides 2+ have no `src` until the first scroll/touch/mouse/key
  (or the delay set in the widget). All other images are lazy with `srcset`.
- Contact Form 7 JS loads only on pages with a form; its CSS is not needed for MiRenova forms.
- jQuery is moved to the footer on the frontend (`add_filter( 'mrn_jquery_in_footer', '__return_false' );` to undo).
- On the server: enable page caching and gzip/brotli (e.g. LiteSpeed Cache / WP Rocket); add a meta description
  with your SEO plugin.

## Files
- `inc/widgets/*` — widgets (`class-base.php` = shared controls and helpers)
- `inc/helpers.php` — global contacts, tokens, images, WPML helpers
- `inc/settings.php` — MiRenova → Settings
- `inc/header-footer.php` — header/footer resolution and per-page override
- `inc/cf7.php` — Contact Form 7 integration and form templates
- `inc/elementor.php` — category, widgets, “MiRenova” icon tab, self-hosted font
- `inc/setup.php` — one-time tools (remove after launch)
