/* ------------------------------------------------------------------
   پلیر ویدیوی امن.

   آدرس ویدیو داخل HTML صفحه نیست. وقتی کاربر پخش را می‌زند، این فایل از
   سرور می‌پرسد و سرور تازه آن‌جا بررسی می‌کند که کاربر حق دیدن دارد یا نه.
   آن‌چه برمی‌گردد یک فهرست موقت است، نه فایل ویدیو.
   ------------------------------------------------------------------ */
(function () {
  "use strict";

  var CFG = window.catalyzerPlay || {};
  var HLS_CDN = "https://cdnjs.cloudflare.com/ajax/libs/hls.js/1.5.17/hls.min.js";
  var hlsPromise = null;

  function loadHls() {
    if (window.Hls) return Promise.resolve(window.Hls);
    if (hlsPromise) return hlsPromise;
    hlsPromise = new Promise(function (resolve, reject) {
      var s = document.createElement("script");
      s.src = HLS_CDN;
      s.onload = function () { resolve(window.Hls); };
      s.onerror = function () { reject(new Error("پخش‌کننده بارگذاری نشد.")); };
      document.head.appendChild(s);
    });
    return hlsPromise;
  }

  function say(box, msg) {
    box.innerHTML = '<p class="player-msg"></p>';
    box.querySelector(".player-msg").textContent = msg;
  }

  /* واترمارک: شماره‌ی خود بیننده، کم‌رنگ، و هر چند ثانیه جای تازه.
     جلوی ضبط صفحه را نمی‌گیرد — کپی‌کننده را قابل‌شناسایی می‌کند. */
  function watermark(box, text) {
    if (!text) return;
    var mark = document.createElement("span");
    mark.className = "player-mark";
    mark.textContent = text;
    mark.setAttribute("aria-hidden", "true");
    box.appendChild(mark);

    function move() {
      mark.style.insetInlineStart = (6 + Math.random() * 62).toFixed(2) + "%";
      mark.style.insetBlockStart = (8 + Math.random() * 74).toFixed(2) + "%";
    }
    move();
    var timer = setInterval(move, 7000);
    box.addEventListener("catalyzer:teardown", function () { clearInterval(timer); });
  }

  function buildVideo(box) {
    var v = document.createElement("video");
    v.className = "player-video";
    v.controls = true;
    v.playsInline = true;
    v.preload = "metadata";
    // سرعت‌گیر، نه قفل: منوی دانلود مرورگر و ذخیره‌ی راست‌کلیک را برمی‌دارد.
    v.setAttribute("controlsList", "nodownload noplaybackrate");
    v.disablePictureInPicture = true;
    v.addEventListener("contextmenu", function (e) { e.preventDefault(); });
    box.appendChild(v);
    return v;
  }

  /* لینک امضاشده از سرور. هر بار تازه، چون عمرش کوتاه است. */
  function getTicket(id) {
    var body = new URLSearchParams();
    body.set("action", "catalyzer_play");
    body.set("nonce", CFG.nonce);
    body.set("post", id);
    return fetch(CFG.ajax, {
      method: "POST",
      credentials: "same-origin",
      headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" },
      body: body.toString()
    }).then(function (r) {
      return r.json().then(function (j) {
        if (!r.ok || !j || !j.success) {
          throw new Error((j && j.data && j.data.message) || "پخش ممکن نشد.");
        }
        return j.data;
      });
    });
  }

  /* امضا کل مسیر ویدیو را پوشش می‌دهد و عمر کوتاهی دارد، پس وسط یک جلسه‌ی یک‌ساعته
     حتماً منقضی می‌شود. به‌جای اینکه پخش قطع شود، لینک تازه می‌گیریم و از همان
     ثانیه ادامه می‌دهیم — کاربر چیزی نمی‌بیند. همین اجازه می‌دهد لینک کوتاه بماند. */
  function attach(box, video, id, data) {
    var native = video.canPlayType("application/vnd.apple.mpegurl");

    if (native) {
      video.src = data.src;
      video.addEventListener("error", function () { renew(); });
      return Promise.resolve();
    }

    return loadHls().then(function (Hls) {
      if (!Hls || !Hls.isSupported()) throw new Error("مرورگر شما این پخش را پشتیبانی نمی‌کند.");
      var hls = new Hls({ lowLatencyMode: false });
      var renewing = false;

      hls.loadSource(data.src);
      hls.attachMedia(video);

      hls.on(Hls.Events.ERROR, function (_e, d) {
        if (!d.fatal) return;
        if (d.type === Hls.ErrorTypes.NETWORK_ERROR) {
          if (renewing) return;
          renewing = true;
          var at = video.currentTime;
          getTicket(id)
            .then(function (fresh) {
              hls.loadSource(fresh.src);
              hls.once(Hls.Events.MANIFEST_PARSED, function () {
                video.currentTime = at;
                var p = video.play();
                if (p && p.catch) p.catch(function () {});
              });
              renewing = false;
            })
            .catch(function (err) { hls.destroy(); say(box, err.message); });
        } else if (d.type === Hls.ErrorTypes.MEDIA_ERROR) {
          hls.recoverMediaError();
        } else {
          hls.destroy();
          say(box, "پخش قطع شد. صفحه را تازه کنید.");
        }
      });

      box.addEventListener("catalyzer:teardown", function () { hls.destroy(); });
    });

    function renew() {
      var at = video.currentTime;
      getTicket(id).then(function (fresh) {
        video.src = fresh.src;
        video.currentTime = at;
        var p = video.play();
        if (p && p.catch) p.catch(function () {});
      }).catch(function (err) { say(box, err.message); });
    }
  }

  /**
   * پخش یک ویدیو داخل یک عنصر.
   *
   * @param {HTMLElement} box   ظرف.
   * @param {number|string} id  شناسه‌ی نوشته‌ی ویدیو.
   */
  function mount(box, id) {
    if (!CFG.ajax || !CFG.nonce) {
      say(box, "پیکربندی پخش کامل نیست.");
      return;
    }
    say(box, "در حال آماده‌سازی…");

    getTicket(id)
      .then(function (data) {
        box.innerHTML = "";
        var video = buildVideo(box);
        watermark(box, data.watermark);
        return attach(box, video, id, data).then(function () {
          var p = video.play();
          if (p && p.catch) p.catch(function () { /* مرورگر اجازه‌ی پخش خودکار نداد — کاربر خودش می‌زند. */ });
        });
      })
      .catch(function (err) { say(box, err.message || "پخش ممکن نشد."); });
  }

  function teardown(box) {
    box.dispatchEvent(new CustomEvent("catalyzer:teardown"));
    box.innerHTML = "";
  }

  window.catalyzerPlayer = { mount: mount, teardown: teardown };

  /* پخش درون‌خطی در تک‌صفحه‌ی ویدیو */
  document.addEventListener("click", function (e) {
    var btn = e.target.closest ? e.target.closest("[data-play-arvan]") : null;
    if (!btn) return;
    e.preventDefault();
    var box = document.getElementById(btn.getAttribute("data-play-arvan"));
    if (box) mount(box, btn.getAttribute("data-post"));
  });
})();
