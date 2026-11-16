# SOLID — design variations

Three homepage-and-site design directions for SOLID, an online magazine for Nigerian men.
All three share the same content and page structure; only the design language differs.

| Variation | Direction | Default mode | Folder |
| --- | --- | --- | --- |
| One | The Editorial: serif masthead, print-style ruled grid | Light | `sites/one` |
| Two | The Statement: condensed uppercase type, cinematic hero | Dark | `sites/two` |
| Three | The Modern: floating navigation, rounded bento layout | Light | `sites/three` |

Every variation has a light/dark switch in the header. The choice is remembered per browser,
and `?theme=dark` or `?theme=light` on any URL forces a mode for sharing.

## Structure

- `src/data.mjs` — sections, stories, authors and honourees (placeholder content)
- `src/shared.mjs` — shared page templates (section, article, SOLID 100, Conversations, About, Advertise)
- `src/base.css`, `src/app.js` — shared styles and behaviour
- `src/themes/*.mjs`, `src/themes/*.css` — the three designs
- `sites/` — generated static output, one folder per variation (this is what gets deployed)

## Build

```bash
node build.mjs
```

No dependencies. Requires Node 20 or later. Commit the regenerated `sites/` folder after building.

## Notes

- Article copy, names and bylines are placeholders.
- Photography is loaded from Unsplash (free to use under the Unsplash License).
- Pages carry `noindex` while the designs are under review.
