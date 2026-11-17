// Design One — "The Editorial". Classic print-magazine language: serif masthead, ruled columns, light-first.
import * as D from '../data.mjs';
import { card, pic, icon, secHead, logo, logoSvg, themeToggle, navItems, newsletter, u, esc } from '../shared.mjs';

const header = (c) => `<div class="top"><div class="wrap top__in"><span>Monday, 16 November 2026</span><span class="top__mid">The magazine for men who want more</span><span class="top__r"><a href="${c.root}the-solid-100/">The SOLID 100</a><a href="${c.root}about/">About</a><a href="${c.root}advertise/">Advertise</a></span></div></div>
<header class="mast"><div class="wrap mast__in">
<div class="mast__l"><button class="mast__menu" type="button" data-menu-open>${icon.menu}<span>Sections</span></button><button class="icon-btn" type="button" data-search-open aria-label="Search">${icon.search}</button></div>
${logo(c, true)}
<div class="mast__r">${themeToggle()}<a class="btn btn--red" href="#newsletter">Subscribe</a></div>
</div></header>
<nav class="nav" aria-label="Sections"><div class="wrap nav__in"><a class="nav__logo" href="${c.root}" aria-hidden="true" tabindex="-1">${logoSvg()}</a><ul class="nav__l">${navItems(c, [...D.SECTIONS, { slug: 'the-solid-100', name: 'The SOLID 100' }, { slug: 'conversations', name: 'Conversations' }])}</ul></div></nav>`;

function home(c) {
  const q = (sec, n = 0) => D.bySection(sec)[n];
  const cover = D.STORIES[0];
  const latest = D.byDate([q('business'), q('wealth'), q('health'), q('lifestyle'), q('leadership'), q('culture')]);
  const trending = [q('wealth', 1), q('health', 3), q('lifestyle', 1), q('mind'), q('business', 4)];
  const men = D.bySection('solid-men').slice(1, 5);
  const [cv, ...eps] = D.CONVERSATIONS;
  const col = (slug) => {
    const s = D.section(slug), list = D.bySection(slug);
    return `<div class="gcol"><a class="gcol__h" href="${u.sec(c, slug)}"><span>${s.name}</span>${icon.arrow}</a>${card(c, list[0], 'std')}<ul class="gcol__l">${list.slice(1, 3).map((x) => `<li><a href="${u.story(c, x)}">${esc(x.title)}</a></li>`).join('')}</ul></div>`;
  };
  const tri = (slug) => `<div class="tri__c"><a class="gcol__h" href="${u.sec(c, slug)}"><span>${D.section(slug).name}</span>${icon.arrow}</a>${D.bySection(slug).slice(0, 3).map((x) => card(c, x, 'row', { meta: false })).join('')}</div>`;

  return `<section class="cover wrap">
<div class="cover__text" data-reveal><p class="cover__flag"><b>Cover Story</b><span>Issue 01 · November 2026</span></p>
<h1 class="cover__t"><a href="${u.story(c, cover)}">The <i>Long</i> Game</a></h1>
<p class="cover__dek">${esc(cover.dek)}</p>
<p class="meta"><span>By ${esc(cover.author.name)}</span><span>${cover.mins} min read</span></p>
<a class="btn btn--ghost" href="${u.story(c, cover)}">Read the cover story ${icon.arrow}</a></div>
<a class="cover__img" href="${u.story(c, cover)}" tabindex="-1">${pic(cover.img, 1040, 1300, 'Portrait of a smiling man in a blue suit', true)}<span class="cover__cap">Kolade Aremu, photographed in Lagos for SOLID</span></a>
<aside class="cover__side"><h2 class="rule-h">Latest</h2><ol class="latest">${latest.map((s, i) => `<li><span class="latest__m"><a class="kicker" href="${u.sec(c, s.section)}">${D.section(s.section).name}</a><time datetime="${s.iso}">${s.day}</time></span><h3><a href="${u.story(c, s)}">${esc(s.title)}</a></h3></li>`).join('')}</ol><a class="more" href="${c.root}search/">All stories ${icon.arrow}</a></aside>
</section>

<section class="trend"><div class="wrap trend__in"><div class="trend__h"><h2>Trending</h2><p>What readers are sharing this week</p></div>
<ol class="trend__l">${trending.map((s, i) => `<li><span class="trend__n">${i + 1}</span><div><a class="kicker" href="${u.sec(c, s.section)}">${D.section(s.section).name}</a><h3><a href="${u.story(c, s)}">${esc(s.title)}</a></h3></div></li>`).join('')}</ol></div></section>

<section class="wrap blk" data-reveal>${secHead('Business Spotlight', u.sec(c, 'business'), 'All Business')}
<div class="biz"><div class="biz__lead">${card(c, q('business', 1), 'wide', { dim: [1200, 760] })}</div><div class="biz__list">${[q('business'), q('business', 2), q('business', 3), q('business', 5)].map((s) => card(c, s, 'row')).join('')}</div></div></section>

<section class="men"><div class="wrap men__in"><div class="men__head" data-reveal><p class="kicker">Portraits of ambition</p><h2 class="men__t">SOLID <i>Men</i></h2><p>Profiles of the men defining what it means to build, lead and last.</p><a class="more" href="${u.sec(c, 'solid-men')}">Meet them all ${icon.arrow}</a></div>
<div class="men__grid">${men.map((s) => card(c, s, 'portrait', { meta: false })).join('')}</div></div></section>

<section class="wrap blk" data-reveal>${secHead('The Lifestyle Edit', u.sec(c, 'lifestyle'), 'All Lifestyle')}
<div class="edit">${card(c, q('lifestyle'), 'over', { dim: [900, 1180] })}<div class="edit__g">${[q('lifestyle', 1), q('lifestyle', 2), q('lifestyle', 7), q('lifestyle', 5)].map((s) => card(c, s, 'std', { dim: [720, 540], meta: false })).join('')}</div></div></section>

<section class="growth"><div class="wrap" data-reveal>${secHead('Growth &amp; Development', '', '', 'Mind · Leadership · Wealth · Health')}
<div class="growth__g">${['mind', 'leadership', 'wealth', 'health'].map(col).join('')}</div></div></section>

<section class="wrap blk" data-reveal>${secHead('SOLID Conversations', `${c.root}conversations/`, 'All episodes')}
<div class="convo"><a class="convo__main" href="${c.root}conversations/">${pic(cv.img, 1280, 800)}<span class="play">${icon.play}</span><span class="convo__txt"><span class="kicker">${cv.kind} · Ep. ${cv.ep} · ${cv.len}</span><b>${esc(cv.title)}</b><span>with ${esc(cv.guest)}</span></span></a>
<ol class="convo__l">${eps.map((v) => `<li><a href="${c.root}conversations/"><span class="convo__ep">Ep. ${v.ep}</span><span class="convo__b"><span class="kicker">${v.kind}</span><b>${esc(v.title)}</b><span>with ${esc(v.guest)} · ${v.len}</span></span><span class="convo__ic">${v.kind.startsWith('Podcast') ? icon.mic : icon.play}</span></a></li>`).join('')}</ol></div></section>

<section class="s100"><div class="wrap s100__in"><div class="s100__copy" data-reveal><p class="kicker">The annual recognition</p><h2 class="s100__t">The SOLID <i>100</i></h2><p>One hundred Nigerian men whose work, character and influence are setting the standard for a generation.</p><a class="btn" href="${c.root}the-solid-100/">Explore the list ${icon.arrow}</a></div>
<div class="s100__faces">${D.HONOUREES.map((p) => `<figure>${pic(p.img, 360, 440, '')}<figcaption>${esc(p.name)}<span>${esc(p.cat)}</span></figcaption></figure>`).join('')}</div></div></section>

<section class="wrap blk tri" data-reveal>${['family', 'culture', 'impact'].map(tri).join('')}</section>
${newsletter(c)}`;
}

export default {
  key: 'one', def: 'light', header, home,
  fonts: 'https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400..900;1,9..144,400..900&family=Inter:wght@400;500;600;700&family=Source+Serif+4:ital,opsz,wght@0,8..60,400..600;1,8..60,400&display=swap',
};
