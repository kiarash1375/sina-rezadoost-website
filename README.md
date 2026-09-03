# Dr. Sina Rezadoost Website — Catalyzer

Marketing and course-sales site for **Dr. Sina Rezadoost**, a konkur chemistry
teacher in Mashhad, under the **Catalyzer** brand. Persian (RTL), warm-charcoal /
amber theme matched to the logo.

The repo ships the site in two forms:

- **`index.html`** — a self-contained static landing page (deployed to Vercel).
- **`theme/catalyzer/`** — a full WordPress theme with the same design, for the
  course store + payments stage (WooCommerce).

## Files

| Path | Description |
| --- | --- |
| `index.html` | Static landing page, self-contained. Open in a browser to view. |
| `assets/` | Photo of Dr. Rezadoost and the Catalyzer logo |
| `catalyzer-preview.html` | Single-file build with embedded images (for publishing as an Artifact) |
| `build-preview.js` | Builds `catalyzer-preview.html` from `index.html` |
| `vercel.json` / `.vercelignore` | Vercel deployment config (static, no build) |
| **`theme/catalyzer/`** | **WordPress theme.** See [`INSTALL-WORDPRESS.md`](INSTALL-WORDPRESS.md) and [`theme/catalyzer/README.md`](theme/catalyzer/README.md) |

## Running the static site

```bash
# Just open index.html in a browser

# Rebuild the preview file after changing index.html
node build-preview.js
```

## Deployment (Vercel)

The static site needs no build step.

1. Import the GitHub repo into Vercel (Add New → Project).
2. Framework Preset: **Other**. Leave the Build/Output fields empty.
3. Deploy. Every push to `main` is then published automatically.

`build-preview.js`, `catalyzer-preview.html` and `theme/` are excluded from the
deployment via `.vercelignore`.

## WordPress theme

`theme/catalyzer/` is a classic PHP theme that reproduces the landing-page design
and makes every part editable in WordPress:

- `front-page.php` composes the landing sections from `template-parts/`.
- All copy and images come from the Customizer panel
  *«کاتالیزور — محتوای صفحه‌ی فرود»*, with per-section on/off toggles.
- Custom post types: **course**, **lesson** (YouTube/Aparat oEmbed),
  **testimonial**, plus a private **catalyzer_lead** for contact-form submissions.
- Native contact form (nonce + honeypot, saves the lead and emails it) or a
  Contact Form 7 shortcode.
- **WooCommerce**: each course links to a product / checkout page; the shop
  inherits the theme styling.
- `single` / `archive` / `search` / `404` / `page` templates plus dedicated
  course and lesson templates; RTL-first; light/dark tokens.

Install: copy `theme/catalyzer/` into `wp-content/themes/` and activate, or zip it
and upload from the dashboard. Full guide in
[`INSTALL-WORDPRESS.md`](INSTALL-WORDPRESS.md).

> Note: the theme's CSS/JS were ported from an earlier revision of `index.html`.
> If the static prototype's look changes (background, layout, theme), re-sync
> `theme/catalyzer/assets/`.

## Design

- Warm charcoal + amber accent, matched to the Catalyzer logo
- Fonts: Vazirmatn + IBM Plex Mono
- Responsive, RTL, animated atomic/hexagonal-lattice background on Canvas

## Remaining before final launch

- Replace prices, stats, video links, and testimonial text with real content
- Install WooCommerce + an Iranian payment gateway and wire the course buttons
