// Builds the three SOLID design variations into sites/one, sites/two and sites/three.
// Usage: node build.mjs
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import * as D from './src/data.mjs';
import * as S from './src/shared.mjs';

const here = path.dirname(fileURLToPath(import.meta.url));
const read = (p) => fs.readFileSync(path.join(here, 'src', p), 'utf8');
const base = read('base.css');
const appJs = read('app.js');
const index = JSON.stringify(D.STORIES.map((s) => ({ t: s.title, d: s.dek, k: D.section(s.section).name, a: s.author.name, u: s.slug })));

const themes = [];
for (const k of ['one', 'two', 'three']) if (fs.existsSync(path.join(here, 'src/themes', `${k}.mjs`))) themes.push((await import(`./src/themes/${k}.mjs`)).default);

for (const theme of themes) {
  const out = path.join(here, 'sites', theme.key);
  fs.rmSync(out, { recursive: true, force: true });
  const write = (rel, html) => {
    const f = path.join(out, rel);
    fs.mkdirSync(path.dirname(f), { recursive: true });
    fs.writeFileSync(f, html);
  };
  const page = (rel, depth, current, title, body, cls = '') => {
    const c = { root: depth ? '../'.repeat(depth) : './', current, D, S };
    const b = typeof body === 'function' ? body(c) : body;
    write(rel ? `${rel}/index.html` : 'index.html', S.layout(theme, c, { title: `${title} | SOLID`, body: b, cls }));
  };

  write('assets/style.css', base + '\n' + read(`themes/${theme.key}.css`));
  write('assets/app.js', appJs);
  write('assets/index.json', index);
  for (const [from, to] of [['favicon/favicon.ico', 'favicon.ico'], ['favicon/apple-touch-icon.png', 'apple-touch-icon.png'], ['favicon/favicon-192.png', 'icon-192.png'], ['favicon/favicon-512.png', 'icon-512.png'], ['sm-mark-red.svg', 'sm-mark-red.svg']])
    fs.copyFileSync(path.join(here, 'brand', from), path.join(out, to));

  page('', 0, 'home', 'SOLID — Grow. Achieve. Thrive.', (c) => theme.home(c), 'is-home');
  for (const sec of D.SECTIONS) page(sec.slug, 1, sec.slug, sec.name, (c) => S.sectionPage(c, sec), 'is-section');
  for (const s of D.STORIES) page(`story/${s.slug}`, 2, s.section, s.title, (c) => S.articlePage(c, s), 'is-article');
  for (const a of D.AUTHORS) page(`author/${a.slug}`, 2, '', a.name, (c) => S.authorPage(c, a), 'is-page');
  page('the-solid-100', 1, 'the-solid-100', 'The SOLID 100', S.solid100Page, 'is-page is-100');
  page('conversations', 1, 'conversations', 'SOLID Conversations', S.conversationsPage, 'is-page');
  page('about', 1, 'about', 'About SOLID', S.aboutPage, 'is-page');
  page('advertise', 1, 'advertise', 'Advertise with SOLID', S.advertisePage, 'is-page');
  page('search', 1, '', 'Search', S.searchPage, 'is-page');
  for (const l of D.LEGAL) page(l.slug, 1, '', l.name, (c) => S.legalPage(c, l), 'is-page');
  const c404 = { root: '/', current: '', D, S };
  write('404.html', S.layout(theme, c404, { title: 'Page not found | SOLID', body: S.notFound(c404), cls: 'is-page' }));

  const n = fs.readdirSync(out, { recursive: true }).filter((f) => String(f).endsWith('.html')).length;
  console.log(`${theme.key}: ${n} pages`);
}
