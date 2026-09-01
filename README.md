# Dr. Sina Rezadoost Website — Catalyzer

Marketing and course-sales template for **Dr. Sina Rezadoost**, a konkur chemistry
teacher in Mashhad, under the **Catalyzer** brand.

Current stage: a Persian (RTL) landing page for the client presentation. The next
stage moves it to a WordPress theme + course store (WooCommerce).

## Files

| File | Description |
| --- | --- |
| `index.html` | Main page, a self-contained single file. Open it in a browser to view. |
| `assets/` | Photo of Dr. Rezadoost and the Catalyzer logo |
| `catalyzer-preview.html` | Single-file build with embedded images (for publishing as an Artifact) |
| `build-preview.js` | Script that builds `catalyzer-preview.html` from `index.html` |
| `vercel.json` | Vercel deployment config (static site, no build) |

## Running

```bash
# Just open index.html in a browser

# Rebuild the preview file after changing index.html
node build-preview.js
```

## Deployment (Vercel)

The site is fully static and needs no build step.

1. Import the GitHub repo into Vercel (Add New → Project).
2. Framework Preset: **Other**. Leave the Build/Output fields empty.
3. Deploy. Every push to `main` is then published automatically.

`build-preview.js` and `catalyzer-preview.html` are excluded from the deployment
via `.vercelignore`.

## Design

- Warm charcoal theme + amber accent, matched to the Catalyzer logo
- Fonts: Vazirmatn + IBM Plex Mono
- Dark theme only, responsive, animated hexagonal carbon-lattice background on Canvas

## Remaining before final launch

- Replace prices, stats, video links, and testimonial text with real content
- Wire up the contact form and payment system on WordPress
