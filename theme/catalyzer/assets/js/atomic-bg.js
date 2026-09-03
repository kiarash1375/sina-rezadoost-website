/* ============================================================
   کاتالیزور — پس‌زمینه‌ی شبکه‌ی کربن
   کندوی شناورِ پیوندهای C–C مثل ورقه‌ای از گرافن. دور نشانگر
   شبکه جمع‌تر و روشن‌تر می‌شود و رد پیوند دوگانه را نشان می‌دهد،
   انگار یک کاتالیزور تازه روی سطح نشسته است.
   ============================================================ */
(function () {
  "use strict";
  var canvas = document.getElementById("bgNet");
  if (!canvas || !canvas.getContext) return;
  var ctx = canvas.getContext("2d");

  var rmq = window.matchMedia("(prefers-reduced-motion: reduce)");
  var W = 0, H = 0, DPR = 1, raf = 0, running = false;
  var nodes = [], bonds = [], cells = [];
  var pointer = { x: -9999, y: -9999, s: 0, ts: 0 };
  var rgb = [240, 130, 30];

  var INFL = 300; // pointer influence radius

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

  /* build a honeycomb (graphene) lattice covering the viewport */
  function build() {
    nodes = []; bonds = []; cells = [];
    var minSide = Math.min(W, H);
    var A = Math.max(38, Math.min(64, minSide / 12)); // C–C bond length
    var vKey = {}, bKey = {};

    function vert(x, y) {
      var k = Math.round(x / 4) + "," + Math.round(y / 4);
      if (vKey[k] === undefined) {
        vKey[k] = nodes.length;
        nodes.push({
          bx: x, by: y, x: x, y: y, p: 0,
          ph: Math.random() * 6.283,
          sp: 0.35 + Math.random() * 0.5
        });
      }
      return vKey[k];
    }
    function bond(a, b) {
      if (a === b) return;
      var k = a < b ? a + "|" + b : b + "|" + a;
      if (bKey[k]) return;
      bKey[k] = 1;
      bonds.push([a, b]);
    }

    var dx = A * 1.5;          // flat-top hex: horizontal centre spacing
    var dy = A * Math.sqrt(3); // vertical centre spacing
    var m = A * 2.2;           // overscan so bonds run off every edge
    var col = 0;
    for (var cx = -m; cx < W + m; cx += dx, col++) {
      var yoff = (col & 1) ? dy / 2 : 0;
      for (var cy = -m; cy < H + m; cy += dy) {
        var ring = [];
        for (var i = 0; i < 6; i++) {
          var ang = 1.0471975 * i; // 60°
          ring.push(vert(cx + A * Math.cos(ang), cy + yoff + A * Math.sin(ang)));
        }
        cells.push(ring);
        for (var e = 0; e < 6; e++) bond(ring[e], ring[(e + 1) % 6]);
      }
    }
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

  function step(ts) {
    var time = (ts || 0) * 0.001;
    pointer.ts *= 0.99;
    pointer.s += (pointer.ts - pointer.s) * 0.06;

    var active = pointer.s > 0.01;
    for (var i = 0; i < nodes.length; i++) {
      var n = nodes[i];
      var ox = Math.cos(time * n.sp + n.ph) * 2.3;
      var oy = Math.sin(time * n.sp * 0.9 + n.ph * 1.3) * 2.3;

      var pr = 0, px = 0, py = 0;
      if (active) {
        var ddx = n.bx - pointer.x, ddy = n.by - pointer.y;
        var d = Math.sqrt(ddx * ddx + ddy * ddy) || 0.001;
        if (d < INFL) {
          var f = 1 - d / INFL;
          pr = f * f * pointer.s;
          var lift = Math.sin(f * 1.5708) * 9 * pointer.s;
          px = (ddx / d) * lift;
          py = (ddy / d) * lift;
        }
      }
      n.x = n.bx + ox + px;
      n.y = n.by + oy + py;
      n.p += (pr - n.p) * 0.12;
    }
  }

  function render() {
    ctx.clearRect(0, 0, W, H);
    var r = rgb[0], g = rgb[1], b = rgb[2], i, q;

    if (pointer.s > 0.01) {
      for (i = 0; i < cells.length; i++) {
        var ring = cells[i], acc = 0;
        for (q = 0; q < 6; q++) acc += nodes[ring[q]].p;
        acc /= 6;
        if (acc > 0.03) {
          ctx.beginPath();
          ctx.moveTo(nodes[ring[0]].x, nodes[ring[0]].y);
          for (q = 1; q < 6; q++) ctx.lineTo(nodes[ring[q]].x, nodes[ring[q]].y);
          ctx.closePath();
          ctx.fillStyle = "rgba(" + r + "," + g + "," + b + "," + (acc * 0.10).toFixed(3) + ")";
          ctx.fill();
        }
      }
    }

    ctx.lineCap = "round";
    for (i = 0; i < bonds.length; i++) {
      var a = nodes[bonds[i][0]], c = nodes[bonds[i][1]];
      var near = a.p > c.p ? a.p : c.p;
      ctx.strokeStyle = "rgba(" + r + "," + g + "," + b + "," + (0.05 + 0.5 * near).toFixed(3) + ")";
      ctx.lineWidth = 0.6 + near * 1.4;
      ctx.beginPath();
      ctx.moveTo(a.x, a.y);
      ctx.lineTo(c.x, c.y);
      ctx.stroke();

      if (near > 0.28) {
        var ex = c.x - a.x, ey = c.y - a.y;
        var pl = Math.sqrt(ex * ex + ey * ey) || 1;
        var nx = -ey / pl * 3.1, ny = ex / pl * 3.1;
        ctx.strokeStyle = "rgba(" + r + "," + g + "," + b + "," + ((near - 0.28) * 0.55).toFixed(3) + ")";
        ctx.lineWidth = 0.8;
        ctx.beginPath();
        ctx.moveTo(a.x + nx + ex * 0.2, a.y + ny + ey * 0.2);
        ctx.lineTo(c.x + nx - ex * 0.2, c.y + ny - ey * 0.2);
        ctx.stroke();
      }
    }

    for (i = 0; i < nodes.length; i++) {
      var nn = nodes[i];
      ctx.fillStyle = "rgba(" + r + "," + g + "," + b + "," + (0.13 + nn.p * 0.7).toFixed(3) + ")";
      ctx.beginPath();
      ctx.arc(nn.x, nn.y, 1.1 + nn.p * 2.6, 0, 6.2831853);
      ctx.fill();
    }
  }

  function loop(ts) { step(ts); render(); raf = requestAnimationFrame(loop); }

  function drawStatic() {
    for (var i = 0; i < nodes.length; i++) {
      var n = nodes[i];
      n.p = 0; n.x = n.bx; n.y = n.by;
    }
    pointer.s = 0;
    render();
  }

  function start() { if (running) return; running = true; raf = requestAnimationFrame(loop); }
  function stop() { running = false; cancelAnimationFrame(raf); }

  readColor();
  resize();

  if (rmq.matches) {
    drawStatic();
  } else {
    start();
    window.addEventListener("pointermove", function (e) {
      pointer.x = e.clientX;
      pointer.y = e.clientY;
      pointer.ts = 1;
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

  (rmq.addEventListener ? rmq.addEventListener.bind(rmq, "change") : rmq.addListener.bind(rmq))(function () {
    if (rmq.matches) { stop(); drawStatic(); } else { start(); }
  });
})();
