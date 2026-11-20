import fs from 'node:fs';
import * as D from './data.mjs';

export const esc = (s) => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/"/g, '&quot;');
export const src = (k, w, h) => `https://images.unsplash.com/photo-${D.P[k]}?auto=format&fit=crop&crop=faces,edges&w=${w}&h=${h}&q=72`;
export const pic = (k, w, h, alt = '', eager = false) =>
  `<img src="${esc(src(k, w, h))}" width="${w}" height="${h}" alt="${esc(alt)}" ${eager ? 'fetchpriority="high"' : 'loading="lazy"'} decoding="async">`;

const I = (d, o = '') => `<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" ${o}>${d}</svg>`;
export const icon = {
  search: I('<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>'),
  sun: I('<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>', 'class="i-sun"'),
  moon: I('<path d="M20 14.5A8 8 0 0 1 9.5 4a8 8 0 1 0 10.5 10.5Z"/>', 'class="i-moon"'),
  menu: I('<path d="M3 7h18M3 12h18M3 17h18"/>'),
  close: I('<path d="M5 5l14 14M19 5 5 19"/>'),
  arrow: I('<path d="M5 12h14M13 6l6 6-6 6"/>', 'class="i-arrow"'),
  left: I('<path d="M19 12H5M11 6l-6 6 6 6"/>'),
  play: '<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true"><path d="M8 5.5v13a1 1 0 0 0 1.5.86l11-6.5a1 1 0 0 0 0-1.72l-11-6.5A1 1 0 0 0 8 5.5Z"/></svg>',
  mic: I('<rect x="9" y="3" width="6" height="11" rx="3"/><path d="M5 11a7 7 0 0 0 14 0M12 18v3"/>'),
  x: '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M17.5 3h3.1l-6.8 7.8L22 21h-6.3l-4.9-6.4L5.2 21H2.1l7.3-8.3L2 3h6.4l4.4 5.9L17.5 3Zm-1.1 16.1h1.7L7.7 4.8H5.9l10.5 14.3Z"/></svg>',
  ig: I('<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r=".6" fill="currentColor"/>'),
  yt: I('<rect x="2.5" y="5.5" width="19" height="13" rx="4"/><path d="m10 9.5 5 2.5-5 2.5v-5Z" fill="currentColor"/>'),
  in: '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M4.98 3.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5ZM3 9.75h4v11H3v-11Zm6.5 0h3.8v1.6h.06c.53-.95 1.83-1.9 3.76-1.9 4.02 0 4.88 2.5 4.88 5.9v5.4h-4v-4.8c0-1.3-.03-3-1.9-3s-2.2 1.4-2.2 2.9v4.9h-4v-11Z"/></svg>',
  link: I('<path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1"/>'),
  mark: I('<path d="M6 3h12v18l-6-4.5L6 21V3Z"/>'),
};

export const u = {
  home: (c) => c.root,
  sec: (c, slug) => `${c.root}${slug}/`,
  sub: (c, slug, sub) => `${c.root}${slug}/#${D.slugify(sub)}`,
  story: (c, s) => `${c.root}story/${s.slug}/`,
  author: (c, a) => `${c.root}author/${a.slug}/`,
};

// The SOLID MAG logo, traced from the client's masthead. Defined once per page as symbols;
// ink follows the text colour (light/dark mode), red uses the brand red.
const LG = JSON.parse(fs.readFileSync(new URL('./logo.json', import.meta.url), 'utf8'));
const sym = (id, g) => `<symbol id="${id}" viewBox="0 0 ${g.w} ${g.h}"><path fill="currentColor" fill-rule="evenodd" d="${g.ink}"/><path style="fill:var(--red)" fill-rule="evenodd" d="${g.red}"/></symbol>`;
export const logoDefs = `<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">${sym('lg-the', LG.the)}${sym('lg-full', LG.full)}</svg>`;
export const logoSvg = (kind = 'the', cls = '') => `<svg class="logo__svg${cls ? ' ' + cls : ''}" viewBox="0 0 ${LG[kind].w} ${LG[kind].h}" aria-hidden="true" focusable="false"><use href="#lg-${kind}"/></svg>`;
// full = masthead with rule and tagline (only where it can be shown at 480px+ wide).
export const logo = (c, full = false) =>
  `<a class="logo" href="${c.root}" aria-label="The SOLID Mag — home">${full ? logoSvg('full', 'logo--full') + logoSvg('the', 'logo--short') : logoSvg('the')}</a>`;

export const themeToggle = () =>
  `<button class="icon-btn theme-toggle" type="button" data-theme-toggle aria-label="Switch between light and dark mode" title="Light / dark mode">${icon.sun}${icon.moon}</button>`;

const DIMS = { std: [720, 480], row: [280, 280], lead: [1280, 860], over: [900, 1100], portrait: [640, 860], wide: [1280, 720], mini: [200, 200] };

export function card(c, s, v = 'std', o = {}) {
  const [w, h] = o.dim || DIMS[v] || [0, 0];
  const sec = D.section(s.section);
  const showDek = o.dek ?? ['lead', 'wide', 'over'].includes(v);
  const kick = o.kick === 'section' ? sec.name : s.sub;
  return `<article class="card card--${v}${o.cls ? ' ' + o.cls : ''}" data-sub="${D.slugify(s.sub)}">
${o.n ? `<span class="card__num">${String(o.n).padStart(2, '0')}</span>` : ''}${w ? `<div class="card__media">${pic(s.img, w, h, '', o.eager)}</div>` : ''}
<div class="card__body"><a class="kicker" href="${u.sec(c, s.section)}">${esc(kick)}</a>
<h3 class="card__title"><a href="${u.story(c, s)}">${esc(s.title)}</a></h3>
${showDek ? `<p class="card__dek">${esc(s.dek)}</p>` : ''}
<p class="meta">${o.meta === false ? '' : `<span>By ${esc(s.author.name)}</span>`}<time datetime="${s.iso}">${s.date}</time>${o.meta === false ? '' : `<span>${s.mins} min read</span>`}</p></div></article>`;
}

export const secHead = (title, href, label = 'View all', kicker = '') =>
  `<div class="sec-head">${kicker ? `<p class="kicker">${kicker}</p>` : ''}<h2 class="sec-title">${title}</h2>${href ? `<a class="more" href="${href}">${label} ${icon.arrow}</a>` : ''}</div>`;

export const convCard = (c, v, cls = '', dim = [960, 540]) =>
  `<article class="conv ${cls}"><a class="conv__media" href="${c.root}conversations/">${pic(v.img, dim[0], dim[1])}<span class="play">${v.kind.startsWith('Podcast') ? icon.mic : icon.play}</span><span class="conv__len">${v.len}</span></a>
<div class="conv__body"><span class="kicker">${v.kind} · Ep. ${v.ep}</span><h3 class="conv__title"><a href="${c.root}conversations/">${esc(v.title)}</a></h3><p class="conv__guest">with ${esc(v.guest)}, ${esc(v.role.toLowerCase())}</p><p class="conv__dek">${esc(v.dek)}</p></div></article>`;

export const person = (c, p, dim = [520, 700]) =>
  `<article class="person"><div class="person__media">${pic(p.img, dim[0], dim[1], '')}</div><div class="person__body"><span class="kicker">${esc(p.cat || p.role)}</span><h3 class="person__name">${p.slug ? `<a href="${u.author(c, p)}">${esc(p.name)}</a>` : esc(p.name)}</h3>${p.cat ? `<p class="person__role">${esc(p.role)}</p>` : ''}</div></article>`;

export const newsletter = (c) =>
  `<section class="nl" id="newsletter"><div class="wrap nl__in"><div class="nl__copy"><p class="kicker">The SOLID Brief</p><h2 class="nl__t">Stay ahead.</h2><p class="nl__d">The week’s sharpest stories on business, money and living well, delivered every Sunday morning.</p></div>
<form class="nl__form" data-newsletter><label class="sr" for="nl-email">Email address</label><input id="nl-email" type="email" required placeholder="Your email address" autocomplete="email"><button class="btn btn--red" type="submit">Subscribe</button><small>No spam. Unsubscribe at any time.</small></form></div></section>`;

export const social = () =>
  `<ul class="social"><li><a href="#" aria-label="X">${icon.x}</a></li><li><a href="#" aria-label="Instagram">${icon.ig}</a></li><li><a href="#" aria-label="YouTube">${icon.yt}</a></li><li><a href="#" aria-label="LinkedIn">${icon.in}</a></li></ul>`;

export function navItems(c, list) {
  return list.map((s) => `<li class="nav__i${c.current === s.slug ? ' is-on' : ''}"><a href="${u.sec(c, s.slug)}">${esc(s.short || s.name)}</a></li>`).join('');
}

function footer(c) {
  const col = (h, items) => `<div class="foot__col"><h3>${h}</h3><ul>${items.map(([t, l]) => `<li><a href="${l}">${esc(t)}</a></li>`).join('')}</ul></div>`;
  return `<footer class="foot"><div class="wrap">
<div class="foot__top"><div class="foot__brand">${logo(c)}<p>The magazine for Nigerian men who want more — in business, in life and in themselves.</p>${social()}</div>
<nav class="foot__cols" aria-label="Footer">
${col('Sections', D.SECTIONS.slice(0, 5).map((s) => [s.name, u.sec(c, s.slug)]))}
${col('&nbsp;', D.SECTIONS.slice(5).map((s) => [s.name, u.sec(c, s.slug)]))}
${col('Franchises', [...D.FRANCHISES.map((s) => [s.name, u.sec(c, s.slug)]), ['Cover Stories', u.sub(c, 'solid-men', 'Cover Stories')], ['Men to Watch', u.sub(c, 'solid-men', 'Men to Watch')], ['Search', `${c.root}search/`]])}
${col('SOLID', [['Our Story', `${c.root}about/`], ['Editorial Team', `${c.root}about/#editorial-team`], ['Advertise', `${c.root}advertise/`], ['Media Kit', `${c.root}advertise/#media-kit`], ['Contact', `${c.root}about/#contact`]])}
</nav></div>
<div class="foot__bot"><span>© 2026 SOLID Magazine. All rights reserved.</span><ul class="foot__legal">${D.LEGAL.map((l) => `<li><a href="${c.root}${l.slug}/">${l.name}</a></li>`).join('')}</ul><span class="foot__tag">#SOLIDMen</span></div>
</div></footer>`;
}

// nl: where Subscribe goes (this page's newsletter block, or the home page's)
function menuPanel(c, nl = '#newsletter') {
  // Sections menu: main items only, each linking to its page (no sub-menus).
  const item = (name, href) => `<div class="menu__col"><a class="menu__h" href="${href}">${esc(name)}</a></div>`;
  return `<div class="menu" id="menu" aria-hidden="true" role="dialog" aria-label="All sections"><div class="menu__in wrap">
<div class="menu__bar">${logo(c, false)}<div class="menu__tools">${themeToggle()}<button class="icon-btn" type="button" data-menu-close aria-label="Close menu">${icon.close}</button></div></div>
<div class="menu__grid">${D.SECTIONS.map((s) => item(s.name, u.sec(c, s.slug))).join('')}${item('About SOLID', `${c.root}about/`)}</div>
<div class="menu__sub"><p class="kicker">Free newsletter</p><p class="menu__sub-t">The SOLID Brief</p><p class="menu__sub-d">The week’s sharpest stories on business, money and living well, in your inbox every Sunday morning.</p><a class="btn btn--red" href="${nl}">Get the newsletter</a><small>Free. No spam. Unsubscribe at any time.</small></div>
</div></div>`;
}

const searchPanel = () =>
  `<div class="search" id="search" aria-hidden="true" role="dialog" aria-label="Search"><div class="search__in"><button class="icon-btn search__x" type="button" data-search-close aria-label="Close search">${icon.close}</button>
<label class="kicker" for="q">Search SOLID</label><div class="search__field">${icon.search}<input id="q" type="search" data-search-input placeholder="Stories, people, topics…" autocomplete="off"></div><ul class="search__res" data-search-results></ul></div></div>`;

export function layout(theme, c, { title, body, cls = '', desc = '' }) {
  const init = `(function(){var d=document.documentElement,s=null;try{s=localStorage.getItem('solid-theme')}catch(e){}var m=location.search.match(/[?&]theme=(dark|light)/);if(m)s=m[1];var f='${theme.def}';d.setAttribute('data-theme',s||(f==='auto'?(matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light'):f))})()`;
  return `<!doctype html>
<html lang="en" data-theme="${theme.def === 'dark' ? 'dark' : 'light'}" data-root="${c.root}">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>${esc(title)}</title>
<meta name="description" content="${esc(desc || 'SOLID is the online magazine for Nigerian men who want more — in business, in life and in themselves.')}">
<meta name="robots" content="noindex">
<meta name="theme-color" content="#C80F2E">
<link rel="icon" href="${c.root}favicon.ico" sizes="48x48"><link rel="icon" href="${c.root}sm-mark-red.svg" type="image/svg+xml"><link rel="apple-touch-icon" href="${c.root}apple-touch-icon.png">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link rel="preconnect" href="https://images.unsplash.com">
<link rel="stylesheet" href="${theme.fonts}">
<link rel="stylesheet" href="${c.root}assets/style.css">
<script>${init}</script>
</head>
<body class="${cls}">
<a class="skip" href="#main">Skip to content</a>
${logoDefs}
${theme.header(c)}
<main id="main">
${body}
</main>
${footer(c)}
${menuPanel(c, body.includes('id="newsletter"') ? '#newsletter' : `${c.root}#newsletter`)}
${searchPanel()}
<script src="${c.root}assets/app.js" defer></script>
</body>
</html>`;
}

/* ───────── Inner pages (shared structure, styled per theme) ───────── */

const phead = (c, { crumb, title, lede, chips = [], cls = '', extra = '' }) =>
  `<header class="phead ${cls}"><div class="wrap phead__in"><p class="crumb"><a href="${c.root}">Home</a><span>/</span>${crumb}</p><h1 class="phead__t">${title}</h1>${lede ? `<p class="phead__l">${lede}</p>` : ''}${chips.length ? `<ul class="chips">${chips.map(([t, l]) => `<li><a href="${l}">${esc(t)}</a></li>`).join('')}</ul>` : ''}${extra}</div></header>`;

const filterBar = (sec) => `<ul class="chips" data-filter-bar aria-label="Filter ${esc(sec.name)} by topic"><li><a href="#" data-filter="" aria-current="true">All</a></li>${sec.subs.map((x) => `<li><a href="#${D.slugify(x)}" data-filter="${D.slugify(x)}">${esc(x)}</a></li>`).join('')}</ul>`;

export function sectionPage(c, sec) {
  const own = D.bySection(sec.slug);
  const others = D.STORIES.filter((s) => s.section !== sec.slug && s.i > 0);
  const pick = (n, off) => Array.from({ length: n }, (_, i) => others[(off + i * 5) % others.length]);
  return phead(c, { crumb: esc(sec.name), title: esc(sec.name), lede: `${esc(sec.line)} ${esc(sec.intro)}`, extra: filterBar(sec) })
    + `<div data-filter-scope><p class="wrap filter-note" data-filter-note aria-live="polite"></p>
<section class="wrap pblock" data-filter-group>${card(c, own[0], 'lead', { eager: true })}</section>
<section class="wrap pblock" data-filter-group><div class="grid grid-3">${own.slice(1).map((s) => card(c, s, 'std', { dek: true })).join('')}</div></section>
<section class="wrap pblock filter-empty" data-filter-empty hidden><p>There are no <b data-filter-name></b> stories yet.</p><a class="btn btn--ghost" href="#" data-filter="">See all ${esc(sec.name)} ${icon.arrow}</a></section></div>
<section class="band"><div class="wrap">${secHead('More from SOLID', c.root, 'Home')}<div class="grid grid-4">${pick(4, sec.slug.length).map((s) => card(c, s, 'std', { kick: 'section' })).join('')}</div></div></section>
${newsletter(c)}`;
}

const BODY = (s) => `<p class="drop">There is a particular kind of quiet that settles over a man who knows exactly what he is building. It is not the quiet of someone with nothing to say. It is the quiet of someone who has stopped needing to announce it.</p>
<p>${esc(s.dek)} That sentence could have been written a decade ago, and in some ways that is the point. The men we spoke to for this story share an unfashionable belief: that most things worth having arrive slowly, and that the work of a serious life is mostly the work of staying the course.</p>
<p>It is easy, in a country that moves as fast as this one, to mistake motion for progress. Lagos rewards the loud. But look closely at the people who are still standing after twenty years and a different pattern emerges. They are deliberate. They keep their promises small and their standards high. They are, in a word, solid.</p>
<h2>The discipline nobody sees</h2>
<p>Ask them how it happened and the answers are almost disappointingly ordinary. They woke up early. They wrote things down. They said no far more often than they said yes. They chose partners carefully and treated their word as the most valuable thing they owned.</p>
<blockquote><p>“Nobody claps for consistency. They clap for the result, years later, and call it luck.”</p></blockquote>
<p>What looks like talent from a distance is usually a long sequence of unremarkable days. The edge is not intensity. It is the refusal to stop: to keep showing up for the plan after the excitement of making the plan has worn off.</p>
<h2>What it asks of you</h2>
<p>None of this is free. Building anything that lasts costs time that could have been spent elsewhere, and the men here are honest about what they traded. The best of them have learned to count success in more than one currency: the health they kept, the families that still know them, the people who would vouch for them without being asked.</p>
<p>That may be the real lesson. Ambition is not the problem, and neither is wanting more. The question is what kind of man the wanting turns you into, and whether, when you finally arrive, there is anyone you love standing beside you.</p>`;

export function articlePage(c, s) {
  const sec = D.section(s.section);
  const related = [...D.bySection(s.section).filter((x) => x !== s), ...D.STORIES.filter((x) => x.section !== s.section)].slice(0, 3);
  const share = `<div class="share"><span class="sr">Share</span><a href="#" aria-label="Share on X">${icon.x}</a><a href="#" aria-label="Share on LinkedIn">${icon.in}</a><a href="#" aria-label="Copy link">${icon.link}</a><a href="#" aria-label="Save">${icon.mark}</a></div>`;
  return `<div class="progress" id="progress" aria-hidden="true"></div>
<article class="art">
<header class="art__head"><div class="art__headin"><p class="crumb"><a href="${u.sec(c, sec.slug)}">${esc(sec.name)}</a><span>/</span>${esc(s.sub)}</p>
<h1 class="art__t">${esc(s.title)}</h1><p class="art__dek">${esc(s.dek)}</p>
<div class="byline"><span class="byline__av">${pic(s.author.img, 120, 120)}</span><div class="byline__who"><a href="${u.author(c, s.author)}">By ${esc(s.author.name)}</a><span>${esc(s.author.role)} · ${s.date} · ${s.mins} min read</span></div>${share}</div></div></header>
<figure class="art__fig">${pic(s.img, 1680, 945, '', true)}<figcaption>Photograph for SOLID</figcaption></figure>
<div class="art__body prose">${BODY(s)}</div>
<footer class="art__foot"><ul class="chips"><li><a href="${u.sec(c, sec.slug)}">${esc(sec.name)}</a></li><li><a href="${u.sub(c, sec.slug, s.sub)}">${esc(s.sub)}</a></li>${sec.slug === 'solid-men' ? '' : `<li><a href="${c.root}solid-men/">SOLID Men</a></li>`}</ul>
<div class="authorbox"><span class="byline__av">${pic(s.author.img, 160, 160)}</span><div><p class="kicker">Written by</p><h3><a href="${u.author(c, s.author)}">${esc(s.author.name)}</a></h3><p>${esc(s.author.role)} at SOLID. Writes about ambition, work and the business of living well.</p></div></div></footer>
</article>
<section class="band"><div class="wrap">${secHead('Read next', u.sec(c, sec.slug), `More ${esc(sec.name)}`)}<div class="grid grid-3">${related.map((x) => card(c, x, 'std', { dek: true, kick: 'section' })).join('')}</div></div></section>
${newsletter(c)}`;
}

export function solid100Page(c) {
  const criteria = [['Achievement', 'A sustained record of excellence in his field, measured in outcomes rather than attention.'], ['Character', 'A reputation for integrity among the people who know his work best.'], ['Influence', 'Evidence that he raises the standard for those around him and those coming after.'], ['Impact', 'A contribution that reaches beyond personal success to community and country.']];
  return phead(c, { cls: 'phead--100', crumb: 'The SOLID 100', title: 'The SOLID <em>100</em>', lede: 'One hundred Nigerian men at the top of their fields, across ten categories, chosen for their work, character and influence.', extra: `<span class="phead__big" aria-hidden="true">100</span>` })
    + `<section class="wrap pblock split" id="about-the-list"><div>${secHead('About the list')}</div><div class="prose"><p>The SOLID 100 is not an award, a rich list or a popularity contest. It is a yearly list of the top Nigerian men in ten fields: men building companies, healing patients, teaching children, making art and serving the public with distinction.</p><p>Each year our editors, guided by an independent advisory panel, choose the men who lead each category. The list is published in full, with a profile of every man on it.</p></div></section>
<section class="band" id="the-categories"><div class="wrap">${secHead('The categories')}<ol class="cats">${D.CATEGORIES_100.map((x, i) => `<li><span>${String(i + 1).padStart(2, '0')}</span>${esc(x)}</li>`).join('')}</ol></div></section>
<section class="wrap pblock" id="how-we-choose">${secHead('How we choose')}<div class="grid grid-4 crit">${criteria.map(([t, d], i) => `<div class="crit__i"><span class="crit__n">${i + 1}</span><h3>${t}</h3><p>${d}</p></div>`).join('')}</div></section>
<section class="wrap pblock" id="the-men-on-the-list">${secHead('The men on the list', `${c.root}solid-men/`, 'All SOLID Men')}<div class="grid grid-4">${D.HONOUREES.map((p) => person(c, p)).join('')}</div></section>
<section class="band" id="previous-editions"><div class="wrap split">${secHead('Previous editions')}<div class="prose"><p>The inaugural SOLID 100 will be published in 2027. Past editions will be archived here, with every profile available to read in full.</p><p><a class="btn btn--red" href="${c.root}about/#contact">Suggest a name ${icon.arrow}</a></p></div></div></section>
${newsletter(c)}`;
}

export function conversationsPage(c) {
  const [first, ...rest] = D.CONVERSATIONS;
  return phead(c, { crumb: 'SOLID Conversations', title: 'SOLID Conversations', lede: 'Long-form interviews with men worth listening to. Watch, listen or read.', chips: D.FRANCHISES[1].subs.map((x) => [x, `#${D.slugify(x)}`]) })
    + `<section class="wrap pblock" id="video-interviews">${convCard(c, first, 'conv--lead', [1440, 810])}</section>
<section class="wrap pblock" id="podcast-episodes">${secHead('Latest episodes')}<div class="grid grid-3">${rest.map((v) => convCard(c, v)).join('')}</div></section>
<section class="band" id="interview-transcripts"><div class="wrap">${secHead('Interview transcripts')}<div class="grid grid-3">${D.bySection('solid-men').slice(1, 4).map((s) => card(c, s, 'std', { dek: true })).join('')}</div></div></section>
${newsletter(c)}`;
}

export function aboutPage(c) {
  return phead(c, { crumb: 'About', title: 'About SOLID', lede: 'A platform for ambition. A community for success.', chips: D.COMPANY[0].subs.map((x) => [x, `#${D.slugify(x)}`]) })
    + `<section class="wrap pblock split" id="our-story"><div>${secHead('Our story')}</div><div class="prose"><p class="drop">SOLID is Nigeria’s online magazine for men who are committed to growth: in business, in life and in themselves. We inspire, inform and celebrate men who dream bigger, work harder and achieve more.</p><p>We started SOLID because the conversation about what it means to be a successful man had become too narrow. Money matters. So do health, family, character and the mark you leave. We cover all of it, with the seriousness it deserves.</p></div></section>
<section class="band" id="mission-and-vision"><div class="wrap grid grid-2 mv"><div><p class="kicker">Mission</p><h3>To equip Nigerian men with the ideas, examples and tools to build lives of substance.</h3></div><div><p class="kicker">Vision</p><h3>A generation of men known for what they build, how they lead and who they lift.</h3></div></div></section>
<section class="wrap pblock" id="editorial-team">${secHead('Editorial team')}<div class="grid grid-4">${D.AUTHORS.map((a) => person(c, a)).join('')}</div></section>
<section class="wrap pblock split" id="editorial-policy"><div>${secHead('Editorial policy')}</div><div class="prose"><p>Our journalism is independent. Advertisers and partners have no influence over editorial coverage, and sponsored content is always clearly labelled. When we get something wrong, we correct it promptly and transparently.</p><p><a href="${c.root}editorial-policy/">Read the full editorial policy</a> · <a href="${c.root}corrections-policy/">Corrections policy</a></p></div></section>
<section class="band" id="contact"><div class="wrap split">${secHead('Contact')}<div class="contact"><div><p class="kicker">Editorial</p><a href="mailto:editorial@solidng.com">editorial@solidng.com</a></div><div><p class="kicker">Partnerships</p><a href="mailto:partnerships@solidng.com">partnerships@solidng.com</a></div><div><p class="kicker">General</p><a href="mailto:hello@solidng.com">hello@solidng.com</a></div></div></div></section>`;
}

export function advertisePage(c) {
  const why = ['Highly engaged, aspirational audience', 'Trusted and credible editorial voice', 'Multi-platform visibility across web, social and newsletter', 'Alignment with success, ambition and excellence'];
  const opps = [['Display Ads', 'Premium placements across the homepage, sections and articles.'], ['Sponsored Articles', 'Clearly labelled stories written with our editorial standards.'], ['Branded Content', 'Custom series and features produced by the SOLID studio.'], ['Video Placements', 'Pre-roll and integrations across SOLID Conversations.'], ['Newsletter Sponsorship', 'Own the Sunday inbox of our most loyal readers.'], ['Events & Experiences', 'Partner on The SOLID 100 and our live gatherings.']];
  const stats = [['1M+', 'Monthly reach (est.)'], ['72%', 'Male audience'], ['25–44', 'Core age group'], ['Lagos, Abuja', 'Top cities and beyond']];
  return phead(c, { crumb: 'Advertise', title: 'Advertise with SOLID', lede: 'Partner. Advertise. Grow. Reach the men shaping Nigeria’s next decade.', chips: D.COMPANY[1].subs.map((x) => [x, `#${D.slugify(x)}`]) })
    + `<section class="wrap pblock" id="media-kit"><div class="stats">${stats.map(([n, l]) => `<div class="stat"><b>${n}</b><span>${l}</span></div>`).join('')}</div></section>
<section class="wrap pblock split" id="brand-partnerships"><div>${secHead('Why partner with SOLID')}</div><ul class="ticks">${why.map((x) => `<li>${x}</li>`).join('')}</ul></section>
<section class="band" id="advertising-opportunities"><div class="wrap">${secHead('Advertising opportunities')}<div class="grid grid-3 opps">${opps.map(([t, d], i) => `<div class="opp"><span class="opp__n">${String(i + 1).padStart(2, '0')}</span><h3>${t}</h3><p>${d}</p></div>`).join('')}</div></div></section>
<section class="wrap pblock split" id="sponsored-content-enquiries"><div>${secHead('Partner with SOLID today')}<p class="phead__l">Let’s create impact together. Tell us about your brand and our partnerships team will be in touch.</p><p><a class="btn btn--ghost" href="mailto:partnerships@solidng.com">partnerships@solidng.com</a></p></div>
<form class="form" data-newsletter><label>Name<input type="text" required></label><label>Company<input type="text"></label><label>Work email<input type="email" required></label><label>Interested in<select><option>Display advertising</option><option>Sponsored content</option><option>Brand partnership</option><option>Newsletter sponsorship</option><option>Events</option></select></label><label class="form__full">Message<textarea rows="4"></textarea></label><button class="btn btn--red" type="submit">Request the media kit</button></form></section>`;
}

export function legalPage(c, l) {
  const sections = ['Overview', 'What this covers', 'How we apply it', 'Your choices', 'Changes to this policy', 'Contact us'];
  return phead(c, { crumb: esc(l.name), title: esc(l.name), lede: 'Last updated 16 November 2026' })
    + `<section class="pblock"><div class="art__body prose">${sections.map((h) => `<h2>${h}</h2><p>This is placeholder text for the ${esc(l.name.toLowerCase())}. The final wording will be supplied by SOLID and reviewed before launch. It sets out, in plain language, how the magazine operates and what readers can expect from us.</p>`).join('')}</div></section>`;
}

export function authorPage(c, a) {
  const own = D.STORIES.filter((s) => s.author === a);
  return phead(c, { cls: 'phead--author', crumb: 'Authors', title: esc(a.name), lede: `${esc(a.role)} at SOLID. Writes about ambition, work and the business of living well.`, extra: `<span class="phead__av">${pic(a.img, 280, 280)}</span>` })
    + `<section class="wrap pblock">${secHead(`Stories by ${esc(a.name.split(' ')[0])}`)}<div class="grid grid-3">${own.map((s) => card(c, s, 'std', { dek: true, kick: 'section' })).join('')}</div></section>${newsletter(c)}`;
}

export function searchPage(c) {
  return phead(c, { crumb: 'Search', title: 'Search', lede: 'Find stories, people and topics across SOLID.', extra: `<div class="search__field search__field--page">${icon.search}<input type="search" data-search-input placeholder="Stories, people, topics…" aria-label="Search SOLID"></div><ul class="search__res" data-search-results></ul>` })
    + `<section class="wrap pblock">${secHead('Latest stories')}<div class="grid grid-4">${D.byDate(D.STORIES).slice(1, 9).map((s) => card(c, s, 'std', { kick: 'section' })).join('')}</div></section>`;
}

export const notFound = (c) =>
  phead(c, { crumb: 'Not found', title: 'Page not found', lede: 'The page you are looking for has moved or never existed.', extra: `<p style="margin-top:24px"><a class="btn btn--red" href="${c.root}">Back to the homepage ${icon.arrow}</a></p>` });
