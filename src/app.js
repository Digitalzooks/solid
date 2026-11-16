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
    el.addEventListener('click', function (e) { if (e.target === el) close(); });
    d.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
  }
  overlay('menu', '[data-menu-open]', '[data-menu-close]');
  overlay('search', '[data-search-open]', '[data-search-close]', function (el) { setTimeout(function () { var i = $('input', el); i && i.focus(); }, 60); });

  // Search
  var index = null;
  function load(cb) {
    if (index) return cb(index);
    fetch(root + 'assets/index.json').then(function (r) { return r.json(); }).then(function (j) { index = j; cb(j); }).catch(function () { cb([]); });
  }
  $$('[data-search-input]').forEach(function (input) {
    var out = input.closest('.search__in, .phead__in').querySelector('[data-search-results]');
    function render(q) {
      load(function (all) {
        q = q.trim().toLowerCase();
        var hits = q ? all.filter(function (s) { return (s.t + ' ' + s.d + ' ' + s.k + ' ' + s.a).toLowerCase().indexOf(q) > -1; }).slice(0, 8) : [];
        out.innerHTML = hits.map(function (s) {
          return '<li><a href="' + root + 'story/' + s.u + '/"><span class="kicker">' + s.k + '</span><b>' + s.t + '</b><small>' + s.d + '</small></a></li>';
        }).join('') || (q ? '<li class="search__none">No stories match “' + q.replace(/</g, '&lt;') + '”.</li>' : '');
      });
    }
    input.addEventListener('input', function () { render(input.value); });
  });

  // Forms (prototype: no submission)
  $$('[data-newsletter]').forEach(function (f) {
    f.addEventListener('submit', function (e) {
      e.preventDefault();
      f.innerHTML = '<p class="form__ok">Thank you. You’re on the list.</p>';
    });
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
