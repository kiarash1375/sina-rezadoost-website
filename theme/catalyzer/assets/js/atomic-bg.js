/* ============================================================
   کاتالیزور — پس‌زمینه‌ی پیوندهای اتمی
   شبکه‌ای همیشگی از اتم‌ها که با حرکت نشانگر در آن ناحیه
   متراکم‌تر می‌شود و به‌دنبال نشانگر کشیده می‌شود.
   ============================================================ */
(function () {
  "use strict";
  var canvas = document.getElementById("bgNet");
  if (!canvas || !canvas.getContext) return;
  var ctx = canvas.getContext("2d");

  var rmq = window.matchMedia("(prefers-reduced-motion: reduce)");
  var W = 0, H = 0, DPR = 1, raf = 0, running = false;
  var atoms = [], prox = [];
  var pointer = { x: -9999, y: -9999, s: 0, ts: 0 };
  var rgb = [240, 130, 30];

  var LINK_BASE = 132;   // bond reach for the always-on network
  var LINK_NEAR = 212;   // bond reach right under the pointer
  var INFL      = 232;   // pointer influence radius

  function readColor() {
    var v = getComputedStyle(document.documentElement).getPropertyValue("--accent").trim();
    var hex = v.match(/^#?([0-9a-fA-F]{6})$/);
    if (hex) {
      var n = parseInt(hex[1], 16);
      rgb = [(n >> 16) & 255, (n >> 8) & 255, n & 255];
      return;
    }
    var fn = v.match(/rgba?\(([^)]+)\)/);
    if (fn) {
      var p = fn[1].split(",").map(parseFloat);
      rgb = [p[0] || 240, p[1] || 130, p[2] || 30];
    }
  }

  function build() {
    var count = Math.max(44, Math.min(124, Math.round(W * H / 12500)));
    atoms = [];
    for (var i = 0; i < count; i++) {
      atoms.push({
        x: Math.random() * W,
        y: Math.random() * H,
        vx: (Math.random() - 0.5) * 0.26,
        vy: (Math.random() - 0.5) * 0.26,
        r: 0.9 + Math.random() * 1.7
      });
    }
    prox = new Array(atoms.length).fill(0);
  }

  function resize() {
    DPR = Math.min(1.75, window.devicePixelRatio || 1);
    W = canvas.clientWidth;
    H = canvas.clientHeight;
    canvas.width = Math.round(W * DPR);
    canvas.height = Math.round(H * DPR);
    ctx.setTransform(DPR, 0, 0, DPR, 0, 0);
    build();
    if (!running) drawStatic();
  }

  function step() {
    var i, a;
    pointer.ts *= 0.99;
    pointer.s += (pointer.ts - pointer.s) * 0.06;

    for (i = 0; i < atoms.length; i++) {
      a = atoms[i];
      a.x += a.vx; a.y += a.vy;

      if (pointer.s > 0.01) {
        var dx = pointer.x - a.x, dy = pointer.y - a.y;
        var d = Math.sqrt(dx * dx + dy * dy) || 0.001;
        if (d < INFL) {
          var f = (1 - d / INFL) * pointer.s;
          a.vx += (dx / d) * f * 0.055;
          a.vy += (dy / d) * f * 0.055;
          if (d < 62) {
            var push = (1 - d / 62) * 0.16;
            a.vx -= (dx / d) * push;
            a.vy -= (dy / d) * push;
          }
        }
      }

      a.vx += (Math.random() - 0.5) * 0.012;
      a.vy += (Math.random() - 0.5) * 0.012;
      a.vx *= 0.985; a.vy *= 0.985;
      var sp = Math.sqrt(a.vx * a.vx + a.vy * a.vy);
      if (sp > 0.9) { a.vx = a.vx / sp * 0.9; a.vy = a.vy / sp * 0.9; }

      if (a.x < -24) { a.x = -24; a.vx = Math.abs(a.vx); }
      else if (a.x > W + 24) { a.x = W + 24; a.vx = -Math.abs(a.vx); }
      if (a.y < -24) { a.y = -24; a.vy = Math.abs(a.vy); }
      else if (a.y > H + 24) { a.y = H + 24; a.vy = -Math.abs(a.vy); }

      if (pointer.s > 0.01) {
        var pdx = pointer.x - a.x, pdy = pointer.y - a.y;
        var pd = Math.sqrt(pdx * pdx + pdy * pdy);
        prox[i] = pd < INFL ? (1 - pd / INFL) * pointer.s : 0;
      } else {
        prox[i] = 0;
      }
    }
  }

  function render() {
    ctx.clearRect(0, 0, W, H);
    var i, j;
    for (i = 0; i < atoms.length; i++) {
      var a = atoms[i], pa = prox[i];
      for (j = i + 1; j < atoms.length; j++) {
        var b = atoms[j];
        var dx = a.x - b.x, dy = a.y - b.y;
        var dist = Math.sqrt(dx * dx + dy * dy);
        var near = pa > prox[j] ? pa : prox[j];
        var maxD = LINK_BASE + (LINK_NEAR - LINK_BASE) * near;
        if (dist < maxD) {
          var t = 1 - dist / maxD;
          var alpha = (0.15 + 0.42 * near) * t;
          ctx.strokeStyle = "rgba(" + rgb[0] + "," + rgb[1] + "," + rgb[2] + "," + alpha.toFixed(3) + ")";
          ctx.lineWidth = 0.7 + near * 0.8;
          ctx.beginPath();
          ctx.moveTo(a.x, a.y);
          ctx.lineTo(b.x, b.y);
          ctx.stroke();
        }
      }
      var g = 0.32 + 0.55 * pa;
      ctx.fillStyle = "rgba(" + rgb[0] + "," + rgb[1] + "," + rgb[2] + "," + g.toFixed(3) + ")";
      ctx.beginPath();
      ctx.arc(a.x, a.y, a.r + pa * 1.7, 0, 6.2831853);
      ctx.fill();
    }
  }

  function loop() { step(); render(); raf = requestAnimationFrame(loop); }
  function drawStatic() { for (var i = 0; i < prox.length; i++) prox[i] = 0; pointer.s = 0; render(); }
  function start() { if (running) return; running = true; raf = requestAnimationFrame(loop); }
  function stop() { running = false; cancelAnimationFrame(raf); }

  readColor();
  resize();

  if (rmq.matches) {
    drawStatic();
  } else {
    start();
    window.addEventListener("pointermove", function (e) {
      pointer.x = e.clientX; pointer.y = e.clientY; pointer.ts = 1;
    }, { passive: true });
    document.addEventListener("pointerleave", function () { pointer.ts = 0; });
    window.addEventListener("blur", function () { pointer.ts = 0; });
    document.addEventListener("visibilitychange", function () {
      if (document.hidden) stop(); else start();
    });
  }

  var rt;
  window.addEventListener("resize", function () {
    clearTimeout(rt);
    rt = setTimeout(resize, 200);
  });

  var onScheme = function () { readColor(); if (!running) drawStatic(); };
  var smq = window.matchMedia("(prefers-color-scheme: dark)");
  (smq.addEventListener ? smq.addEventListener.bind(smq, "change") : smq.addListener.bind(smq))(onScheme);
  new MutationObserver(onScheme).observe(document.documentElement, {
    attributes: true, attributeFilter: ["data-theme"]
  });
  (rmq.addEventListener ? rmq.addEventListener.bind(rmq, "change") : rmq.addListener.bind(rmq))(function () {
    if (rmq.matches) { stop(); drawStatic(); } else { start(); }
  });
})();
