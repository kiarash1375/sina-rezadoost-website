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
