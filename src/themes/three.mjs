// Design Three — "The Modern". Digital-first: floating pill navigation, rounded bento grid, soft surfaces.
import * as D from '../data.mjs';
import { card, pic, icon, secHead, logo, themeToggle, navItems, newsletter, u, esc } from '../shared.mjs';

const header = (c) => `<header class="pill"><div class="pill__in">
${logo(c, false)}
<nav aria-label="Sections"><ul class="nav__l">${navItems(c, [...D.SECTIONS, { slug: 'the-solid-100', name: 'The 100' }, { slug: 'conversations', name: 'Conversations' }])}</ul></nav>
<div class="pill__r"><button class="icon-btn" type="button" data-search-open aria-label="Search">${icon.search}</button>${themeToggle()}<a class="btn btn--red pill__sub" href="#newsletter">Subscribe</a><button class="icon-btn pill__menu" type="button" data-menu-open aria-label="Open menu">${icon.menu}</button></div>
</div></header>`;

function home(c) {
  const q = (sec, n = 0) => D.bySection(sec)[n];
  const cover = D.STORIES[0];
  const trending = [q('wealth', 1), q('health', 3), q('lifestyle', 1), q('mind')];
  const latest = D.byDate([q('business'), q('wealth'), q('health'), q('lifestyle'), q('leadership'), q('culture')]);
  const [cv, ...eps] = D.CONVERSATIONS;
  const bars = Array.from({ length: 56 }, (_, i) => `<i style="--h:${20 + Math.round(Math.abs(Math.sin(i * 1.7) * 55 + Math.cos(i * 0.6) * 25))}%"></i>`).join('');
  const gcard = (slug) => {
    const s = D.section(slug), list = D.bySection(slug);
    return `<div class="g"><a class="g__h" href="${u.sec(c, slug)}"><span><b>${s.name}</b><small>${s.line}</small></span><span class="g__go">${icon.arrow}</span></a>${card(c, list[0], 'std', { meta: false, dim: [640, 400] })}<ul class="g__l">${list.slice(1, 3).map((x) => `<li><a href="${u.story(c, x)}">${esc(x.title)}</a></li>`).join('')}</ul></div>`;
  };
  const tri = (slug) => `<div class="g g--list"><a class="g__h" href="${u.sec(c, slug)}"><span><b>${D.section(slug).name}</b><small>${D.section(slug).line}</small></span><span class="g__go">${icon.arrow}</span></a>${D.bySection(slug).slice(0, 3).map((x) => card(c, x, 'mini', { meta: false })).join('')}</div>`;

  return `<section class="wrap intro"><p class="intro__d">Monday, 16 November · Issue 01</p><h1 class="intro__t">Grow. Achieve. <em>Thrive.</em></h1>
<ul class="chips intro__chips">${D.SECTIONS.map((s) => `<li><a href="${u.sec(c, s.slug)}">${s.name}</a></li>`).join('')}</ul></section>

<section class="wrap bento">
<article class="b b--cover"><div class="b__media">${pic(cover.img, 1400, 1300, 'Portrait of a smiling man in a blue suit', true)}</div>
<div class="b__glass"><p class="tagrow"><span class="tag tag--red">Cover story</span><span class="tag">${cover.mins} min read</span></p><h2 class="b__t"><a href="${u.story(c, cover)}">${esc(cover.title)}</a></h2><p class="b__d">${esc(cover.dek)}</p><p class="b__by"><span class="av">${pic(cover.author.img, 80, 80)}</span>By ${esc(cover.author.name)}<span class="b__go">${icon.arrow}</span></p></div></article>
<div class="b b--trend"><div class="b__h"><h2>Trending</h2><span class="live">This week</span></div><ol>${trending.map((s, i) => `<li><span class="b__n">${i + 1}</span><a href="${u.story(c, s)}"><small>${D.section(s.section).name}</small><b>${esc(s.title)}</b></a></li>`).join('')}</ol></div>
<a class="b b--100" href="${c.root}the-solid-100/"><span class="tag tag--glass">Annual recognition</span><span class="b100__n">100</span><span class="b100__t">The SOLID 100 ${icon.arrow}</span></a>
<a class="b b--talk" href="${c.root}conversations/">${pic(cv.img, 800, 640)}<span class="tag tag--glass">${cv.kind} · ${cv.len}</span><span class="b--talk__b"><span class="play">${icon.play}</span><span><b>${esc(cv.title)}</b><small>with ${esc(cv.guest)}</small></span></span></a>
</section>

<section class="wrap blk" data-reveal>${secHead('Latest stories', `${c.root}search/`, 'All stories')}<div class="grid grid-3">${latest.map((s) => card(c, s, 'std', { kick: 'section' })).join('')}</div></section>

<section class="wrap" data-reveal><div class="panel">${secHead('Business Spotlight', u.sec(c, 'business'), 'All Business', 'Build. Scale. Lead.')}
<div class="biz"><div class="biz__lead">${card(c, q('business', 1), 'wide', { dim: [1200, 720] })}</div><div class="biz__list">${[q('business', 2), q('business', 3), q('business', 4), q('business', 5)].map((s) => card(c, s, 'row', { meta: false })).join('')}</div></div></div></section>

<section class="wrap blk" data-reveal><div class="panel panel--dark">${secHead('SOLID Men', u.sec(c, 'solid-men'), 'Meet them all', 'Portraits of ambition')}
<div class="men">${D.bySection('solid-men').slice(1, 5).map((s) => card(c, s, 'over', { dek: false, meta: false, dim: [640, 860] })).join('')}</div></div></section>

<section class="wrap edit" data-reveal><div class="edit__s"><p class="kicker">Live well. Live deliberately.</p><h2 class="sec-title">The Lifestyle Edit</h2><p>Style, craft and the considered details of a life well lived.</p><ul class="chips">${D.section('lifestyle').subs.map((x) => `<li><a href="${u.sub(c, 'lifestyle', x)}">${esc(x)}</a></li>`).join('')}</ul><a class="btn btn--ghost" href="${u.sec(c, 'lifestyle')}">All Lifestyle ${icon.arrow}</a></div>
<div class="edit__g">${[q('lifestyle'), q('lifestyle', 1), q('lifestyle', 2), q('lifestyle', 7)].map((s) => card(c, s, 'std', { dim: [720, 720] })).join('')}</div></section>

<section class="wrap blk" data-reveal>${secHead('Growth &amp; Development', '', '', 'The inner work behind outer success')}<div class="grow">${['mind', 'leadership', 'wealth', 'health'].map(gcard).join('')}</div></section>

<section class="wrap" data-reveal><div class="panel panel--dark player"><div class="player__art">${pic(cv.img, 900, 900)}<span class="play">${icon.play}</span></div>
<div class="player__b"><p class="kicker">SOLID Conversations · Ep. ${cv.ep}</p><h2 class="player__t"><a href="${c.root}conversations/">${esc(cv.title)}</a></h2><p class="player__g">with ${esc(cv.guest)}, ${cv.role.toLowerCase()}</p><p class="player__d">${esc(cv.dek)}</p>
<div class="wave" aria-hidden="true">${bars}</div><div class="player__time"><span>12:40</span><span>${cv.len}</span></div>
<ul class="player__l">${eps.map((v) => `<li><a href="${c.root}conversations/"><span class="player__ic">${v.kind.startsWith('Podcast') ? icon.mic : icon.play}</span><span><b>${esc(v.title)}</b><small>${v.kind} · with ${esc(v.guest)}</small></span><span class="player__len">${v.len}</span></a></li>`).join('')}</ul></div></div></section>

<section class="wrap blk" data-reveal><div class="panel panel--red s100"><div class="s100__c"><p class="kicker">The annual recognition</p><h2 class="s100__t">The SOLID <b>100</b></h2><p>One hundred Nigerian men whose work, character and influence are setting the standard for a generation.</p><a class="btn" href="${c.root}the-solid-100/">Explore the list ${icon.arrow}</a></div>
<div class="s100__f">${D.HONOUREES.map((p) => `<figure>${pic(p.img, 300, 300, '')}<figcaption><b>${esc(p.name)}</b>${esc(p.cat)}</figcaption></figure>`).join('')}</div></div></section>

<section class="wrap tri" data-reveal>${['family', 'culture', 'impact'].map(tri).join('')}</section>
${newsletter(c)}`;
}

export default {
  key: 'three', def: 'light', header, home,
  fonts: 'https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400..800&family=Inter:wght@400;500;600;700&family=Newsreader:ital,opsz,wght@0,6..72,400..600;1,6..72,400&display=swap',
};
