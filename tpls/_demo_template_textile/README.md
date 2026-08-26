# Textile & Garment Factory Template — Dark Flame

A high-impact dark industrial theme for **textile mills, garment factories, fabric merchants and OEM/ODM contractors**.

Midnight ground `#0a0c10` + flame orange `#ff5a3c` + amber `#ffb454` + thread cyan `#4cc9f0`. Big headlines, floating thread canvas, slider, 3D cards — the look of a serious manufacturing brand.

## Hero slider

The home page supports a multi-image slider. Use it for factory floor photos, weaving machines, dyeing lines, garment lines or smart warehouses.

How to configure (editable after import):
1. Admin: Theme Center -> Home DIY
2. Open the "Hero" component -> expand Advanced
3. "Slider images": paste one image URL per line; each line becomes a slide
4. "Slider interval (seconds)": default 5

To get image URLs: upload photos to "Image Space", then copy the URL and paste here.

This demo includes 3 default SVG slides in `images/`, so the slider works immediately after import.
If no images are set, the hero falls back to the dark gradient + thread canvas.

## Default content (home.json)

`home.json` contains sample home-page content for a textile + garment factory (stats, capabilities, workflow, products, news).

After importing the template, go to **Theme Center -> Home DIY -> Import / paste JSON** and paste the `home.json` content to load the full factory page.

Tip: disable the "Scenario" module in the layout if you want a pure factory home page.

## Switch between textile / garment focus

The template is a generic skeleton; just edit the copy in Home DIY:
- **Textile mill**: capabilities = spinning / weaving / dyeing / fabrics; products = knitted fabric, woven fabric, functional fabric.
- **Garment factory**: capabilities = cutting / sewing / pressing / finished garments; products = T-shirts, hoodies, shirts, workwear OEM.
- **Both**: use the default copy, which covers the full chain from yarn to garment.

## Palette

| Color   | Hex       | Usage                                  |
|---------|-----------|----------------------------------------|
| Midnight| `#0a0c10` | Page background with woven grid      |
| Flame   | `#ff5a3c` | Primary accent (energy / dyeing)     |
| Amber   | `#ffb454` | Warm lines / highlights / gradients  |
| Cyan    | `#4cc9f0` | Tech accent (thread canvas)            |
| Warm white| `#f3efe9` | Body text, high contrast on dark     |

To change colors, edit `--c1`, `--c2`, `--c3` and `--grad` at the top of `style.css`.

## Effects

1. Hero slider — auto-switch (default 5s), Ken Burns zoom, amber dots
2. Floating thread canvas — 80 coral/amber/cyan threads
3. Hero title char-by-char reveal
4. Scroll parallax on hero copy
5. Flowing gradient line under titles
6. Pulsing glow on primary buttons
7. Ambient glow breathing background
8. 3D tilt on capability cards (desktop hover)
9. Animated stat counters

## Quick import

Zip these files directly into the archive root (do not wrap in an extra folder):

```
style.css
main.js
README.md
home.json
images/
```

Then: Admin -> Theme Center -> Import Template -> Enable.

## Customization prompt for AI

> This is the deyingding "Dark Flame" textile/garment factory template. Change the primary accent from flame orange `#ff5a3c` to tech blue `#2f6bff`, and raise the thread canvas density to 120 lines. Update `--c1` in `style.css` and `LINES_N` in `main.js`; keep all animations.

## Notes

- Default slider images are SVG vector placeholders. Replace them with real photos from Image Space for production.
- Thread canvas uses `<canvas>`; battery use is limited by capping DPR to 2.
- 3D tilt only activates on pointer/hover devices.
