// Design Two — "The Statement". Dark-first, cinematic, condensed uppercase headlines and hard red blocks.
import * as D from '../data.mjs';
import { card, pic, icon, secHead, logo, themeToggle, navItems, newsletter, convCard, u, esc } from '../shared.mjs';

const header = (c) => `<header class="bar"><div class="wrap bar__in">
${logo(c)}
<nav aria-label="Sections"><ul class="nav__l">${navItems(c, [...D.SECTIONS, { slug: 'the-solid-100', name: 'SOLID 100' }, { slug: 'conversations', name: 'Conversations' }])}</ul></nav>
<div class="bar__r"><button class="icon-btn" type="button" data-search-open aria-label="Search">${icon.search}</button>${themeToggle()}<a class="btn btn--red bar__sub" href="#newsletter">Subscribe</a><button class="icon-btn bar__menu" type="button" data-menu-open aria-label="Open menu">${icon.menu}</button></div>
</div></header>`;

function home(c) {
  const q = (sec, n = 0) => D.bySection(sec)[n];
  const cover = D.STORIES[0];
  const inIssue = [q('business', 1), q('wealth'), q('solid-men', 1)];
  const tick = [q('wealth', 1), q('health', 3), q('lifestyle', 1), q('mind'), q('business', 4), q('culture', 1)];
  const trending = [q('wealth', 1), q('health', 3), q('lifestyle', 1), q('mind'), q('business', 4)];
  const [cv, ...eps] = D.CONVERSATIONS;
  const rows = ['mind', 'leadership', 'wealth', 'health', 'family', 'culture', 'impact'];
  const ticker = tick.map((s) => `<a href="${u.story(c, s)}">${esc(s.title)}</a><i></i>`).join('');
  const names = D.HONOUREES.map((p) => `<span>${esc(p.name)}</span><i></i>`).join('');

  return `<section class="hero"><div class="hero__img">${pic(cover.img, 1700, 1200, 'Portrait of a smiling man in a blue suit', true)}</div>
<div class="wrap hero__in"><div class="hero__copy"><p class="hero__flag"><b>Cover Story</b><span>Issue 01 — November 2026</span></p>
<h1 class="hero__t"><a href="${u.story(c, cover)}">The Long <em>Game</em></a></h1>
<p class="hero__dek">${esc(cover.dek)}</p>
<div class="hero__cta"><a class="btn btn--red" href="${u.story(c, cover)}">Read the cover story ${icon.arrow}</a><span class="meta"><span>By ${esc(cover.author.name)}</span><span>${cover.mins} min read</span></span></div></div>
<ol class="hero__also">${inIssue.map((s, i) => `<li><a href="${u.story(c, s)}"><span class="hero__n">0${i + 1}</span><span><span class="kicker">${D.section(s.section).name}</span><b>${esc(s.title)}</b></span></a></li>`).join('')}</ol></div></section>

<div class="tick" aria-label="Trending now"><b>Trending</b><div class="tick__w"><div class="tick__t">${ticker}${ticker}</div></div></div>

<section class="wrap idx" aria-label="Sections"><ul class="idx__g">${D.SECTIONS.map((s, i) => `<li><a href="${u.sec(c, s.slug)}"><span class="idx__n">${String(i + 1).padStart(2, '0')}</span><b>${s.name}</b><span class="idx__l">${s.line}</span>${icon.arrow}</a></li>`).join('')}</ul></section>

<section class="wrap blk">${secHead('Latest <em>Stories</em>', `${c.root}search/`, 'All stories')}
<div class="lat"><div class="mosaic" data-reveal>${card(c, q('business'), 'over', { cls: 'm-a', kick: 'section', dim: [1200, 1000] })}${[q('health'), q('lifestyle'), q('leadership'), q('culture')].map((s) => card(c, s, 'over', { dek: false, kick: 'section', dim: [720, 600] })).join('')}</div>
<aside class="most"><h3 class="most__h">Most read</h3><ol>${trending.map((s, i) => `<li><span class="most__n">${i + 1}</span><div><a class="kicker" href="${u.sec(c, s.section)}">${D.section(s.section).name}</a><h4><a href="${u.story(c, s)}">${esc(s.title)}</a></h4></div></li>`).join('')}</ol></aside></div></section>

<section class="spot" data-reveal><a class="spot__img" href="${u.story(c, q('business', 1))}" tabindex="-1">${pic(q('business', 1).img, 1400, 1000)}</a>
<div class="spot__copy"><p class="spot__k">Business Spotlight</p><h2 class="spot__t"><a href="${u.story(c, q('business', 1))}">${esc(q('business', 1).title)}</a></h2><p>${esc(q('business', 1).dek)}</p><a class="btn" href="${u.story(c, q('business', 1))}">Read the story ${icon.arrow}</a></div></section>
<section class="wrap spot__more"><div class="grid grid-4">${[q('business', 2), q('business', 3), q('business', 4), q('business', 5)].map((s) => card(c, s, 'std')).join('')}</div></section>

<section class="men" data-rail><div class="wrap men__h"><h2 class="men__t">Solid <em>Men</em></h2><p>Portraits of ambition. The men defining what it means to build, lead and last.</p><div class="men__ctl"><button class="icon-btn" type="button" data-rail-btn="prev" aria-label="Previous">${icon.left}</button><button class="icon-btn" type="button" data-rail-btn="next" aria-label="Next">${icon.arrow}</button><a class="more" href="${u.sec(c, 'solid-men')}">All profiles ${icon.arrow}</a></div></div>
<div class="men__rail" data-rail-track>${D.bySection('solid-men').map((s, i) => card(c, s, 'over', { dek: false, meta: false, n: i + 1, dim: [700, 980] })).join('')}</div></section>

<section class="wrap blk" data-reveal>${secHead('The Lifestyle <em>Edit</em>', u.sec(c, 'lifestyle'), 'All Lifestyle')}
<div class="bento">${card(c, q('lifestyle'), 'over', { cls: 'b-a', dim: [1100, 1100] })}${card(c, q('lifestyle', 2), 'over', { cls: 'b-b', dek: false, dim: [1200, 560] })}${card(c, q('lifestyle', 1), 'over', { cls: 'b-c', dek: false, dim: [640, 560] })}${card(c, q('lifestyle', 7), 'over', { cls: 'b-c', dek: false, dim: [640, 560] })}</div></section>

<section class="grow"><div class="wrap">${secHead('Growth <em>&amp;</em> Development', '', '')}
<ul class="grow__l">${rows.map((slug, i) => { const s = q(slug), sec = D.section(slug); return `<li><a href="${u.story(c, s)}"><span class="grow__n">${String(i + 1).padStart(2, '0')}</span><span class="grow__s">${sec.name}</span><span class="grow__b"><b>${esc(s.title)}</b><span>${esc(s.sub)} · ${s.mins} min read</span></span><span class="grow__img">${pic(s.img, 420, 280)}</span>${icon.arrow}</a></li>`; }).join('')}</ul></div></section>

<section class="wrap blk" data-reveal>${secHead('Solid <em>Conversations</em>', `${c.root}conversations/`, 'All episodes')}
<div class="talk">${convCard(c, cv, 'talk__main', [1400, 800])}<div class="talk__l">${eps.map((v) => convCard(c, v, 'talk__i', [480, 320])).join('')}</div></div></section>

<section class="s100"><div class="s100__names" aria-hidden="true"><div class="tick__t">${names}${names}</div></div>
<div class="wrap s100__in" data-reveal><p class="kicker">The annual recognition</p><h2 class="s100__t"><span>The Solid</span><b>100</b></h2><p class="s100__d">One hundred Nigerian men whose work, character and influence are setting the standard for a generation.</p><a class="btn btn--red" href="${c.root}the-solid-100/">Explore the list ${icon.arrow}</a></div>
<div class="s100__faces">${D.HONOUREES.map((p) => `<figure>${pic(p.img, 360, 440, '')}<figcaption>${esc(p.name)}</figcaption></figure>`).join('')}</div></section>
${newsletter(c)}`;
}

export default {
  key: 'two', def: 'dark', header, home,
  fonts: 'https://fonts.googleapis.com/css2?family=Archivo:ital,wdth,wght@0,62..125,100..900;1,62..125,100..900&display=swap',
};
