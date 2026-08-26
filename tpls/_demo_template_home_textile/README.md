# Home Textile Factory Template — Linen Origin

A warm, natural, cream-toned theme for **bedding, towel, curtain, sofa cover and mattress factories**.

Cream `#f9f7f2` + linen `#e8e0d2` + sage green `#7d9471` + warm wood `#c9a87c`. Soft headlines, floating cotton particles, woven canvas, gentle reveals — the feeling of a cozy, trustworthy home brand.

## Hero slider

The home page supports a multi-image slider. Use it for bedroom scenes, factory floors, curtain showrooms or material close-ups.

How to configure (editable after import):
1. Admin: Theme Center -> Home DIY
2. Open the "Hero" component -> expand Advanced
3. "Slider images": paste one image URL per line; each line becomes a slide
4. "Slider interval (seconds)": default 5

To get image URLs: upload photos to "Image Space", then copy the URL and paste here.

This demo includes 3 default SVG slides in `images/`, so the slider works immediately after import.
If no images are set, the hero falls back to the cream gradient + cotton/woven canvas effects.

## Default content (home.json)

`home.json` contains sample home-page content for a home textile factory (stats, capabilities, workflow, products, news, contact).

After importing the template, go to **Theme Center -> Home DIY -> Import / paste JSON** and paste the `home.json` content to load the full factory page.

Tip: the "Scenario" module is disabled by default in this template.

## Switch between product focus

The template is a generic skeleton; just edit the copy in Home DIY:
- **Bedding factory**: capabilities = fabric weaving / filling / quilting / packaging.
- **Towel factory**: capabilities = yarn / weaving / dyeing / finishing.
- **Curtain / sofa cover**: capabilities = fabric sourcing / cutting / sewing / inspection.

## Palette

| Color    | Hex       | Usage                                  |
|----------|-----------|----------------------------------------|
| Cream    | `#f9f7f2` | Page background                        |
| Linen    | `#e8e0d2` | Secondary background / borders         |
| Sage     | `#7d9471` | Primary accent / buttons               |
| Warm wood| `#c9a87c` | Highlights / kicker / dots             |
| Dark     | `#3d3a36` | Body text                              |

To change colors, edit `--c1`, `--c2`, `--c3` and `--grad` at the top of `style.css`.

## Effects

1. Hero slider — auto-switch (default 5s), slow Ken Burns zoom, sage dots
2. Floating cotton particles — gentle downward drift
3. Woven line canvas — horizontal + vertical threads moving slowly
4. Hero title char-by-char reveal
5. Scroll parallax on hero copy
6. Reveal animations on scroll
7. Soft card hover lift
8. Animated stat counters
9. Gentle 3D tilt on capability cards (desktop hover)

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

> This is the deyingding "Linen Origin" home textile factory template. Change the primary accent from sage `#7d9471` to dusty blue `#6b8fa3`, and raise the cotton particle count to 80. Update `--c1` in `style.css` and `N` in `main.js`; keep all animations.

## Notes

- Default slider images are SVG vector placeholders. Replace them with real photos from Image Space for production.
- Cotton/woven canvas use `<canvas>`; battery use is limited by capping DPR to 2.
- 3D tilt only activates on pointer/hover devices.
