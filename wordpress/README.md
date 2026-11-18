# SOLID on WordPress (solid.wpflat.com)

The Solid One design as a WordPress theme plus a companion plugin.

- `solid/`: the **SOLID theme**. It holds the header, footer, menus, search, and the templates for stories, sections, writers, policy pages and search. `assets/solid.css` and `assets/app.js` are generated, so don't edit them by hand.
- `solid-core/`: the **SOLID Core plugin**. It adds the content types (Writers, Conversations, SOLID 100 honourees), section straplines and order, the search index (`/wp-json/solid/v1/search-index`), the content importer, and 23 Elementor widgets under "SOLID Magazine".
- `build-wp.mjs`: copies `src/base.css` and `src/themes/one.css` into the theme, adds the Elementor compatibility rules, adapts `src/app.js`, and zips both packages into `dist/`.

Design changes: edit the files in `src/`, then run `node wordpress/build-wp.mjs` and upload `dist/solid.zip`.

Content lives in WordPress:
- Stories are posts in a topic (a sub-category of a section).
- The home, The SOLID 100, Conversations, About and Advertise pages are built in Elementor from the SOLID widgets.
