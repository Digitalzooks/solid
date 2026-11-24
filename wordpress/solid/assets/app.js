(function () {
  var d = document, html = d.documentElement, root = html.getAttribute('data-root') || './';
  var $ = function (s, c) { return (c || d).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || d).querySelectorAll(s)); };

  // Light / dark mode
  $$('[data-theme-toggle]').forEach(function (b) {
    b.addEventListener('click', function () {
      var next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
      html.classList.add('theme-switch');
      html.setAttribute('data-theme', next);
      try { localStorage.setItem('solid-theme', next); } catch (e) {}
      setTimeout(function () { html.classList.remove('theme-switch'); }, 400);
    });
  });

  // Overlays: sections menu and search
  function overlay(id, openSel, closeSel, onOpen) {
    var el = d.getElementById(id); if (!el) return;
    var last;
    function open() { last = d.activeElement; el.setAttribute('aria-hidden', 'false'); html.classList.add('has-overlay'); onOpen && onOpen(el); }
    function close() { if (el.getAttribute('aria-hidden') === 'true') return; el.setAttribute('aria-hidden', 'true'); html.classList.remove('has-overlay'); last && last.focus && last.focus(); }
    $$(openSel).forEach(function (b) { b.addEventListener('click', open); });
    $$(closeSel, el).forEach(function (b) { b.addEventListener('click', close); });
    $$('a[href]', el).forEach(function (a) { a.addEventListener('click', close); });
    el.addEventListener('click', function (e) { if (e.target === el) close(); });
    d.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
  }
  overlay('menu', '[data-menu-open]', '[data-menu-close]');
  overlay('search', '[data-search-open]', '[data-search-close]', function (el) { setTimeout(function () { var i = $('input', el); i && i.focus(); }, 60); });

  // Section topic filter
  var scope = $('[data-filter-scope]'), fbar = $('[data-filter-bar]');
  if (scope && fbar) {
    var names = {};
    $$('[data-filter]', fbar).forEach(function (a) { names[a.getAttribute('data-filter')] = a.textContent; });
    var apply = function (key) {
      if (!names.hasOwnProperty(key)) key = '';
      $$('[data-filter]', fbar).forEach(function (a) { a.setAttribute('aria-current', a.getAttribute('data-filter') === key ? 'true' : 'false'); });
      var shown = 0;
      $$('.card[data-sub]', scope).forEach(function (cd) { var on = !key || cd.getAttribute('data-sub') === key; cd.hidden = !on; if (on) shown++; });
      $$('[data-filter-group]', scope).forEach(function (g) { g.hidden = !$$('.card[data-sub]', g).some(function (cd) { return !cd.hidden; }); });
      $('[data-filter-empty]', scope).hidden = shown > 0;
      $('[data-filter-name]', scope).textContent = names[key] || '';
      $('[data-filter-note]', scope).innerHTML = key && shown ? 'Showing <b>' + shown + '</b> ' + (shown === 1 ? 'story' : 'stories') + ' in <b>' + names[key] + '</b>' : '';
    };
    var fromHash = function () { apply(decodeURIComponent(location.hash.slice(1))); };
    $$('[data-filter]', d).forEach(function (a) {
      a.addEventListener('click', function (e) {
        e.preventDefault();
        var key = a.getAttribute('data-filter');
        history.replaceState(null, '', key ? '#' + key : location.pathname + location.search);
        apply(key);
        if (!fbar.contains(a)) fbar.scrollIntoView({ block: 'center', behavior: 'smooth' });
      });
    });
    window.addEventListener('hashchange', fromHash);
    fromHash();
  }

  // Search
  var index = null;
  function load(cb) {
    if (index) return cb(index);
    fetch(html.getAttribute('data-search-index')).then(function (r) { return r.json(); }).then(function (j) { index = j; cb(j); }).catch(function () { cb([]); });
  }
  $$('[data-search-input]').forEach(function (input) {
    var out = input.closest('.search__in, .phead__in').querySelector('[data-search-results]');
    function render(q) {
      load(function (all) {
        q = q.trim().toLowerCase();
        var hits = q ? all.filter(function (s) { return (s.t + ' ' + s.d + ' ' + s.k + ' ' + s.a).toLowerCase().indexOf(q) > -1; }).slice(0, 8) : [];
        out.innerHTML = hits.map(function (s) {
          return '<li><a href="' + s.u + '"><span class="kicker">' + s.k + '</span><b>' + s.t + '</b><small>' + s.d + '</small></a></li>';
        }).join('') || (q ? '<li class="search__none">No stories match “' + q.replace(/</g, '&lt;') + '”.</li>' : '');
      });
    }
    input.addEventListener('input', function () { render(input.value); });
  });

  // Copy link
  $$('[data-copy-link]').forEach(function (a) {
    a.addEventListener('click', function (e) {
      if (!navigator.clipboard) return;
      e.preventDefault();
      navigator.clipboard.writeText(a.href).then(function () { a.setAttribute('aria-label', 'Link copied'); a.classList.add('is-copied'); });
    });
  });

  // Forms (prototype: no submission)
  $$('[data-newsletter]').forEach(function (f) {
    f.addEventListener('submit', function (e) {
      e.preventDefault();
      f.innerHTML = '<p class="form__ok">Thank you. You’re on the list.</p>';
    });
  });

  // Newsletter (Fluent Forms blocks Enter in inputs): let Enter submit the one-field form
  d.addEventListener('keydown', function (e) {
    var i = e.target;
    if (e.key !== 'Enter' || !i.matches || !i.matches('.ff-solid--nl input.ff-el-form-control')) return;
    var b = i.form && i.form.querySelector('.ff-btn-submit');
    if (b) { e.preventDefault(); b.click(); }
  });

  // Horizontal rails
  $$('[data-rail]').forEach(function (wrap) {
    var track = $('[data-rail-track]', wrap); if (!track) return;
    $$('[data-rail-btn]', wrap).forEach(function (b) {
      b.addEventListener('click', function () { track.scrollBy({ left: (b.getAttribute('data-rail-btn') === 'prev' ? -1 : 1) * track.clientWidth * 0.8, behavior: 'smooth' }); });
    });
  });

  // Header state and reading progress
  var bar = d.getElementById('progress'), art = $('.art__body');
  function onScroll() {
    html.classList.toggle('scrolled', window.scrollY > 80);
    if (bar && art) {
      var r = art.getBoundingClientRect(), total = r.height - window.innerHeight * 0.5;
      bar.style.transform = 'scaleX(' + Math.max(0, Math.min(1, -r.top / total + 0.15)) + ')';
    }
  }
  window.addEventListener('scroll', onScroll, { passive: true }); onScroll();

  // Reveal on scroll
  if ('IntersectionObserver' in window && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
    var io = new IntersectionObserver(function (es) { es.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } }); }, { rootMargin: '0px 0px -8% 0px' });
    $$('[data-reveal]').forEach(function (el) { io.observe(el); });
  } else { $$('[data-reveal]').forEach(function (el) { el.classList.add('in'); }); }
})();
