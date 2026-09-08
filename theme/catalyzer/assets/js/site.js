/* کاتالیزور — تعامل‌های رابط کاربری */
(function () {
  "use strict";

  /* --- mobile nav --- */
  var navToggle = document.getElementById("navToggle");
  var navPanel = document.getElementById("navPanel");
  if (navToggle && navPanel) {
    navToggle.addEventListener("click", function () {
      var open = navPanel.classList.toggle("open");
      navToggle.setAttribute("aria-expanded", open ? "true" : "false");
    });
    navPanel.addEventListener("click", function (e) {
      if (e.target.closest("a")) {
        navPanel.classList.remove("open");
        navToggle.setAttribute("aria-expanded", "false");
      }
    });
  }

  /* --- header shadow on scroll --- */
  var header = document.querySelector(".site-header");
  if (header) {
    var onScroll = function () { header.classList.toggle("scrolled", window.scrollY > 8); };
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
  }

  /* --- reveal on scroll --- */
  var items = document.querySelectorAll(".reveal, .stagger");
  if (items.length) {
    if ("IntersectionObserver" in window) {
      var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (en) {
          if (en.isIntersecting) { en.target.classList.add("in"); io.unobserve(en.target); }
        });
      }, { threshold: 0.12, rootMargin: "0px 0px -8% 0px" });
      items.forEach(function (el) { io.observe(el); });
    } else {
      items.forEach(function (el) { el.classList.add("in"); });
    }
  }

  /* --- active nav link (scroll-spy on the one-pager) --- */
  var links = Array.prototype.slice.call(document.querySelectorAll(".js-navlink"));
  if (links.length && "IntersectionObserver" in window) {
    var map = {};
    links.forEach(function (a) {
      var href = a.getAttribute("href") || "";
      var hash = href.indexOf("#") >= 0 ? href.slice(href.indexOf("#") + 1) : "";
      var sec = hash && document.getElementById(hash);
      if (sec) map[hash] = a;
    });
    var spy = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) {
          links.forEach(function (a) { a.classList.remove("active"); });
          if (map[en.target.id]) map[en.target.id].classList.add("active");
        }
      });
    }, { threshold: 0.5 });
    Object.keys(map).forEach(function (id) { spy.observe(document.getElementById(id)); });
  }
})();

/* ---------------------------------------------------------------------------
 * Login / signup with an SMS one-time code.
 * ------------------------------------------------------------------------ */
(function () {
  var root = document.getElementById("catAuth");
  if (!root || typeof CatalyzerAuth === "undefined") return;

  var stepPhone = root.querySelector('[data-step="phone"]');
  var stepCode = root.querySelector('[data-step="code"]');
  var elPhone = document.getElementById("catPhone");
  var elCode = document.getElementById("catCode");
  var elName = document.getElementById("catName");
  var elField = document.getElementById("catField");
  var elNewUser = document.getElementById("catNewUser");
  var elEcho = document.getElementById("catPhoneEcho");
  var elMsg = document.getElementById("catAuthMsg");
  var elTimer = document.getElementById("catTimer");
  var btnSend = document.getElementById("catSendOtp");
  var btnVerify = document.getElementById("catVerifyOtp");
  var btnResend = document.getElementById("catResend");
  var btnEdit = document.getElementById("catEditPhone");
  var ticker = null;

  function say(text, kind) {
    elMsg.textContent = text || "";
    elMsg.className = "auth-msg" + (kind ? " is-" + kind : "");
  }

  function busy(button, on, labelWhenBusy) {
    if (!button) return;
    if (on) {
      button.dataset.label = button.textContent;
      button.textContent = labelWhenBusy || "لطفاً صبر کنید…";
      button.disabled = true;
    } else {
      if (button.dataset.label) button.textContent = button.dataset.label;
      button.disabled = false;
    }
  }

  function post(action, data) {
    var body = new URLSearchParams();
    body.append("action", action);
    body.append("nonce", CatalyzerAuth.nonce);
    Object.keys(data).forEach(function (k) { body.append(k, data[k]); });
    return fetch(CatalyzerAuth.ajaxUrl, {
      method: "POST",
      credentials: "same-origin",
      headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" },
      body: body.toString()
    }).then(function (r) { return r.json(); });
  }

  function startCountdown(seconds) {
    if (ticker) clearInterval(ticker);
    var left = seconds;
    btnResend.disabled = true;
    elTimer.textContent = "(" + left + " ثانیه)";
    ticker = setInterval(function () {
      left -= 1;
      if (left <= 0) {
        clearInterval(ticker);
        ticker = null;
        elTimer.textContent = "";
        btnResend.disabled = false;
      } else {
        elTimer.textContent = "(" + left + " ثانیه)";
      }
    }, 1000);
  }

  function sendCode(button) {
    var phone = (elPhone.value || "").trim();
    if (!phone) { say("شماره‌ی موبایل را وارد کن.", "err"); elPhone.focus(); return; }

    busy(button, true, "در حال ارسال…");
    post("catalyzer_send_otp", { phone: phone })
      .then(function (res) {
        busy(button, false);
        if (!res || !res.success) {
          say((res && res.data && res.data.message) || "ارسال کد ناموفق بود.", "err");
          return;
        }
        stepPhone.hidden = true;
        stepCode.hidden = false;
        elEcho.textContent = res.data.phone;
        elNewUser.hidden = !res.data.is_new;
        elCode.value = "";
        elCode.focus();
        startCountdown(60);
        if (res.data.test_code) {
          say("حالت تست — کد شما: " + res.data.test_code, "test");
        } else {
          say("کد برای " + res.data.phone + " ارسال شد.", "ok");
        }
      })
      .catch(function () { busy(button, false); say("خطای شبکه. دوباره تلاش کن.", "err"); });
  }

  btnSend.addEventListener("click", function () { sendCode(btnSend); });
  btnResend.addEventListener("click", function () { sendCode(btnResend); });

  elPhone.addEventListener("keydown", function (e) {
    if (e.key === "Enter") { e.preventDefault(); sendCode(btnSend); }
  });

  btnEdit.addEventListener("click", function () {
    stepCode.hidden = true;
    stepPhone.hidden = false;
    say("");
    elPhone.focus();
  });

  function verify() {
    var code = (elCode.value || "").trim();
    if (!code) { say("کد را وارد کن.", "err"); elCode.focus(); return; }
    if (!elNewUser.hidden && !(elName.value || "").trim()) {
      say("نام و نام خانوادگی را وارد کن.", "err");
      elName.focus();
      return;
    }

    busy(btnVerify, true, "در حال بررسی…");
    post("catalyzer_verify_otp", {
      phone: elEcho.textContent,
      code: code,
      name: elName ? elName.value : "",
      field: elField ? elField.value : ""
    })
      .then(function (res) {
        if (!res || !res.success) {
          busy(btnVerify, false);
          say((res && res.data && res.data.message) || "تأیید ناموفق بود.", "err");
          return;
        }
        say(res.data.created ? "حساب ساخته شد. در حال ورود…" : "خوش آمدی. در حال ورود…", "ok");
        window.location.href = res.data.redirect;
      })
      .catch(function () { busy(btnVerify, false); say("خطای شبکه. دوباره تلاش کن.", "err"); });
  }

  btnVerify.addEventListener("click", verify);
  elCode.addEventListener("keydown", function (e) {
    if (e.key === "Enter") { e.preventDefault(); verify(); }
  });
})();

/* ---------------------------------------------------------------------------
 * Success-story videos: play inside a lightbox, one iframe at a time.
 * ------------------------------------------------------------------------ */
(function () {
  var cards = Array.prototype.slice.call(document.querySelectorAll(".sv-card[data-embed]"));
  if (!cards.length) return;

  var modal = document.createElement("div");
  modal.className = "sv-modal";
  modal.setAttribute("hidden", "");
  modal.innerHTML =
    '<div class="sv-modal-backdrop" data-close></div>' +
    '<div class="sv-modal-body" role="dialog" aria-modal="true">' +
    '<button type="button" class="sv-modal-close" data-close aria-label="بستن">&times;</button>' +
    '<div class="sv-modal-slot"></div>' +
    "</div>";
  document.body.appendChild(modal);

  var slot = modal.querySelector(".sv-modal-slot");
  var lastFocus = null;

  function open(card) {
    lastFocus = card;
    slot.innerHTML = decodeURIComponent(card.getAttribute("data-embed"));
    modal.removeAttribute("hidden");
    document.body.classList.add("sv-modal-open");
    var btn = modal.querySelector(".sv-modal-close");
    if (btn) btn.focus();
  }

  function close() {
    slot.innerHTML = "";
    modal.setAttribute("hidden", "");
    document.body.classList.remove("sv-modal-open");
    if (lastFocus) lastFocus.focus();
  }

  cards.forEach(function (card) {
    card.addEventListener("click", function (e) { e.preventDefault(); open(card); });
    card.addEventListener("keydown", function (e) {
      if (e.key === "Enter" || e.key === " ") { e.preventDefault(); open(card); }
    });
  });

  modal.addEventListener("click", function (e) {
    if (e.target.hasAttribute("data-close")) close();
  });
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && !modal.hasAttribute("hidden")) close();
  });
})();
