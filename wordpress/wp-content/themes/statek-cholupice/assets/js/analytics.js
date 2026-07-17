(() => {
  "use strict";

  const banner = document.querySelector("[data-cookie-banner]");
  if (!banner) return;

  const measurementId = (banner.dataset.measurementId || "").trim().toUpperCase();
  const productionDomains = (banner.dataset.productionDomains || "")
    .split(",")
    .map((domain) => domain.trim().toLowerCase())
    .filter(Boolean);
  const consentVersion = Number.parseInt(banner.dataset.consentVersion || "", 10);
  const cookieName = (banner.dataset.consentCookie || "").trim();
  const hostname = window.location.hostname.toLowerCase().replace(/\.$/, "");

  if (
    !/^G-[A-Z0-9]+$/.test(measurementId) ||
    !Number.isInteger(consentVersion) ||
    consentVersion < 1 ||
    !/^[a-z0-9_]+$/.test(cookieName) ||
    !productionDomains.includes(hostname)
  ) {
    return;
  }

  window.dataLayer = window.dataLayer || [];
  window.gtag = window.gtag || function gtag() {
    window.dataLayer.push(arguments);
  };

  window.gtag("consent", "default", {
    analytics_storage: "denied",
    ad_storage: "denied",
    ad_user_data: "denied",
    ad_personalization: "denied",
    functionality_storage: "granted",
    security_storage: "granted"
  });

  const COOKIE_MAX_AGE_SECONDS = 180 * 24 * 60 * 60;
  const COOKIE_MAX_AGE_MS = COOKIE_MAX_AGE_SECONDS * 1000;
  const FUTURE_TOLERANCE_MS = 5 * 60 * 1000;
  const allowedParameters = {
    section_view: ["section_id", "section_name"],
    faq_open: ["faq_id", "faq_position", "faq_title"],
    before_after_interaction: ["slider_id", "slider_position", "interaction_method"],
    before_after_complete: ["slider_id", "slider_position", "view"],
    area_tab_select: ["area_id", "area_title", "area_position"],
    contact_click: ["method", "placement"],
    generate_lead: ["method"],
    select_content: ["content_type", "item_id", "item_name", "placement"]
  };
  const restrictedValues = {
    interaction_method: ["pointer", "keyboard"],
    view: ["before", "after"],
    method: ["email", "contact_form"],
    placement: ["footer", "faq", "contact", "homepage", "archive", "site"],
    content_type: ["news"]
  };

  let consentState = null;
  let googleRequested = false;
  let googleConfigured = false;
  let scheduleVisibleSections = () => {};

  function readCookie(name) {
    const prefix = `${name}=`;
    const pair = document.cookie.split(";").map((item) => item.trim()).find((item) => item.startsWith(prefix));
    return pair ? pair.slice(prefix.length) : "";
  }

  function readConsent() {
    const rawValue = readCookie(cookieName);
    if (!rawValue) return null;

    try {
      const value = JSON.parse(decodeURIComponent(rawValue));
      const updatedAt = Date.parse(value.updatedAt);
      const now = Date.now();
      if (
        value.version !== consentVersion ||
        !["granted", "denied"].includes(value.analytics) ||
        !Number.isFinite(updatedAt) ||
        updatedAt > now + FUTURE_TOLERANCE_MS ||
        now - updatedAt > COOKIE_MAX_AGE_MS
      ) {
        return null;
      }
      return value;
    } catch (error) {
      return null;
    }
  }

  function writeConsent(analytics) {
    const value = encodeURIComponent(JSON.stringify({
      version: consentVersion,
      analytics,
      updatedAt: new Date().toISOString()
    }));
    const secure = window.location.protocol === "https:" ? "; Secure" : "";
    document.cookie = `${cookieName}=${value}; Max-Age=${COOKIE_MAX_AGE_SECONDS}; Path=/; SameSite=Lax${secure}`;
    consentState = analytics;
  }

  function setBannerVisible(visible, focusFirstControl = false) {
    banner.hidden = !visible;
    banner.classList.toggle("is-visible", visible);
    if (visible && focusFirstControl) {
      window.requestAnimationFrame(() => banner.querySelector("[data-cookie-allow]")?.focus());
    }
  }

  function updateConsent(analytics) {
    window.gtag("consent", "update", {
      analytics_storage: analytics,
      ad_storage: "denied",
      ad_user_data: "denied",
      ad_personalization: "denied"
    });
  }

  function loadGoogleAnalytics() {
    if (googleRequested || consentState !== "granted") return;
    googleRequested = true;

    if (!document.querySelector("script[data-statek-ga4]")) {
      const script = document.createElement("script");
      script.async = true;
      script.dataset.statekGa4 = "true";
      script.src = `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(measurementId)}`;
      script.addEventListener("error", () => {
        // Analytics is optional; a blocker or network failure must not affect the website.
      }, { once: true });
      document.head.append(script);
    }

    if (!googleConfigured) {
      googleConfigured = true;
      window.gtag("js", new Date());
      window.gtag("config", measurementId, {
        allow_google_signals: false,
        allow_ad_personalization_signals: false,
        send_page_view: true
      });
    }
  }

  function normalizeValue(key, value) {
    if (typeof value === "number") {
      return Number.isFinite(value) ? value : null;
    }
    if (typeof value !== "string") return null;

    const normalized = value.replace(/\s+/g, " ").trim().slice(0, 100);
    const containsUrlWithQuery = /\S+\?[a-z0-9_.~-]+=/i.test(normalized);
    if (!normalized || normalized.includes("@") || normalized.includes("://") || containsUrlWithQuery) {
      return null;
    }
    if (restrictedValues[key] && !restrictedValues[key].includes(normalized)) {
      return null;
    }
    return normalized;
  }

  function track(eventName, parameters = {}) {
    if (consentState !== "granted" || !allowedParameters[eventName]) return false;

    const safeParameters = {};
    allowedParameters[eventName].forEach((key) => {
      const value = normalizeValue(key, parameters[key]);
      if (value !== null) safeParameters[key] = value;
    });

    window.gtag("event", eventName, safeParameters);
    return true;
  }

  function deleteAnalyticsCookies() {
    const names = document.cookie
      .split(";")
      .map((item) => item.trim().split("=")[0])
      .filter((name) => name === "_ga" || name.startsWith("_ga_") || name === "_gid" || name === "_gat" || name.startsWith("_gat_"));
    const domains = ["", hostname, "statekcholupice.cz", ".statekcholupice.cz"];
    const secure = window.location.protocol === "https:" ? "; Secure" : "";

    [...new Set(names)].forEach((name) => {
      domains.forEach((domain) => {
        const domainPart = domain ? `; Domain=${domain}` : "";
        document.cookie = `${name}=; Expires=Thu, 01 Jan 1970 00:00:00 GMT; Max-Age=0; Path=/${domainPart}; SameSite=Lax${secure}`;
      });
    });
  }

  function allowAnalytics() {
    writeConsent("granted");
    updateConsent("granted");
    loadGoogleAnalytics();
    setBannerVisible(false);
    scheduleVisibleSections();
  }

  function denyAnalytics() {
    const analyticsWasActive = consentState === "granted" || googleRequested;
    updateConsent("denied");
    deleteAnalyticsCookies();
    writeConsent("denied");
    setBannerVisible(false);
    if (analyticsWasActive) window.location.reload();
  }

  function openPreferences() {
    setBannerVisible(true, true);
  }

  window.StatekAnalytics = Object.freeze({
    recordLead: () => track("generate_lead", { method: "contact_form" }),
    hasConsent: () => consentState === "granted",
    openPreferences
  });

  banner.querySelector("[data-cookie-allow]")?.addEventListener("click", allowAnalytics);
  banner.querySelector("[data-cookie-deny]")?.addEventListener("click", denyAnalytics);
  document.querySelectorAll("[data-cookie-preferences]").forEach((button) => {
    button.addEventListener("click", openPreferences);
  });
  banner.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && consentState) {
      setBannerVisible(false);
      document.querySelector("[data-cookie-preferences]")?.focus();
    }
  });

  function initializeSectionTracking() {
    if (!("IntersectionObserver" in window)) return;

    const definitions = [
      ["#projekt", "projekt", "Projekt"],
      ["#popis-arealu", "podoba-arealu", "Podoba areálu"],
      ["#provoz-arealu", "provoz", "Provoz"],
      ["#bezpecnost", "bezpecnost", "Bezpečnost"],
      ["#doprava", "doprava", "Doprava"],
      ["#zivotni-prostredi", "zivotni-prostredi", "Životní prostředí"],
      ["#prinosy", "prinosy", "Přínosy"],
      ["#novinky", "novinky", "Novinky"],
      ["#kontakt .faq-heading", "faq", "Časté otázky"],
      ["#kontakt .faq-contact-block", "kontakt", "Kontakt"]
    ];
    const metadata = new Map();
    const visible = new Set();
    const completed = new Set();
    const timers = new Map();

    definitions.forEach(([selector, sectionId, sectionName]) => {
      const element = document.querySelector(selector);
      if (element && !element.hidden) metadata.set(element, { sectionId, sectionName });
    });

    function requiredVisibleHeight(element) {
      const sectionHeight = element.getBoundingClientRect().height;
      return Math.min(sectionHeight * 0.5, window.innerHeight * 0.5);
    }

    function hasMeaningfulVisibility(element) {
      const rect = element.getBoundingClientRect();
      const visibleHeight = Math.max(0, Math.min(rect.bottom, window.innerHeight) - Math.max(rect.top, 0));
      return visibleHeight + 1 >= requiredVisibleHeight(element);
    }

    function schedule(element) {
      if (completed.has(element) || timers.has(element) || !visible.has(element)) return;
      timers.set(element, window.setTimeout(() => {
        timers.delete(element);
        if (!visible.has(element) || !hasMeaningfulVisibility(element) || !window.StatekAnalytics.hasConsent()) return;
        const data = metadata.get(element);
        if (track("section_view", { section_id: data.sectionId, section_name: data.sectionName })) {
          completed.add(element);
          observer.unobserve(element);
        }
      }, 1000));
    }

    scheduleVisibleSections = () => visible.forEach(schedule);

    const thresholds = Array.from({ length: 51 }, (_, index) => index / 100);
    thresholds.push(1);

    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting && hasMeaningfulVisibility(entry.target)) {
          visible.add(entry.target);
          schedule(entry.target);
        } else {
          visible.delete(entry.target);
          window.clearTimeout(timers.get(entry.target));
          timers.delete(entry.target);
        }
      });
    }, { threshold: thresholds });

    metadata.forEach((value, element) => observer.observe(element));
  }

  function initializeInteractionTracking() {
    document.querySelectorAll(".faq-item").forEach((item, index) => {
      const button = item.querySelector(".faq-question");
      button?.addEventListener("click", () => {
        if (button.getAttribute("aria-expanded") !== "false") return;
        track("faq_open", {
          faq_id: button.getAttribute("aria-controls") || `faq-${index + 1}`,
          faq_position: index + 1,
          faq_title: button.textContent || `FAQ ${index + 1}`
        });
      });
    });

    document.querySelectorAll("[data-before-after]").forEach((slider, index) => {
      const range = slider.querySelector("input[type='range']");
      if (!range) return;
      const sliderId = slider.dataset.sliderId || `slider-${index + 1}`;
      const completedViews = new Set();
      let interactionTracked = false;
      let interactionMethod = "";

      function trackInteraction(method) {
        if (interactionTracked) return;
        interactionTracked = track("before_after_interaction", {
          slider_id: sliderId,
          slider_position: index + 1,
          interaction_method: method
        });
      }

      function trackCompletion() {
        const value = Number(range.value);
        const view = value >= 95 ? "before" : value <= 5 ? "after" : "";
        if (!view || completedViews.has(view)) return;
        if (track("before_after_complete", {
          slider_id: sliderId,
          slider_position: index + 1,
          view
        })) {
          completedViews.add(view);
        }
      }

      range.addEventListener("pointerdown", () => {
        interactionMethod = "pointer";
        trackInteraction("pointer");
      });
      range.addEventListener("keydown", (event) => {
        if (!["ArrowLeft", "ArrowRight", "ArrowUp", "ArrowDown", "Home", "End", "PageUp", "PageDown"].includes(event.key)) return;
        interactionMethod = "keyboard";
        trackInteraction("keyboard");
      });
      range.addEventListener("keyup", (event) => {
        if (["ArrowLeft", "ArrowRight", "ArrowUp", "ArrowDown", "Home", "End", "PageUp", "PageDown"].includes(event.key)) {
          trackCompletion();
        }
      });
      range.addEventListener("input", () => {
        trackInteraction(interactionMethod || "keyboard");
        trackCompletion();
      });
    });

    const areaTabs = Array.from(document.querySelectorAll(".site-area-tab"));
    function trackArea(tab, index) {
      const panelId = tab.getAttribute("aria-controls") || "";
      const title = document.getElementById(panelId)?.querySelector("h3")?.textContent || `Část areálu ${index + 1}`;
      track("area_tab_select", {
        area_id: panelId || `area-${index + 1}`,
        area_title: title,
        area_position: index + 1
      });
    }
    areaTabs.forEach((tab, index) => {
      tab.addEventListener("click", () => {
        if (tab.getAttribute("aria-selected") === "false") trackArea(tab, index);
      });
      tab.addEventListener("keydown", (event) => {
        let nextIndex = index;
        if (event.key === "ArrowRight") nextIndex = (index + 1) % areaTabs.length;
        if (event.key === "ArrowLeft") nextIndex = (index - 1 + areaTabs.length) % areaTabs.length;
        if (event.key === "Home") nextIndex = 0;
        if (event.key === "End") nextIndex = areaTabs.length - 1;
        if (nextIndex !== index) trackArea(areaTabs[nextIndex], nextIndex);
      });
    });

    document.addEventListener("click", (event) => {
      const mailLink = event.target.closest("a[href^='mailto:']");
      if (mailLink) {
        let placement = "site";
        if (mailLink.closest("footer")) placement = "footer";
        else if (mailLink.closest(".faq-contact-block")) placement = "contact";
        else if (mailLink.closest(".faq-list, .faq-heading")) placement = "faq";
        track("contact_click", { method: "email", placement });
      }

      const newsLink = event.target.closest(".news-card a");
      if (newsLink) {
        const card = newsLink.closest(".news-card");
        track("select_content", {
          content_type: "news",
          item_id: card?.dataset.newsId || "news-item",
          item_name: card?.dataset.newsTitle || card?.querySelector("h3")?.textContent || "Novinka",
          placement: document.body.classList.contains("home") ? "homepage" : "archive"
        });
      }
    });
  }

  initializeSectionTracking();
  initializeInteractionTracking();

  const savedConsent = readConsent();
  if (!savedConsent) {
    consentState = null;
    setBannerVisible(true);
  } else if (savedConsent.analytics === "granted") {
    consentState = "granted";
    updateConsent("granted");
    loadGoogleAnalytics();
    scheduleVisibleSections();
  } else {
    consentState = "denied";
  }
})();
