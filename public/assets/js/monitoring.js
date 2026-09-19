/* ==========================================================================
   SIPANDHALU — Dashboard Monitoring Operasional (interaktif)
   Vanilla JS, tanpa dependensi eksternal (syarat: 100% offline / intranet BPS).
   Grafik dirender sebagai SVG native.
   ========================================================================== */
(function () {
  'use strict';

  var CFG = window.MON_CONFIG || {};
  var POLL_INTERVAL = 45000;      /* 45 detik saat tab aktif */
  var DEBOUNCE_MS = 300;

  /* ------------------------------------------------------------------ util */

  var $ = function (sel, root) { return (root || document).querySelector(sel); };
  var $$ = function (sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); };

  function esc(v) {
    return String(v === null || v === undefined ? '' : v)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
  }

  function fmtInt(n) {
    var v = Number(n || 0);
    return v.toLocaleString('id-ID');
  }

  function fmtPct(n, dec) {
    if (n === null || n === undefined) { return '\u2014'; }
    return Number(n).toLocaleString('id-ID', { minimumFractionDigits: dec === 0 ? 0 : 1, maximumFractionDigits: dec === 0 ? 0 : 1 }) + '%';
  }

  function fmtDate(s) {
    if (!s) { return '\u2014'; }
    var d = new Date(String(s).replace(' ', 'T'));
    if (isNaN(d.getTime())) { return String(s); }
    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
  }

  function debounce(fn, ms) {
    var t = null;
    return function () {
      var args = arguments, self = this;
      clearTimeout(t);
      t = setTimeout(function () { fn.apply(self, args); }, ms);
    };
  }

  function toneClass(t) {
    return ['ok', 'warn', 'danger', 'neutral'].indexOf(t) >= 0 ? t : 'neutral';
  }

  function toneIcon(t) {
    return ({ ok: 'fa-circle-check', warn: 'fa-triangle-exclamation', danger: 'fa-circle-exclamation' })[t] || 'fa-circle-info';
  }

  function toneBadge(t, label) {
    var c = toneClass(t);
    return '<span class="mon-badge-tone mon-badge-tone--' + c + '"><i class="fas ' + toneIcon(c) + '" aria-hidden="true"></i>' + esc(label) + '</span>';
  }

  /* --------------------------------------------------------------- state */

  var state = {
    filters: {},
    seriesHidden: {},
    selection: [],
    openDrawerId: null,
    pollTimer: null,
    lastSuccess: 0,
    loading: false
  };

  var els = {};

  function cacheEls() {
    els.live = $('#monLive');
    els.liveText = $('#monLiveText');
    els.stamp = $('#monStamp');
    els.filters = $('#monFilters');
    els.filterToggle = $('#monFilterToggle');
    els.filterSummary = $('#monFilterSummary');
    els.kpiGrid = $('#monKpiGrid');
    els.stack = $('#monStack');
    els.layer = $('#monLayer');
    els.trend = $('#monTrendChart');
    els.trendLegend = $('#monTrendLegend');
    els.trendMeta = $('#monTrendMeta');
    els.donut = $('#monDonutChart');
    els.donutLegend = $('#monDonutLegend');
    els.rank = $('#monRank');
    els.rankMeta = $('#monRankMeta');
    els.kendala = $('#monKendala');
    els.consistency = $('#monConsistency');
    els.gridBody = $('#monGridBody');
    els.cards = $('#monCards');
    els.pager = $('#monPager');
    els.gridInfo = $('#monGridInfo');
    els.selection = $('#monSelection');
    els.tooltip = $('#monTooltip');
    els.drawer = $('#monDrawer');
    els.drawerBackdrop = $('#monDrawerBackdrop');
    els.drawerTitle = $('#monDrawerTitle');
    els.drawerSub = $('#monDrawerSub');
    els.drawerBody = $('#monDrawerBody');
    els.drawerFoot = $('#monDrawerFoot');
    els.meta = $('#monMeta');
  }

  /* --------------------------------------------------- status koneksi data */

  function setLive(stateName, text) {
    if (!els.live) { return; }
    els.live.setAttribute('data-state', stateName);
    if (els.liveText) { els.liveText.textContent = text; }
    els.live.setAttribute('aria-label', 'Status koneksi data: ' + text);
  }

  /* ------------------------------------------------- URL <-> state filter */

  function filtersToQuery(f) {
    var q = [];
    Object.keys(f || {}).forEach(function (k) {
      var v = f[k];
      if (v === null || v === undefined || v === '') { return; }
      if ((k === 'desa_id' || k === 'pengolah_id' || k === 'pcl_id' || k === 'pml_id') && (v === 0 || v === '0')) { return; }
      if (v === 0 && k !== 'has_error') { return; }
      q.push(encodeURIComponent(k) + '=' + encodeURIComponent(v));
    });
    return q.join('&');
  }

  function syncUrl(replace) {
    var url = CFG.baseUrl + '?' + filtersToQuery(state.filters);
    try {
      if (replace && window.history.replaceState) {
        window.history.replaceState({ mon: true }, '', url);
      } else if (window.history.pushState) {
        window.history.pushState({ mon: true }, '', url);
      }
    } catch (e) { /* diabaikan: URL tetap benar di server */ }
    try { localStorage.setItem(CFG.storageKey, JSON.stringify(state.filters)); } catch (e) {}
  }

  function readFiltersFromForm() {
    var f = {};
    $$('[data-filter]', els.filters || document).forEach(function (el) {
      var key = el.getAttribute('data-filter');
      if (el.type === 'checkbox') { return; }
      f[key] = el.value;
    });
    return f;
  }

  function applyFiltersToForm(f) {
    $$('[data-filter]', els.filters || document).forEach(function (el) {
      var key = el.getAttribute('data-filter');
      var val = f[key];
      if ((key === 'desa_id' || key === 'pengolah_id' || key === 'pcl_id' || key === 'pml_id') && (val === 0 || val === '0')) {
        val = '';
      }
      el.value = (val === null || val === undefined) ? '' : String(val);
    });
    /* Chip preset rentang waktu */
    $$('.mon-chip[data-range]').forEach(function (btn) {
      var active = btn.getAttribute('data-range') === String(f.range || 'periode');
      btn.setAttribute('aria-pressed', active ? 'true' : 'false');
    });
    handleKecamatanChange(f.kec || '');
  }

  /* --------------------------------------------------------------- fetch */

  function skeleton() {
    var s = '<div class="mon-skeleton">'
      + '<div class="mon-skel-line mon-skel-line--md"></div>'
      + '<div class="mon-skel-line mon-skel-line--sm"></div>'
      + '<div class="mon-skel-block"></div></div>';
    if (els.gridBody) { els.gridBody.innerHTML = '<tr><td colspan="9" class="p-3">' + s + '</td></tr>'; }
    if (els.cards) { els.cards.innerHTML = s; }
  }

  function toast(msg, isError) {
    var el = document.createElement('div');
    el.className = 'mon-toast' + (isError ? ' mon-toast--err' : '');
    el.setAttribute('role', 'status');
    el.innerHTML = '<i class="fas ' + (isError ? 'fa-circle-exclamation' : 'fa-circle-check') + '" aria-hidden="true"></i><span>' + esc(msg) + '</span>';
    document.body.appendChild(el);
    setTimeout(function () { if (el.parentNode) { el.parentNode.removeChild(el); } }, 4200);
  }

  function load(opts) {
    if (state.loading) { return; }
    state.loading = true;
    if (els.filters) { els.filters.classList.add('mon-filters--loading'); }
    var options = opts || {};
    if (options.skeleton) { skeleton(); }
    setLive('syncing', 'Memuat\u2026');

    var url = CFG.dataUrl + '?' + filtersToQuery(state.filters);
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
      .then(function (r) {
        if (!r.ok) { throw new Error('HTTP ' + r.status); }
        return r.json();
      })
      .then(function (json) {
        if (!json || !json.ok) { throw new Error((json && json.message) || 'Respons tidak valid'); }
        state.lastSuccess = Date.now();
        renderAll(json);
        setLive('live', 'Live');
        if (options.toast) { toast(options.toast); }
      })
      .catch(function (err) {
        setLive('offline', 'Offline');
        toast('Gagal memuat data: ' + err.message, true);
      })
      .then(function () {
        state.loading = false;
        if (els.filters) { els.filters.classList.remove('mon-filters--loading'); }
      });
  }

  /* ---------------------------------------------------------------- render */

  function renderAll(json) {
    state.lastSuccess = Date.now();
    var payload = json.payload || {};
    var grid = json.grid || {};
    if (json.filters) {
      state.filters = json.filters;
      applyFiltersToForm(state.filters);
    }
    renderKpi(payload.kpi || []);
    renderStacked(payload.stacked || {});
    renderTrend(payload.trend || {});
    renderKomposisi(payload.komposisi || {});
    renderRank(payload.beban || {});
    renderKendala(payload.kendala || {});
    renderConsistency(payload.consistency || {});
    renderMeta(payload.meta || {});
    renderGrid(grid);
    renderFilterSummary(payload.meta || {});
  }

  /* ------------------------------------------------------------ sparkline */

  function sparkline(series, tone) {
    var data = (series || []).map(Number);
    if (data.length === 0) { return ''; }
    if (data.length === 1) { data = [data[0], data[0]]; }
    var W = 100, H = 30, P = 2;
    var min = Math.min.apply(null, data);
    var max = Math.max.apply(null, data);
    var span = (max - min) || 1;
    var step = (W - P * 2) / (data.length - 1);
    var pts = data.map(function (v, i) {
      var x = P + i * step;
      var y = H - P - ((v - min) / span) * (H - P * 2);
      return [Math.round(x * 100) / 100, Math.round(y * 100) / 100];
    });
    var line = pts.map(function (p, i) { return (i === 0 ? 'M' : 'L') + p[0] + ' ' + p[1]; }).join(' ');
    var area = line + ' L' + pts[pts.length - 1][0] + ' ' + (H - 1) + ' L' + pts[0][0] + ' ' + (H - 1) + ' Z';
    var color = ({ ok: '#15803d', warn: '#b45309', danger: '#b91c1c' })[toneClass(tone)] || '#475569';
    var last = pts[pts.length - 1];

    return '<svg viewBox="0 0 ' + W + ' ' + H + '" preserveAspectRatio="none" role="img" aria-hidden="true" focusable="false">'
      + '<path d="' + area + '" fill="' + color + '" opacity="0.12"></path>'
      + '<path d="' + line + '" fill="none" stroke="' + color + '" stroke-width="1.6" stroke-linejoin="round" stroke-linecap="round"></path>'
      + '<circle cx="' + last[0] + '" cy="' + last[1] + '" r="2.4" fill="' + color + '" stroke="#fff" stroke-width="1"></circle>'
      + '</svg>';
  }

  /* ------------------------------------------------------------ Tier 1 KPI */

  function renderKpi(cards) {
    if (!els.kpiGrid) { return; }
    if (!cards.length) {
      els.kpiGrid.innerHTML = '<div class="mon-card mon-card__body mon-empty"><i class="fas fa-chart-simple mon-empty__icon" aria-hidden="true"></i><h3>Belum ada metrik</h3><p>Tidak ada data pada filter yang dipilih.</p></div>';
      return;
    }
    var idx = 0;
    els.kpiGrid.innerHTML = cards.map(function (c) {
      var tone = toneClass(c.tone);
      idx += 1;
      var rows = (c.detail || []).map(function (d) {
        return '<div class="mon-tooltip__row"><span>' + esc(d.label) + '</span><b>' + esc(d.value) + '</b></div>';
      }).join('');
      return '<div class="mon-kpi mon-kpi--' + tone + '" data-kpi="' + esc(c.key) + '" tabindex="0" role="group" aria-label="' + esc(c.aria) + '">'
        + '<div class="mon-kpi__top"><span class="mon-kpi__label">' + esc(c.label) + '</span>'
        + '<i class="fas ' + esc(c.icon) + ' mon-kpi__icon" aria-hidden="true"></i></div>'
        + '<div class="mon-kpi__value">' + esc(c.value_display) + '<small>' + esc(c.unit) + '</small></div>'
        + '<div class="mon-kpi__row"><span class="mon-kpi__pct">' + esc(c.pct_display) + '</span>'
        + '<span class="mon-kpi__delta">&Delta; ' + esc(c.delta_display) + '</span></div>'
        + '<div class="mon-kpi__spark" aria-hidden="true">' + sparkline(c.series, c.tone) + '</div>'
        + '<div class="mon-kpi__row">' + toneBadge(c.tone, c.tone_label) + '</div>'
        + '<div class="mon-kpi__hint">' + esc(c.hint) + '</div>'
        + '<div class="d-none" id="monKpiDetail' + idx + '">' + rows + '</div>'
        + '</div>';
    }).join('');

    $$('.mon-kpi', els.kpiGrid).forEach(function (card) {
      card.addEventListener('click', function () {
        var detail = $('#monKpiDetail' + ($$('.mon-kpi', els.kpiGrid).indexOf(card) + 1), els.kpiGrid);
        if (!detail) { return; }
        showHtmlTooltip(card, '<div class="mon-tooltip__title">' + esc($('.mon-kpi__label', card).textContent) + '</div>' + detail.innerHTML, true);
      });
      card.addEventListener('keydown', function (ev) {
        if (ev.key === 'Enter' || ev.key === ' ') { ev.preventDefault(); card.click(); }
      });
    });
  }

  /* ---------------------------------------------------- tooltip kontekstual */

  function showHtmlTooltip(anchor, html, keepOpen) {
    if (!els.tooltip || !anchor) { return; }
    els.tooltip.innerHTML = html;
    els.tooltip.setAttribute('data-show', '1');
    var wrap = anchor.closest('.mon-chart') || anchor.closest('.mon-card__body') || anchor.parentNode;
    var rect = anchor.getBoundingClientRect();
    var wrapRect = wrap.getBoundingClientRect();
    var x = rect.left - wrapRect.left + rect.width / 2;
    var y = rect.top - wrapRect.top;
    els.tooltip.style.left = Math.max(4, Math.min(x, wrapRect.width - 8)) + 'px';
    els.tooltip.style.top = Math.max(0, y - 8) + 'px';
    els.tooltip.style.transform = 'translate(-50%, -100%)';
    if (!keepOpen) {
      clearTimeout(els.tooltip._t);
      els.tooltip._t = setTimeout(hideTooltip, 2500);
    }
  }

  function hideTooltip() {
    if (els.tooltip) { els.tooltip.setAttribute('data-show', '0'); }
  }

  /* ------------------------------------------------ stacked progress bar */

  function renderStacked(s) {
    if (!els.stack) { return; }
    var target = Math.max(1, Number(s.target || 0));
    var items = s.items || [];
    var segs = items.map(function (it) {
      var w = Math.min(100, Math.max(0, Number(it.pct || 0)));
      return '<button type="button" class="mon-stack__seg" style="width:' + (w / items.length).toFixed(3) + '%;background:' + esc(it.color) + '"'
        + ' data-stage="' + esc(it.key) + '" aria-label="' + esc(it.aria) + '" title="' + esc(it.aria) + '"></button>';
    }).join('');

    els.stack.innerHTML =
      '<div class="mon-stack__bar" role="img" aria-label="Progres berlapis terhadap target ' + fmtInt(target) + ' ruta">' + segs + '</div>'
      + '<div class="mon-stack__legend">' + items.map(function (it) {
        return '<button type="button" class="mon-stack__item" data-stage="' + esc(it.key) + '" aria-label="' + esc(it.aria) + '">'
          + '<span class="mon-stack__swatch" style="background:' + esc(it.color) + '"></span>'
          + '<i class="fas ' + esc(it.icon) + '" aria-hidden="true"></i>'
          + esc(it.label) + ' <b>' + esc(it.n_display) + '</b> <span class="mon-muted">(' + esc(it.pct_display) + ')</span>'
          + '</button>';
      }).join('') + '</div>';

    if (els.layer) {
      els.layer.innerHTML = items.map(function (it) {
        return '<div class="mon-layer__row">'
          + '<span class="mon-layer__label"><i class="fas ' + esc(it.icon) + '" style="color:' + esc(it.color) + '" aria-hidden="true"></i>' + esc(it.label) + '</span>'
          + '<span class="mon-layer__track"><span class="mon-layer__fill" style="width:' + Math.min(100, Number(it.pct || 0)).toFixed(2) + '%;background:' + esc(it.color) + '"></span></span>'
          + '<span class="mon-layer__val">' + esc(it.n_display) + ' \u00b7 ' + esc(it.pct_display) + '</span>'
          + '</div>';
      }).join('');
    }

    $$('[data-stage]', els.stack).forEach(function (el) {
      el.addEventListener('click', function () {
        var stage = el.getAttribute('data-stage');
        var map = { dok_ada: { status_dokumen: 'ADA' }, transfer_k: { transfer_stage: 'K' }, transfer_kp: { transfer_stage: 'KP' } };
        crossFilter(map[stage] || {});
      });
    });
  }

  /* -------------------------------------------------- Tier 2: trend chart */

  function niceCeil(v) {
    if (v <= 0) { return 1; }
    var base = Math.pow(10, Math.floor(Math.log10(v)));
    var n = v / base;
    return (n <= 1 ? 1 : n <= 2 ? 2 : n <= 5 ? 5 : 10) * base;
  }

  function renderTrend(t) {
    if (!els.trend) { return; }
    var labels = t.labels || [];
    var allSeries = t.series || [];
    var target = t.target || { label: 'Target', color: '#94a3b8', values: [] };

    if (labels.length === 0) {
      els.trend.innerHTML = '<div class="mon-empty"><i class="fas fa-chart-area mon-empty__icon" aria-hidden="true"></i><h3>Belum ada aktivitas pada rentang ini</h3><p>Coba pilih preset rentang waktu yang lebih luas atau bersihkan filter.</p><button type="button" class="btn btn-sm btn-outline-secondary" data-reset-filter>Bersihkan Filter</button></div>';
      if (els.trendLegend) { els.trendLegend.innerHTML = ''; }
      return;
    }

    var series = allSeries.filter(function (s) { return !state.seriesHidden[s.key]; });
    var W = 760, H = 240, M = { l: 48, r: 14, t: 14, b: 28 };
    var iw = W - M.l - M.r, ih = H - M.t - M.b;
    var vals = [];
    series.forEach(function (s) { (s.values || []).forEach(function (v) { vals.push(Number(v) || 0); }); });
    (target.values || []).forEach(function (v) { vals.push(Number(v) || 0); });
    var maxV = niceCeil(Math.max.apply(null, vals.concat([1])));
    var n = labels.length;
    var px = function (i) { return M.l + (n > 1 ? (i * iw) / (n - 1) : iw / 2); };
    var py = function (v) { return M.t + ih - ((Number(v) || 0) / maxV) * ih; };

    var svg = ['<svg viewBox="0 0 ' + W + ' ' + H + '" role="img" aria-label="Tren kumulatif dibanding target linear, ' + n + ' titik waktu">'];

    for (var g = 0; g <= 4; g++) {
      var gv = (maxV / 4) * g;
      var gy = py(gv);
      svg.push('<line class="mon-grid-line" x1="' + M.l + '" y1="' + gy.toFixed(1) + '" x2="' + (W - M.r) + '" y2="' + gy.toFixed(1) + '"></line>');
      svg.push('<text x="' + (M.l - 6) + '" y="' + (gy + 3).toFixed(1) + '" text-anchor="end">' + fmtInt(Math.round(gv)) + '</text>');
    }

    var stepLabel = Math.max(1, Math.ceil(n / 7));
    for (var i = 0; i < n; i += stepLabel) {
      svg.push('<text x="' + px(i).toFixed(1) + '" y="' + (H - 8) + '" text-anchor="middle">' + esc(String(labels[i]).slice(5)) + '</text>');
    }
    svg.push('<line class="mon-axis-line" x1="' + M.l + '" y1="' + (M.t + ih) + '" x2="' + (W - M.r) + '" y2="' + (M.t + ih) + '"></line>');

    series.forEach(function (s) {
      var pts = (s.values || []).map(function (v, j) { return [px(j), py(v)]; });
      if (pts.length === 0) { return; }
      var line = pts.map(function (p, j) { return (j === 0 ? 'M' : 'L') + p[0].toFixed(1) + ' ' + p[1].toFixed(1); }).join(' ');
      var area = line + ' L' + pts[pts.length - 1][0].toFixed(1) + ' ' + (M.t + ih) + ' L' + pts[0][0].toFixed(1) + ' ' + (M.t + ih) + ' Z';
      svg.push('<path class="mon-series-area" d="' + area + '" fill="' + esc(s.color) + '"></path>');
      svg.push('<path class="mon-series-line" d="' + line + '" stroke="' + esc(s.color) + '"></path>');
    });

    if ((target.values || []).length) {
      var tline = target.values.map(function (v, j) { return (j === 0 ? 'M' : 'L') + px(j).toFixed(1) + ' ' + py(v).toFixed(1); }).join(' ');
      svg.push('<path class="mon-target-line" d="' + tline + '"></path>');
    }

    svg.push('<line class="mon-hover-line" id="monTrendHover" x1="0" y1="' + M.t + '" x2="0" y2="' + (M.t + ih) + '" style="display:none"></line>');
    svg.push('<rect id="monTrendOverlay" x="' + M.l + '" y="' + M.t + '" width="' + iw + '" height="' + ih + '" fill="transparent" style="cursor:crosshair"></rect>');
    svg.push('</svg>');
    els.trend.innerHTML = svg.join('');

    if (els.trendLegend) {
      els.trendLegend.innerHTML = allSeries.map(function (s) {
        var hidden = !!state.seriesHidden[s.key];
        return '<button type="button" class="mon-legend-item" data-series="' + esc(s.key) + '" aria-pressed="' + (hidden ? 'false' : 'true') + '">'
          + '<span class="mon-legend-swatch" style="background:' + esc(s.color) + '"></span>' + esc(s.label) + '</button>';
      }).join('') + '<span class="mon-legend-item"><span class="mon-legend-swatch" style="background:' + esc(target.color) + '"></span>' + esc(target.label) + '</span>';

      $$('[data-series]', els.trendLegend).forEach(function (btn) {
        btn.addEventListener('click', function () {
          var key = btn.getAttribute('data-series');
          state.seriesHidden[key] = !state.seriesHidden[key];
          renderTrend(t);
        });
      });
    }

    if (els.trendMeta) {
      els.trendMeta.textContent = ((t.meta && t.meta.basis) ? t.meta.basis : '') + ' \u00b7 ' + n + ' titik waktu';
    }

    var overlay = $('#monTrendOverlay', els.trend);
    var hoverLine = $('#monTrendHover', els.trend);
    if (!overlay || !hoverLine) { return; }

    overlay.addEventListener('mousemove', function (ev) {
      var box = overlay.getBoundingClientRect();
      var ratio = box.width > 0 ? (ev.clientX - box.left) / box.width : 0;
      var idx = Math.max(0, Math.min(n - 1, Math.round(ratio * (n - 1))));
      var xPos = px(idx);
      hoverLine.setAttribute('x1', xPos.toFixed(1));
      hoverLine.setAttribute('x2', xPos.toFixed(1));
      hoverLine.style.display = '';

      var daily = (t.daily || [])[idx] || {};
      var rata = daily.total ? Math.round(((daily.dok_ada || 0) / daily.total) * 100) : 0;
      var html = '<div class="mon-tooltip__title">' + esc(labels[idx]) + '</div>';
      allSeries.forEach(function (s) {
        if (state.seriesHidden[s.key]) { return; }
        html += '<div class="mon-tooltip__row"><span>' + esc(s.label) + '</span><b>' + fmtInt((s.values || [])[idx]) + '</b></div>';
      });
      html += '<div class="mon-tooltip__row"><span>' + esc(target.label) + '</span><b>' + fmtInt((target.values || [])[idx] || 0) + '</b></div>';
      html += '<div class="mon-tooltip__note">Aktivitas hari itu: ' + fmtInt(daily.total) + ' baris \u00b7 dokumen diterima ' + rata + '% dari aktivitas hari itu</div>';

      showHtmlTooltip(overlay, html, true);
      var chartBox = els.trend.getBoundingClientRect();
      var hostBox = els.trend.parentNode.getBoundingClientRect();
      var scale = chartBox.width / W;
      els.tooltip.style.left = (chartBox.left - hostBox.left + xPos * scale) + 'px';
      els.tooltip.style.top = (chartBox.top - hostBox.top + 8) + 'px';
      els.tooltip.style.transform = 'translate(-50%, 0)';
    });

    overlay.addEventListener('mouseleave', function () {
      hoverLine.style.display = 'none';
      hideTooltip();
    });
  }

  /* ------------------------------------------------ Tier 2: donut komposisi */

  function renderKomposisi(k) {
    if (!els.donut) { return; }
    var segs = k.segments || [];
    var total = Number(k.total || 0);

    if (total === 0) {
      els.donut.innerHTML = '<div class="mon-empty"><i class="fas fa-chart-pie mon-empty__icon" aria-hidden="true"></i><h3>Tidak ada data</h3><p>Tidak ada baris yang cocok dengan filter aktif.</p></div>';
      if (els.donutLegend) { els.donutLegend.innerHTML = ''; }
      return;
    }

    var R = 68, SW = 26, C = 100, CIRC = 2 * Math.PI * R;
    var acc = 0;
    var circles = segs.map(function (s) {
      var len = (Number(s.n) / total) * CIRC;
      var el = '<circle cx="' + C + '" cy="' + C + '" r="' + R + '" fill="none" stroke="' + esc(s.color) + '" stroke-width="' + SW + '"'
        + ' stroke-dasharray="' + len.toFixed(2) + ' ' + (CIRC - len).toFixed(2) + '"'
        + ' stroke-dashoffset="' + (-acc).toFixed(2) + '"'
        + ' transform="rotate(-90 ' + C + ' ' + C + ')"'
        + ' data-seg="' + esc(s.key) + '" tabindex="0" role="button"'
        + ' aria-label="' + esc(s.aria) + '" style="cursor:pointer"></circle>';
      acc += len;
      return el;
    }).join('');

    els.donut.innerHTML = '<svg viewBox="0 0 200 200" role="img" aria-label="Komposisi status untuk ' + fmtInt(total) + ' baris ruta">'
      + '<circle cx="' + C + '" cy="' + C + '" r="' + R + '" fill="none" stroke="#eef1f6" stroke-width="' + SW + '"></circle>'
      + circles
      + '<text x="' + C + '" y="' + (C - 2) + '" text-anchor="middle" style="font-size:26px;font-weight:800;fill:#2e3450">' + fmtInt(total) + '</text>'
      + '<text x="' + C + '" y="' + (C + 16) + '" text-anchor="middle" style="font-size:11px">baris ruta</text>'
      + '</svg>';

    if (els.donutLegend) {
      els.donutLegend.innerHTML = segs.map(function (s) {
        return '<button type="button" class="mon-legend-item" data-seg="' + esc(s.key) + '" aria-label="' + esc(s.aria) + '">'
          + '<span class="mon-legend-swatch mon-legend-swatch--dot" style="background:' + esc(s.color) + '"></span>'
          + '<i class="fas ' + esc(s.icon) + '" aria-hidden="true"></i>'
          + esc(s.label) + ' \u00b7 <b>' + esc(s.n_display) + '</b> <span class="mon-muted">(' + esc(s.pct_display) + ')</span>'
          + (Number(s.anomali) > 0 ? ' <span class="mon-muted">\u00b7 ' + fmtInt(s.anomali) + ' kendala</span>' : '')
          + '</button>';
      }).join('');
    }

    var byKey = {};
    segs.forEach(function (s) { byKey[s.key] = s; });

    $$('[data-seg]', els.donut.parentNode).forEach(function (el) {
      var seg = byKey[el.getAttribute('data-seg')];
      if (!seg) { return; }
      var activate = function () { crossFilter(seg.filter || {}); };
      el.addEventListener('click', activate);
      el.addEventListener('keydown', function (ev) {
        if (ev.key === 'Enter' || ev.key === ' ') { ev.preventDefault(); activate(); }
      });
      var show = function (ev) {
        var html = '<div class="mon-tooltip__title">' + esc(seg.label) + '</div>'
          + '<div class="mon-tooltip__row"><span>Jumlah</span><b>' + esc(seg.n_display) + ' baris</b></div>'
          + '<div class="mon-tooltip__row"><span>Porsi</span><b>' + esc(seg.pct_display) + '</b></div>'
          + '<div class="mon-tooltip__row"><span>Kendala</span><b>' + fmtInt(seg.anomali) + '</b></div>'
          + '<div class="mon-tooltip__note">Klik untuk memfilter seluruh dashboard ke segmen ini.</div>';
        showHtmlTooltip(el, html, !!(ev && ev.type === 'focus'));
      };
      el.addEventListener('mouseenter', show);
      el.addEventListener('focus', show);
      el.addEventListener('mouseleave', hideTooltip);
      el.addEventListener('blur', hideTooltip);
    });
  }

  /* -------------------------------------------- Tier 2: ranking horizontal */

  function renderRank(b) {
    if (!els.rank) { return; }
    var items = b.items || [];
    if (!items.length) {
      els.rank.innerHTML = '<div class="mon-empty"><i class="fas fa-user-slash mon-empty__icon" aria-hidden="true"></i><h3>Belum ada penugasan pengolah</h3><p>Tidak ada pengolah pada filter yang dipilih.</p></div>';
      if (els.rankMeta) { els.rankMeta.textContent = ''; }
      return;
    }

    els.rank.innerHTML = items.map(function (r) {
      var tone = toneClass(r.tone);
      var color = tone === 'ok' ? '#15803d' : tone === 'warn' ? '#d97706' : '#b91c1c';
      return '<button type="button" class="mon-rank__row" data-pengolah="' + esc(r.orang_id) + '" aria-pressed="false" aria-label="' + esc(r.aria) + '">'
        + '<span class="mon-rank__pos">' + r.peringkat + '</span>'
        + '<span class="mon-rank__name" title="' + esc(r.nama) + '">' + esc(r.nama) + '</span>'
        + '<span class="mon-rank__track"><span class="mon-rank__fill" style="width:' + Math.min(100, Number(r.share_pct || 0)).toFixed(2) + '%;background:' + color + '"></span></span>'
        + '<span class="mon-rank__val">' + esc(r.total_ruta_display) + ' <small>\u00b7 ' + esc(r.capaian_display) + '</small></span>'
        + '</button>';
    }).join('');

    if (els.rankMeta) {
      els.rankMeta.textContent = items.length + ' pengolah \u00b7 rata-rata ' + fmtInt(b.rata_rata) + ' baris/pengolah'
        + (Number(b.tanpa_pengolah) > 0 ? ' \u00b7 ' + fmtInt(b.tanpa_pengolah) + ' baris tanpa pengolah' : '');
    }

    $$('[data-pengolah]', els.rank).forEach(function (row) {
      var id = Number(row.getAttribute('data-pengolah'));
      var item = items.filter(function (x) { return Number(x.orang_id) === id; })[0] || {};

      row.addEventListener('click', function () {
        var isActive = row.getAttribute('aria-pressed') === 'true';
        $$('[data-pengolah]', els.rank).forEach(function (r2) { r2.setAttribute('aria-pressed', 'false'); });
        if (isActive) {
          crossFilter({ pengolah_id: '' });
        } else {
          row.setAttribute('aria-pressed', 'true');
          crossFilter({ pengolah_id: id });
        }
      });

      var show = function () {
        showHtmlTooltip(row, '<div class="mon-tooltip__title">' + esc(item.nama || '') + '</div>'
          + '<div class="mon-tooltip__row"><span>Beban ruta</span><b>' + fmtInt(item.total_ruta) + '</b></div>'
          + '<div class="mon-tooltip__row"><span>Jumlah SLS</span><b>' + fmtInt(item.total_sls) + '</b></div>'
          + '<div class="mon-tooltip__row"><span>Dokumen diterima</span><b>' + fmtInt(item.dok_ada) + '</b></div>'
          + '<div class="mon-tooltip__row"><span>Transfer K / KP</span><b>' + fmtInt(item.transfer_k) + ' / ' + fmtInt(item.transfer_kp) + '</b></div>'
          + '<div class="mon-tooltip__row"><span>Kendala</span><b>' + fmtInt(item.anomali) + '</b></div>'
          + '<div class="mon-tooltip__note">Porsi beban ' + esc(item.share_display || '') + ' \u00b7 capaian ' + esc(item.capaian_display || '') + ' \u00b7 ' + esc(item.tone_label || '') + '</div>', true);
      };
      row.addEventListener('mouseenter', show);
      row.addEventListener('focus', show);
      row.addEventListener('mouseleave', hideTooltip);
      row.addEventListener('blur', hideTooltip);
    });
  }

  /* -------------------------------------------------------- kendala detail */

  function renderKendala(k) {
    if (!els.kendala) { return; }
    var items = (k.items || []).filter(function (i) { return Number(i.n) > 0; });
    if (!items.length) {
      els.kendala.innerHTML = '<div class="mon-notice"><i class="fas fa-circle-check" aria-hidden="true" style="color:#15803d"></i> Tidak ada catatan kendala pada filter aktif.</div>';
      return;
    }
    els.kendala.innerHTML = items.map(function (i) {
      return '<button type="button" class="mon-kendala__row" data-kendala="' + esc(i.key) + '" aria-label="' + esc(i.aria) + '" style="background:none;border:0;padding:0;text-align:left;width:100%">'
        + '<span class="mon-kendala__label"><i class="fas ' + esc(i.icon) + '" style="color:' + esc(i.color) + '" aria-hidden="true"></i><span>' + esc(i.label) + '</span></span>'
        + '<span class="mon-kendala__val">' + esc(i.n_display) + '</span>'
        + '<span class="mon-kendala__track" style="grid-column:1/-1"><span class="mon-kendala__fill" style="width:' + Math.min(100, Number(i.rel || 0)).toFixed(2) + '%;background:' + esc(i.color) + '"></span></span>'
        + '</button>';
    }).join('') + '<div class="mon-notice" style="margin-top:.3rem">' + esc(k.note || '') + '</div>';

    $$('[data-kendala]', els.kendala).forEach(function (el) {
      el.addEventListener('click', function () { crossFilter({ has_error: '1' }); });
    });
  }

  /* ------------------------------------- konsistensi angka & metadata header */

  function renderConsistency(c) {
    if (!els.consistency) { return; }
    var checks = c.checks || [];
    var head = c.ok
      ? '<div class="mon-notice"><i class="fas fa-circle-check" style="color:#15803d" aria-hidden="true"></i> Semua ' + checks.length + ' pemeriksaan konsistensi LULUS \u2014 angka KPI, grafik, dan tabel sinkron.</div>'
      : '<div class="mon-notice"><i class="fas fa-triangle-exclamation" style="color:#b45309" aria-hidden="true"></i> ' + (Number(c.jumlah_periksa) - Number(c.jumlah_ok)) + ' dari ' + fmtInt(c.jumlah_periksa) + ' pemeriksaan perlu ditinjau.</div>';

    els.consistency.innerHTML = head + '<div class="mon-consistency">' + checks.map(function (r) {
      return '<div class="mon-consistency__row">'
        + '<i class="fas ' + esc(r.tone_icon) + ' mon-tone--' + toneClass(r.tone) + '" aria-hidden="true"></i>'
        + '<span>' + esc(r.label) + '</span>'
        + '<span>' + fmtInt(r.actual) + ' / ' + fmtInt(r.expected) + '</span>'
        + '</div>';
    }).join('') + '</div>';
  }

  function renderMeta(m) {
    if (els.stamp) {
      var t = m.terakhir_aktivitas || m.server_time || '';
      els.stamp.textContent = 'Diperbarui ' + fmtDate(t) + (m.server_time ? ' \u00b7 ' + String(m.server_time).slice(11) : '');
    }
    if (els.meta) {
      els.meta.innerHTML = '<span class="mon-filter-tag"><i class="fas fa-calendar-check mr-1" aria-hidden="true"></i>' + esc(m.periode_label || '\u2014') + ' (' + esc(m.periode_status || '\u2014') + ')</span>'
        + '<span class="mon-filter-tag"><i class="fas fa-gauge-high mr-1" aria-hidden="true"></i>Laju target ' + esc(m.target_pace_display || '\u2014') + '</span>'
        + '<span class="mon-filter-tag"><i class="fas fa-list-check mr-1" aria-hidden="true"></i>' + fmtInt(m.total_filtered) + ' baris ruta</span>'
        + '<span class="mon-filter-tag"><i class="fas fa-shield-halved mr-1" aria-hidden="true"></i>Audit 24 jam: ' + fmtInt(m.audit_24jam) + '</span>';
    }
  }

  /* ----------------------------------------------- Filter Summary / Badges */

  function renderFilterSummary(m) {
    if (!els.filterTags) { els.filterTags = $('#monFilterTags'); }
    if (!els.filterTags) { return; }

    var f = state.filters || {};
    var tags = [];

    var addTag = function (label, key, valDisplay) {
      tags.push('<span class="mon-filter-tag">'
        + esc(label) + ': <b>' + esc(valDisplay) + '</b> '
        + '<button type="button" class="mon-filter-tag__remove" data-remove-filter="' + esc(key) + '" aria-label="Hapus filter ' + esc(label) + '">&times;</button>'
        + '</span>');
    };

    if (f.range && f.range !== 'periode') {
      var rLabel = ({ today: 'Hari Ini', '7d': '7 Hari', '30d': '30 Hari', custom: 'Kustom' })[f.range] || f.range;
      addTag('Waktu', 'range', rLabel);
    }
    if (f.date_from || f.date_to) {
      addTag('Tanggal', 'date_range', (f.date_from || '—') + ' s/d ' + (f.date_to || '—'));
    }
    if (f.kec) {
      var kecOpt = $('#fKec option[value="' + f.kec + '"]');
      addTag('Kecamatan', 'kec', kecOpt ? kecOpt.textContent : f.kec);
    }
    if (f.desa_id && Number(f.desa_id) > 0) {
      var desaOpt = $('#fDesa option[value="' + f.desa_id + '"]');
      addTag('Desa', 'desa_id', desaOpt ? desaOpt.textContent : f.desa_id);
    }
    if (f.pengolah_id && Number(f.pengolah_id) > 0) {
      var pengOpt = $('#fPengolah option[value="' + f.pengolah_id + '"]');
      addTag('Pengolah', 'pengolah_id', pengOpt ? pengOpt.textContent : f.pengolah_id);
    }
    if (f.pcl_id && Number(f.pcl_id) > 0) {
      var pclOpt = $('#fPcl option[value="' + f.pcl_id + '"]');
      addTag('PCL', 'pcl_id', pclOpt ? pclOpt.textContent : f.pcl_id);
    }
    if (f.pml_id && Number(f.pml_id) > 0) {
      var pmlOpt = $('#fPml option[value="' + f.pml_id + '"]');
      addTag('PML', 'pml_id', pmlOpt ? pmlOpt.textContent : f.pml_id);
    }
    if (f.status_dokumen) {
      addTag('Dokumen', 'status_dokumen', f.status_dokumen === 'ADA' ? 'Ada' : 'Belum');
    }
    if (f.transfer_stage) {
      var stageMap = { BELUM_K: 'Dokumen saja', K: 'Entri K', KP: 'Valid KP', SERUTI: 'Seruti' };
      addTag('Tahap', 'transfer_stage', stageMap[f.transfer_stage] || f.transfer_stage);
    }
    if (f.has_error !== '' && f.has_error !== undefined) {
      addTag('Verifikasi', 'has_error', f.has_error === '1' ? 'Ada kendala' : 'Bersih');
    }
    if (f.q) {
      addTag('Pencarian', 'q', f.q);
    }

    if (els.filterSummary) {
      els.filterSummary.textContent = tags.length ? '(' + tags.length + ' aktif)' : '';
    }

    els.filterTags.innerHTML = tags.join('');

    $$('[data-remove-filter]', els.filterTags).forEach(function (btn) {
      btn.addEventListener('click', function () {
        var key = btn.getAttribute('data-remove-filter');
        if (key === 'range') {
          state.filters.range = 'periode';
          state.filters.date_from = '';
          state.filters.date_to = '';
        } else if (key === 'date_range') {
          state.filters.date_from = '';
          state.filters.date_to = '';
          state.filters.range = 'periode';
        } else {
          state.filters[key] = '';
        }
        state.filters.page = 1;
        applyFiltersToForm(state.filters);
        syncUrl(false);
        load({ skeleton: true });
      });
    });
  }

  /* ---------------------------------------------------- Interaktif Filter */

  function crossFilter(diff) {
    Object.keys(diff || {}).forEach(function (k) {
      state.filters[k] = diff[k];
    });
    state.filters.page = 1;
    applyFiltersToForm(state.filters);
    syncUrl(false);
    load({ skeleton: true });
  }

  /* ------------------------------------------------ Tier 3: Data Grid & Pager */

  function renderGrid(g) {
    if (!els.gridBody) { return; }
    var rows = g.rows || [];
    var total = Number(g.total || 0);

    if (els.gridInfo) {
      els.gridInfo.textContent = fmtInt(total) + ' baris ruta';
    }

    if (rows.length === 0) {
      var emptyHtml = '<div class="mon-empty"><i class="fas fa-table-list mon-empty__icon" aria-hidden="true"></i><h3>Tidak ada data ruta</h3><p>Tidak ada sampel ruta yang cocok dengan filter aktif.</p><button type="button" class="btn btn-sm btn-outline-secondary" id="monGridReset">Bersihkan Filter</button></div>';
      els.gridBody.innerHTML = '<tr><td colspan="10">' + emptyHtml + '</td></tr>';
      if (els.cards) { els.cards.innerHTML = emptyHtml; }
      if (els.pager) { els.pager.innerHTML = ''; }
      var resetBtn = $('#monGridReset', els.gridBody);
      if (resetBtn) {
        resetBtn.addEventListener('click', function () {
          resetFilters();
        });
      }
      return;
    }

    /* Render Table Rows */
    els.gridBody.innerHTML = rows.map(function (r) {
      var isChecked = state.selection.indexOf(r.id) >= 0;
      var stageTone = toneClass(r.stage.tone);
      var stageIcon = esc(r.stage.icon || 'fa-circle-info');
      var dokCls = r.status_dokumen === 'ADA' ? 'mon-badge-inline--ya' : 'mon-badge-inline--tidak';
      var kCls = r.transfer_k ? 'mon-badge-inline--ya' : 'mon-badge-inline--tidak';
      var kpCls = r.transfer_kp ? 'mon-badge-inline--ya' : 'mon-badge-inline--tidak';
      var serutiCls = r.transfer_seruti ? 'mon-badge-inline--ya' : 'mon-badge-inline--tidak';
      var kendalaCls = r.anomali ? 'mon-badge-tone--danger' : 'mon-badge-tone--ok';
      var kendalaIcon = r.anomali ? 'fa-triangle-exclamation' : 'fa-circle-check';

      return '<tr data-id="' + r.id + '" aria-selected="' + (isChecked ? 'true' : 'false') + '">'
        + '<td><input type="checkbox" class="mon-row-check" data-id="' + r.id + '" aria-label="Pilih ' + esc(r.nks) + ' ' + esc(r.ruta_label) + '"' + (isChecked ? ' checked' : '') + '></td>'
        + '<td class="mon-sticky"><div class="mon-ident"><b>' + esc(r.nks) + '</b><small>' + esc(r.ruta_label) + '</small></div></td>'
        + '<td><div class="mon-ident"><span>' + esc(r.desa) + '</span><small class="mon-muted">' + esc(r.kecamatan) + '</small></div></td>'
        + '<td><div class="mon-ident"><span>PCL: ' + esc(r.pcl) + '</span><small class="mon-muted">PML: ' + esc(r.pml) + ' | Olah: ' + esc(r.pengolah) + '</small></div></td>'
        + '<td><span class="mon-badge-tone mon-badge-tone--' + stageTone + '"><i class="fas ' + stageIcon + '" aria-hidden="true"></i>' + esc(r.stage.label) + '</span></td>'
        + '<td><span class="mon-badge-inline ' + dokCls + '">' + esc(r.status_dokumen_label) + '</span></td>'
        + '<td><span class="mon-badge-inline ' + kCls + '">' + (r.transfer_k ? 'Ya' : 'Tdk') + '</span></td>'
        + '<td><span class="mon-badge-inline ' + kpCls + '">' + (r.transfer_kp ? 'Ya' : 'Tdk') + '</span></td>'
        + '<td><span class="mon-badge-inline ' + serutiCls + '">' + (r.transfer_seruti ? 'Ya' : 'Tdk') + '</span></td>'
        + '<td><span class="mon-badge-tone ' + kendalaCls + '"><i class="fas ' + kendalaIcon + '" aria-hidden="true"></i>' + esc(r.anomali_label) + '</span></td>'
        + '<td><div class="mon-actions">'
        + '<button type="button" class="mon-icon-btn" data-detail-id="' + r.id + '" title="Quick View & Riwayat Audit"><i class="fas fa-eye" aria-hidden="true"></i></button>'
        + '<a href="' + esc(r.detail_url) + '" class="mon-icon-btn" target="_blank" title="Buka di lembar kerja pengolahan"><i class="fas fa-pen-to-square" aria-hidden="true"></i></a>'
        + '</div></td>'
        + '</tr>';
    }).join('');

    /* Render Mobile Cards */
    if (els.cards) {
      els.cards.innerHTML = rows.map(function (r) {
        var stageTone = toneClass(r.stage.tone);
        return '<div class="mon-data-card" data-id="' + r.id + '">'
          + '<div class="mon-data-card__head">'
          + '<div class="mon-ident"><b>' + esc(r.nks) + ' \u00b7 ' + esc(r.ruta_label) + '</b><small>' + esc(r.wilayah) + '</small></div>'
          + '<span class="mon-badge-tone mon-badge-tone--' + stageTone + '">' + esc(r.stage.label) + '</span>'
          + '</div>'
          + '<div class="mon-data-card__body">'
          + '<div class="mon-data-card__row"><span>Tim Lapangan</span><span>' + esc(r.pcl) + ' / ' + esc(r.pml) + '</span></div>'
          + '<div class="mon-data-card__row"><span>Pengolah</span><span>' + esc(r.pengolah) + '</span></div>'
          + '<div class="mon-data-card__row"><span>Dok / K / KP / Seruti</span><span>' + esc(r.status_dokumen_label) + ' / ' + (r.transfer_k ? 'Ya' : 'Tdk') + ' / ' + (r.transfer_kp ? 'Ya' : 'Tdk') + ' / ' + (r.transfer_seruti ? 'Ya' : 'Tdk') + '</span></div>'
          + '<div class="mon-data-card__row"><span>Kendala</span><span>' + esc(r.anomali_label) + '</span></div>'
          + '<div class="mon-actions mt-2">'
          + '<button type="button" class="btn btn-xs btn-outline-primary" data-detail-id="' + r.id + '"><i class="fas fa-eye mr-1" aria-hidden="true"></i>Detail & Audit</button>'
          + '<a href="' + esc(r.detail_url) + '" class="btn btn-xs btn-outline-secondary" target="_blank"><i class="fas fa-pen mr-1" aria-hidden="true"></i>Lembar Kerja</a>'
          + '</div>'
          + '</div></div>';
      }).join('');
    }

    renderPager(g);
    bindGridEvents();
    updateSelectionUi();
  }

  function renderPager(g) {
    if (!els.pager) { return; }
    var page = Number(g.page || 1);
    var pages = Number(g.pages || 1);
    var total = Number(g.total || 0);
    var dari = Number(g.dari || 0);
    var sampai = Number(g.sampai || 0);

    if (total === 0 || pages <= 1) {
      els.pager.innerHTML = '<span class="mon-muted">Menampilkan ' + fmtInt(total) + ' baris</span>';
      return;
    }

    var html = '<span class="mon-muted">Menampilkan <b>' + fmtInt(dari) + '</b>\u2013<b>' + fmtInt(sampai) + '</b> dari <b>' + fmtInt(total) + '</b> baris</span>';
    html += '<div class="mon-pager__nav" role="navigation" aria-label="Halaman tabel">';

    html += '<button type="button" data-page="1"' + (page <= 1 ? ' disabled' : '') + ' aria-label="Halaman pertama"><i class="fas fa-angles-left" aria-hidden="true"></i></button>';
    html += '<button type="button" data-page="' + (page - 1) + '"' + (page <= 1 ? ' disabled' : '') + ' aria-label="Halaman sebelumnya"><i class="fas fa-angle-left" aria-hidden="true"></i></button>';

    var startP = Math.max(1, page - 2);
    var endP = Math.min(pages, page + 2);
    if (startP > 1) {
      html += '<button type="button" data-page="1">1</button>';
      if (startP > 2) { html += '<span class="mon-muted px-1">&hellip;</span>'; }
    }
    for (var p = startP; p <= endP; p++) {
      html += '<button type="button" data-page="' + p + '"' + (p === page ? ' aria-current="page"' : '') + '>' + p + '</button>';
    }
    if (endP < pages) {
      if (endP < pages - 1) { html += '<span class="mon-muted px-1">&hellip;</span>'; }
      html += '<button type="button" data-page="' + pages + '">' + pages + '</button>';
    }

    html += '<button type="button" data-page="' + (page + 1) + '"' + (page >= pages ? ' disabled' : '') + ' aria-label="Halaman berikutnya"><i class="fas fa-angle-right" aria-hidden="true"></i></button>';
    html += '<button type="button" data-page="' + pages + '"' + (page >= pages ? ' disabled' : '') + ' aria-label="Halaman terakhir"><i class="fas fa-angles-right" aria-hidden="true"></i></button>';
    html += '</div>';

    els.pager.innerHTML = html;

    $$('button[data-page]', els.pager).forEach(function (btn) {
      btn.addEventListener('click', function () {
        var targetPage = Number(btn.getAttribute('data-page'));
        if (!targetPage || targetPage === page || btn.disabled) { return; }
        state.filters.page = targetPage;
        syncUrl(false);
        load({ skeleton: true });
      });
    });
  }

  function bindGridEvents() {
    /* Click detail drawer */
    $$('[data-detail-id]', els.gridBody).concat($$('[data-detail-id]', els.cards)).forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.stopPropagation();
        var id = Number(btn.getAttribute('data-detail-id'));
        if (id > 0) { openDrawer(id); }
      });
    });

    /* Row checkboxes */
    $$('.mon-row-check[data-id]', els.gridBody).forEach(function (chk) {
      chk.addEventListener('change', function () {
        var id = Number(chk.getAttribute('data-id'));
        var idx = state.selection.indexOf(id);
        if (chk.checked && idx < 0) {
          state.selection.push(id);
        } else if (!chk.checked && idx >= 0) {
          state.selection.splice(idx, 1);
        }
        var tr = chk.closest('tr');
        if (tr) { tr.setAttribute('aria-selected', chk.checked ? 'true' : 'false'); }
        updateSelectionUi();
      });
    });
  }

  function updateSelectionUi() {
    var count = state.selection.length;
    if (els.selection) {
      els.selection.textContent = count + ' dipilih';
    }
    var checkAll = $('#monCheckAll');
    if (checkAll && els.gridBody) {
      var rowChecks = $$('.mon-row-check[data-id]', els.gridBody);
      var allChecked = rowChecks.length > 0 && rowChecks.every(function (c) { return c.checked; });
      checkAll.checked = allChecked;
    }
  }

  /* ---------------------------------------------------- Drawer Quick View */

  function openDrawer(id) {
    if (!els.drawer || !els.drawerBackdrop) { return; }
    state.openDrawerId = id;
    els.drawerBackdrop.setAttribute('data-open', '1');
    els.drawer.setAttribute('data-open', '1');
    if (els.drawerTitle) { els.drawerTitle.textContent = 'Memuat rincian\u2026'; }
    if (els.drawerSub) { els.drawerSub.textContent = ''; }
    if (els.drawerBody) {
      els.drawerBody.innerHTML = '<div class="mon-skeleton p-3"><div class="mon-skel-line mon-skel-line--md"></div><div class="mon-skel-block"></div></div>';
    }
    if (els.drawerFoot) { els.drawerFoot.innerHTML = ''; }

    var url = CFG.detailUrl + '/' + id;
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
      .then(function (r) {
        if (!r.ok) { throw new Error('HTTP ' + r.status); }
        return r.json();
      })
      .then(function (json) {
        if (!json || !json.ok || !json.detail) { throw new Error((json && json.message) || 'Gagal memuat detail'); }
        renderDrawer(json.detail);
      })
      .catch(function (err) {
        if (els.drawerBody) {
          els.drawerBody.innerHTML = '<div class="mon-notice"><i class="fas fa-circle-exclamation" style="color:#b91c1c" aria-hidden="true"></i> Gagal memuat data: ' + esc(err.message) + '</div>';
        }
      });
  }

  function closeDrawer() {
    state.openDrawerId = null;
    if (els.drawer) { els.drawer.setAttribute('data-open', '0'); }
    if (els.drawerBackdrop) { els.drawerBackdrop.setAttribute('data-open', '0'); }
  }

  function renderDrawer(detail) {
    var r = detail.row || {};
    var riwayat = detail.riwayat || [];
    var aksi = detail.aksi || {};
    var canQuick = !!aksi.can_quick_verify;

    if (els.drawerTitle) {
      els.drawerTitle.textContent = 'NKS ' + (r.nks || '—') + ' \u2014 ' + (r.ruta_label || '—');
    }
    if (els.drawerSub) {
      els.drawerSub.textContent = (r.desa || '') + ', Kec. ' + (r.kecamatan || '') + ' \u00b7 SLS: ' + (r.nama_sls || '') + ' (' + (r.kode_full || '') + ')';
    }

    var html = '';

    /* Rincian Identitas */
    html += '<h4 class="mon-subhead">Identitas Sampel & Wilayah</h4>';
    html += '<dl class="mon-kv">';
    html += '<dt>Klasifikasi</dt><dd>' + esc(r.klasifikasi || '—') + '</dd>';
    html += '<dt>Dusun / RW / RT</dt><dd>' + esc(r.dusun || '—') + ' / RW ' + esc(r.rw || '—') + ' / RT ' + esc(r.rt || '—') + '</dd>';
    html += '<dt>Jumlah KK SLS</dt><dd>' + fmtInt(r.jml_kk) + ' KK</dd>';
    html += '<dt>PCL Lapangan</dt><dd>' + esc(r.pcl || '—') + '</dd>';
    html += '<dt>PML Pengawas</dt><dd>' + esc(r.pml || '—') + (r.hp_pml ? ' (' + esc(r.hp_pml) + ')' : '') + '</dd>';
    html += '<dt>Petugas Pengolah</dt><dd>' + esc(r.pengolah || '—') + '</dd>';
    html += '</dl>';

    /* Status Pengolahan & Aksi Cepat */
    html += '<h4 class="mon-subhead">Status Pengolahan Data</h4>';
    if (canQuick) {
      html += '<form id="monQuickForm">';
      html += '<input type="hidden" name="ruta_id" value="' + esc(r.id) + '">';
      html += '<div class="mon-card p-3 mb-3">';
      html += '<div class="form-group mb-2"><label class="small font-weight-bold" for="qDok">Status Dokumen Fisik</label>'
        + '<select id="qDok" name="status_dokumen" class="form-control form-control-sm">'
        + '<option value="ADA"' + (r.status_dokumen === 'ADA' ? ' selected' : '') + '>ADA (Fisik Diterima)</option>'
        + '<option value="BELUM"' + (r.status_dokumen === 'BELUM' ? ' selected' : '') + '>BELUM (Belum Diterima)</option>'
        + '</select></div>';
      html += '<div class="mon-switch"><input type="checkbox" id="qK" name="status_transfer_k" value="1"' + (r.transfer_k ? ' checked' : '') + '><label for="qK" class="mb-0">Transfer K (Entri Kor Selesai)</label></div>';
      html += '<div class="mon-switch"><input type="checkbox" id="qKp" name="status_transfer_kp" value="1"' + (r.transfer_kp ? ' checked' : '') + '><label for="qKp" class="mb-0">Transfer KP (Validasi Konsistensi Selesai)</label></div>';
      html += '<div class="mon-switch"><input type="checkbox" id="qSeruti" name="status_transfer_seruti" value="1"' + (r.transfer_seruti ? ' checked' : '') + '><label for="qSeruti" class="mb-0">Transfer Seruti (Integrasi Seruti Selesai)</label></div>';
      html += '<button type="submit" class="btn btn-sm btn-primary mt-2" id="monQuickSubmit"><i class="fas fa-floppy-disk mr-1" aria-hidden="true"></i>Simpan Cepat Status</button>';
      html += '</div>';
      html += '</form>';
    } else {
      html += '<dl class="mon-kv">';
      html += '<dt>Dokumen Fisik</dt><dd>' + (r.status_dokumen === 'ADA' ? '<span class="badge badge-success">ADA</span>' : '<span class="badge badge-secondary">BELUM</span>') + '</dd>';
      html += '<dt>Transfer K</dt><dd>' + (r.transfer_k ? '<span class="badge badge-success">Sudah</span>' : '<span class="badge badge-secondary">Belum</span>') + '</dd>';
      html += '<dt>Transfer KP</dt><dd>' + (r.transfer_kp ? '<span class="badge badge-success">Sudah</span>' : '<span class="badge badge-secondary">Belum</span>') + '</dd>';
      html += '<dt>Transfer Seruti</dt><dd>' + (r.transfer_seruti ? '<span class="badge badge-success">Sudah</span>' : '<span class="badge badge-secondary">Belum</span>') + '</dd>';
      html += '</dl>';
      if (aksi.alasan && aksi.alasan.length) {
        html += '<div class="mon-notice mt-2"><i class="fas fa-lock" aria-hidden="true"></i> ' + esc(aksi.alasan.join(' \u00b7 ')) + '</div>';
      }
    }

    /* Catatan & Temuan */
    if (r.ket_kp_pengolah || r.ket_m_pengolah || r.uji_petik_pengawas) {
      html += '<h4 class="mon-subhead">Catatan Kendala & Temuan</h4>';
      html += '<div class="mon-card p-3 mb-3 bg-light">';
      if (r.ket_kp_pengolah) { html += '<p class="mb-1"><b>Catatan KP:</b> ' + esc(r.ket_kp_pengolah) + '</p>'; }
      if (r.ket_m_pengolah) { html += '<p class="mb-1"><b>Catatan Modul:</b> ' + esc(r.ket_m_pengolah) + '</p>'; }
      if (r.uji_petik_pengawas) { html += '<p class="mb-0"><b>Uji Petik Pengawas:</b> ' + esc(r.uji_petik_pengawas) + '</p>'; }
      html += '</div>';
    }

    /* Jejak Audit */
    html += '<h4 class="mon-subhead">Riwayat Perubahan (Audit Trail)</h4>';
    if (!riwayat.length) {
      html += '<div class="mon-notice"><i class="fas fa-clock-rotate-left" aria-hidden="true"></i> Belum ada rekaman jejak audit pada baris ini.</div>';
    } else {
      html += '<ul class="mon-timeline">';
      html += riwayat.map(function (item) {
        var diffHtml = (item.perubahan || []).map(function (d) {
          return '<code>' + esc(d.field) + ': ' + esc(d.before) + ' &rarr; ' + esc(d.after) + '</code>';
        }).join(' ');
        return '<li>'
          + '<div class="font-weight-bold">' + esc(item.aksi) + ' \u2014 ' + esc(item.user) + '</div>'
          + '<div class="mon-timeline__meta">' + fmtDate(item.waktu) + ' \u00b7 ' + String(item.waktu).slice(11) + ' \u00b7 IP: ' + esc(item.ip) + '</div>'
          + (diffHtml ? '<div class="mon-diff">' + diffHtml + '</div>' : '')
          + '</li>';
      }).join('');
      html += '</ul>';
    }

    if (els.drawerBody) { els.drawerBody.innerHTML = html; }

    if (els.drawerFoot) {
      els.drawerFoot.innerHTML = '<a href="' + esc(aksi.detail_url || '#') + '" class="btn btn-sm btn-outline-primary" target="_blank"><i class="fas fa-arrow-up-right-from-square mr-1" aria-hidden="true"></i>Lembar Kerja Penuh</a>'
        + '<button type="button" class="btn btn-sm btn-secondary ml-auto" data-close-drawer>Tutup</button>';
      $$('[data-close-drawer]', els.drawerFoot).forEach(function (btn) {
        btn.addEventListener('click', closeDrawer);
      });
    }

    /* Attach submit listener to Quick Verify form */
    var form = $('#monQuickForm', els.drawerBody);
    if (form) {
      form.addEventListener('submit', function (ev) {
        ev.preventDefault();
        var submitBtn = $('#monQuickSubmit', form);
        if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Menyimpan\u2026'; }

        var formData = new FormData(form);
        formData.append('_csrf', CFG.csrfToken || '');

        fetch(CFG.quickUrl, {
          method: 'POST',
          body: formData,
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          credentials: 'same-origin'
        })
          .then(function (r) {
            if (!r.ok) { throw new Error('HTTP ' + r.status); }
            return r.json();
          })
          .then(function (json) {
            if (!json || !json.ok) { throw new Error((json && json.message) || 'Gagal menyimpan'); }
            toast('Status berhasil diperbarui & tercatat pada audit trail.');
            if (json.detail) { renderDrawer(json.detail); }
            load();
          })
          .catch(function (err) {
            toast('Aksi cepat gagal: ' + err.message, true);
            if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Simpan Cepat Status'; }
          });
      });
    }
  }

  /* ---------------------------------------------------- Bulk Actions */

  function bindBulkActions() {
    $$('[data-bulk]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        if (!state.selection.length) {
          toast('Pilih minimal 1 baris ruta di tabel terlebih dahulu.', true);
          return;
        }

        var field = btn.getAttribute('data-bulk');
        var val = btn.getAttribute('data-value') === '1';
        var label = (field === 'status_transfer_k' ? 'Transfer K' : 'Transfer KP') + ' = ' + (val ? 'Ya' : 'Tidak');

        var conf = confirm('Terapkan ' + label + ' untuk ' + state.selection.length + ' baris terpilih? Aksi akan dicatat pada audit log.');
        if (!conf) { return; }

        var body = new URLSearchParams();
        body.append('field', field);
        body.append('value', val ? '1' : '0');
        body.append('periode_id', state.filters.periode_id || '0');
        body.append('_csrf', CFG.csrfToken || '');
        state.selection.forEach(function (id) { body.append('ruta_ids[]', id); });

        setLive('syncing', 'Menyimpan massal\u2026');
        fetch(CFG.bulkUrl, {
          method: 'POST',
          body: body,
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          credentials: 'same-origin'
        })
          .then(function (r) {
            if (!r.ok) { throw new Error('HTTP ' + r.status); }
            return r.json();
          })
          .then(function (json) {
            if (!json || !json.ok) { throw new Error((json && json.message) || 'Gagal aksi massal'); }
            toast(json.message || 'Aksi massal berhasil.');
            state.selection = [];
            load();
          })
          .catch(function (err) {
            setLive('live', 'Live');
            toast('Gagal aksi massal: ' + err.message, true);
          });
      });
    });
  }

  /* ---------------------------------------------------- Filter Bindings */

  function bindFilters() {
    /* Select dropdowns */
    $$('select[data-filter]', els.filters).forEach(function (sel) {
      sel.addEventListener('change', function () {
        var key = sel.getAttribute('data-filter');
        state.filters[key] = sel.value;
        state.filters.page = 1;

        if (key === 'periode_id') {
          handlePeriodeChange(sel.value);
          return;
        }
        if (key === 'kec') {
          handleKecamatanChange(sel.value);
        }

        syncUrl(false);
        load({ skeleton: true });
      });
    });

    /* Date inputs */
    $$('input[type="date"][data-filter]', els.filters).forEach(function (inp) {
      inp.addEventListener('change', function () {
        var key = inp.getAttribute('data-filter');
        state.filters[key] = inp.value;
        state.filters.range = 'custom';
        state.filters.page = 1;
        applyFiltersToForm(state.filters);
        syncUrl(false);
        load({ skeleton: true });
      });
    });

    /* Search input global (debounced) */
    var searchInp = $('#fQ');
    if (searchInp) {
      searchInp.addEventListener('input', debounce(function () {
        state.filters.q = searchInp.value.trim();
        state.filters.page = 1;
        syncUrl(false);
        load({ skeleton: false });
      }, DEBOUNCE_MS));
    }

    /* Range preset chips */
    $$('.mon-chip[data-range]').forEach(function (chip) {
      chip.addEventListener('click', function () {
        var r = chip.getAttribute('data-range');
        state.filters.range = r;
        state.filters.page = 1;
        if (r === 'today') {
          var td = new Date().toISOString().slice(0, 10);
          state.filters.date_from = td;
          state.filters.date_to = td;
        } else if (r === 'periode') {
          state.filters.date_from = '';
          state.filters.date_to = '';
        }
        applyFiltersToForm(state.filters);
        syncUrl(false);
        load({ skeleton: true });
      });
    });

    /* Per-page selector */
    var perPageSel = $('#monPerPage');
    if (perPageSel) {
      perPageSel.addEventListener('change', function () {
        state.filters.per_page = Number(perPageSel.value) || 25;
        state.filters.page = 1;
        syncUrl(false);
        load({ skeleton: true });
      });
    }

    /* Sorting headers */
    $$('th button[data-sort]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var key = btn.getAttribute('data-sort');
        if (state.filters.sort === key) {
          state.filters.dir = state.filters.dir === 'DESC' ? 'ASC' : 'DESC';
        } else {
          state.filters.sort = key;
          state.filters.dir = 'ASC';
        }
        state.filters.page = 1;
        syncUrl(false);
        load({ skeleton: true });
      });
    });

    /* Select all checkbox */
    var checkAll = $('#monCheckAll');
    if (checkAll) {
      checkAll.addEventListener('change', function () {
        var rowChecks = $$('.mon-row-check[data-id]', els.gridBody);
        rowChecks.forEach(function (chk) {
          chk.checked = checkAll.checked;
          var id = Number(chk.getAttribute('data-id'));
          var idx = state.selection.indexOf(id);
          if (checkAll.checked && idx < 0) {
            state.selection.push(id);
          } else if (!checkAll.checked && idx >= 0) {
            state.selection.splice(idx, 1);
          }
          var tr = chk.closest('tr');
          if (tr) { tr.setAttribute('aria-selected', checkAll.checked ? 'true' : 'false'); }
        });
        updateSelectionUi();
      });
    }

    /* Reset button */
    var resetBtn = $('#monReset');
    if (resetBtn) {
      resetBtn.addEventListener('click', function () {
        resetFilters();
      });
    }

    /* Refresh button */
    var refreshBtn = $('#monRefresh');
    if (refreshBtn) {
      refreshBtn.addEventListener('click', function () {
        load({ toast: 'Data berhasil dimuat ulang' });
      });
    }

    /* Export PDF button */
    var pdfBtn = $('#monExportPdf');
    if (pdfBtn) {
      pdfBtn.addEventListener('click', function () {
        window.print();
      });
    }

    /* Mobile filter toggle */
    if (els.filterToggle && els.filters) {
      els.filterToggle.addEventListener('click', function () {
        var isOpen = els.filters.getAttribute('data-open') === '1';
        els.filters.setAttribute('data-open', isOpen ? '0' : '1');
        els.filterToggle.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
      });
    }

    /* Drawer backdrop & close buttons */
    $$('[data-close-drawer]').forEach(function (el) {
      el.addEventListener('click', closeDrawer);
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && state.openDrawerId) {
        closeDrawer();
      }
    });
  }

  function initDesaMaster() {
    var desaSel = $('#fDesa');
    if (!desaSel) { return; }
    state.desaMaster = Array.prototype.slice.call(desaSel.options).map(function (opt) {
      return {
        value: opt.value,
        text: opt.textContent,
        kec: opt.getAttribute('data-kec') || ''
      };
    });
  }

  function handleKecamatanChange(kecKode) {
    var desaSel = $('#fDesa');
    if (!desaSel) { return; }
    if (!state.desaMaster || !state.desaMaster.length) {
      initDesaMaster();
    }
    var currentVal = String(state.filters.desa_id || '');
    if (currentVal === '0') { currentVal = ''; }
    var matchedCurrent = false;

    var filtered = (state.desaMaster || []).filter(function (d) {
      if (!d.value) { return true; }
      return !kecKode || d.kec === kecKode;
    });

    desaSel.innerHTML = filtered.map(function (d) {
      var isSel = (d.value && d.value === currentVal);
      if (isSel) { matchedCurrent = true; }
      return '<option value="' + esc(d.value) + '"'
        + (d.kec ? ' data-kec="' + esc(d.kec) + '"' : '')
        + (isSel ? ' selected' : '') + '>'
        + esc(d.text) + '</option>';
    }).join('');

    if (!matchedCurrent && currentVal !== '') {
      state.filters.desa_id = '';
      desaSel.value = '';
    } else if (matchedCurrent) {
      desaSel.value = currentVal;
    } else {
      desaSel.value = '';
    }
  }

  function handlePeriodeChange(periodeId) {
    var url = CFG.optionsUrl + '?periode_id=' + encodeURIComponent(periodeId);
    setLive('syncing', 'Memperbarui opsi\u2026');
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (json) {
        if (json && json.ok && json.options) {
          updateDropdownOptions(json.options);
        }
      })
      .finally(function () {
        syncUrl(false);
        load({ skeleton: true });
      });
  }

  function updateDropdownOptions(opts) {
    var kecSel = $('#fKec');
    if (kecSel && opts.kecamatan) {
      var kHtml = '<option value="">Semua kecamatan</option>';
      opts.kecamatan.forEach(function (k) {
        kHtml += '<option value="' + esc(k.kode) + '">' + esc(k.nama || k.kode) + '</option>';
      });
      kecSel.innerHTML = kHtml;
    }
    var desaSel = $('#fDesa');
    if (desaSel && opts.desa) {
      var dHtml = '<option value="">Semua desa</option>';
      opts.desa.forEach(function (d) {
        dHtml += '<option value="' + d.id + '" data-kec="' + esc(d.kecamatan_kode || '') + '">' + esc(d.nama) + '</option>';
      });
      desaSel.innerHTML = dHtml;
      initDesaMaster();
    }
    var pengSel = $('#fPengolah');
    var pclSel = $('#fPcl');
    var pmlSel = $('#fPml');
    if (opts.petugas) {
      if (pengSel) {
        var pengHtml = '<option value="">Semua pengolah</option>';
        opts.petugas.filter(function (x) { return x.peran === 'PENGOLAH'; }).forEach(function (p) {
          pengHtml += '<option value="' + p.id + '">' + esc(p.nama) + ' (' + p.jml + ')</option>';
        });
        pengSel.innerHTML = pengHtml;
      }
      if (pclSel) {
        var pclHtml = '<option value="">Semua PCL</option>';
        opts.petugas.filter(function (x) { return x.peran === 'PCL'; }).forEach(function (p) {
          pclHtml += '<option value="' + p.id + '">' + esc(p.nama) + ' (' + p.jml + ')</option>';
        });
        pclSel.innerHTML = pclHtml;
      }
      if (pmlSel) {
        var pmlHtml = '<option value="">Semua PML</option>';
        opts.petugas.filter(function (x) { return x.peran === 'PML'; }).forEach(function (p) {
          pmlHtml += '<option value="' + p.id + '">' + esc(p.nama) + ' (' + p.jml + ')</option>';
        });
        pmlSel.innerHTML = pmlHtml;
      }
    }
  }

  function resetFilters() {
    var pId = state.filters.periode_id;
    state.filters = {
      periode_id: pId || 0,
      range: 'periode',
      date_from: '',
      date_to: '',
      kec: '',
      desa_id: '',
      pengolah_id: '',
      pcl_id: '',
      pml_id: '',
      status_dokumen: '',
      transfer_stage: '',
      has_error: '',
      q: '',
      sort: 'nks',
      dir: 'ASC',
      page: 1,
      per_page: 25
    };
    applyFiltersToForm(state.filters);
    handleKecamatanChange('');
    syncUrl(false);
    load({ skeleton: true, toast: 'Filter telah direset ke default.' });
  }

  /* ---------------------------------------------------- Background Polling */

  function startPolling() {
    if (state.pollTimer) { clearInterval(state.pollTimer); }
    state.pollTimer = setInterval(function () {
      if (document.hidden) { return; }
      load();
    }, POLL_INTERVAL);

    document.addEventListener('visibilitychange', function () {
      if (!document.hidden && Date.now() - state.lastSuccess > POLL_INTERVAL) {
        load();
      }
    });
  }

  /* ---------------------------------------------------- Init */

  function init() {
    cacheEls();
    initDesaMaster();
    state.filters = CFG.initialFilters || readFiltersFromForm();
    applyFiltersToForm(state.filters);
    bindFilters();
    bindBulkActions();

    if (CFG.initialPayload && CFG.initialGrid) {
      state.lastSuccess = Date.now();
      renderAll({ ok: true, payload: CFG.initialPayload, grid: CFG.initialGrid, filters: state.filters });
    } else {
      load({ skeleton: true });
    }

    startPolling();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();