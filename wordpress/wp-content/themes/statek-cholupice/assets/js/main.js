
    document.querySelectorAll("[data-before-after]").forEach((slider) => {
      const range = slider.querySelector("input");
      const after = slider.querySelector(".after");
      const handle = slider.querySelector(".ba-handle");
      const beforeLabel = slider.querySelector(".ba-label.before");
      const afterLabel = slider.querySelector(".ba-label.after-label");

      function updateLabels(value) {
        const width = slider.clientWidth || 1;
        const handleX = width * (Number(value) / 100);
        const safeGap = 86;
        const beforeWidth = beforeLabel?.offsetWidth || 0;
        const afterWidth = afterLabel?.offsetWidth || 0;
        beforeLabel?.classList.toggle("is-visible", handleX > beforeWidth + safeGap);
        afterLabel?.classList.toggle("is-visible", width - handleX > afterWidth + safeGap);
      }

      function update(value) {
        const numericValue = Math.round(Number(value));
        after.style.clipPath = `inset(0 0 0 ${value}%)`;
        handle.style.left = `${value}%`;
        range.setAttribute("aria-valuenow", String(numericValue));
        range.setAttribute("aria-valuetext", `${numericValue} % navrhované podoby`);
        updateLabels(value);
      }

      range.addEventListener("input", (event) => update(event.target.value));
      range.addEventListener("keydown", (event) => {
        if (event.key !== "Home" && event.key !== "End") return;
        event.preventDefault();
        range.value = event.key === "Home" ? range.min : range.max;
        update(range.value);
      });
      range.addEventListener("pointerdown", () => slider.classList.add("is-dragging"));
      ["pointerup", "pointercancel"].forEach((eventName) => {
        window.addEventListener(eventName, () => slider.classList.remove("is-dragging"));
      });
      range.addEventListener("blur", () => slider.classList.remove("is-dragging"));
      window.addEventListener("resize", () => update(range.value));
      update(range.value);
    });

    const areaTabs = Array.from(document.querySelectorAll(".site-area-tab"));
    const areaPanels = Array.from(document.querySelectorAll(".site-area-panel"));
    function activateArea(index, moveFocus = false) {
      areaTabs.forEach((tab, tabIndex) => {
        const active = tabIndex === index;
        tab.setAttribute("aria-selected", String(active));
        tab.tabIndex = active ? 0 : -1;
      });
      const activePanelId = areaTabs[index].getAttribute("aria-controls");
      areaPanels.forEach((panel) => {
        panel.setAttribute("aria-hidden", String(panel.id !== activePanelId));
      });
      if (moveFocus) areaTabs[index].focus();
    }
    areaTabs.forEach((tab, index) => {
      tab.addEventListener("click", () => activateArea(index));
      tab.addEventListener("keydown", (event) => {
        let nextIndex = index;
        if (event.key === "ArrowRight") nextIndex = (index + 1) % areaTabs.length;
        if (event.key === "ArrowLeft") nextIndex = (index - 1 + areaTabs.length) % areaTabs.length;
        if (event.key === "Home") nextIndex = 0;
        if (event.key === "End") nextIndex = areaTabs.length - 1;
        if (nextIndex !== index || event.key === "Home" || event.key === "End") {
          event.preventDefault();
          activateArea(nextIndex, true);
        }
      });
    });

    const header = document.querySelector("header");
    const mobileMenuToggle = document.querySelector("#mobile-menu-toggle");
    const mobileMenu = document.querySelector("#mobile-menu");
    const mobileMenuLinks = Array.from(document.querySelectorAll("#mobile-menu a"));
    function setMobileMenu(open, returnFocus = true) {
      if (!mobileMenu || !mobileMenuToggle) return;
      mobileMenu.hidden = !open;
      mobileMenu.classList.toggle("is-open", open);
      mobileMenuToggle.setAttribute("aria-expanded", String(open));
      mobileMenuToggle.setAttribute("aria-label", open ? "Zavřít menu" : "Otevřít menu");
      document.body.classList.toggle("menu-open", open);
      if (open) mobileMenuLinks[0]?.focus();
      else if (returnFocus) mobileMenuToggle.focus();
    }
    mobileMenuToggle?.addEventListener("click", () => {
      const open = mobileMenuToggle.getAttribute("aria-expanded") === "true";
      setMobileMenu(!open);
    });
    mobileMenuLinks.forEach((link) => link.addEventListener("click", () => setMobileMenu(false, false)));
    document.addEventListener("keydown", (event) => {
      if (event.key === "Escape" && mobileMenuToggle?.getAttribute("aria-expanded") === "true") setMobileMenu(false);
    });
    document.addEventListener("click", (event) => {
      if (mobileMenuToggle?.getAttribute("aria-expanded") !== "true" || header?.contains(event.target)) return;
      setMobileMenu(false, false);
    });

    let scrollTicking = false;
    window.addEventListener("scroll", () => {
      if (scrollTicking) return;
      scrollTicking = true;
      window.requestAnimationFrame(() => {
        header?.classList.toggle("is-scrolled", window.scrollY > 56);
        scrollTicking = false;
      });
    }, { passive: true });
    header?.classList.toggle("is-scrolled", window.scrollY > 56);

    const sectionLinks = Array.from(document.querySelectorAll("header a[href^='#']:not(.button)"));
    const sectionIds = [...new Set(sectionLinks.map((link) => link.getAttribute("href").slice(1)))];
    const sectionTargets = sectionIds.map((id) => document.getElementById(id)).filter(Boolean);
    function setActiveSection(id) {
      sectionLinks.forEach((link) => {
        const active = link.getAttribute("href") === `#${id}`;
        link.classList.toggle("is-active", active);
        if (active) link.setAttribute("aria-current", "page");
        else link.removeAttribute("aria-current");
      });
    }
    if ("IntersectionObserver" in window) {
      const sectionObserver = new IntersectionObserver((entries) => {
        const visible = entries.filter((entry) => entry.isIntersecting).sort((a, b) => b.intersectionRatio - a.intersectionRatio);
        if (visible[0]) setActiveSection(visible[0].target.id);
      }, { rootMargin: "-22% 0px -62% 0px", threshold: [0, .2, .5] });
      sectionTargets.forEach((section) => sectionObserver.observe(section));
    }

      const revealTargets = Array.from(document.querySelectorAll(".story-block, .site-area, .site-operation-section, .topic, .benefit, .faq-item, .faq-contact-block, footer .footer-main, footer .footer-notice, footer .footer-bottom"));
    revealTargets.forEach((element) => element.classList.add("reveal"));
    const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    if (reducedMotion || !("IntersectionObserver" in window)) {
      revealTargets.forEach((element) => element.classList.add("is-visible"));
    } else {
      const revealObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return;
          entry.target.classList.add("is-visible");
          observer.unobserve(entry.target);
        });
      }, { threshold: .12, rootMargin: "0px 0px -8% 0px" });
      revealTargets.forEach((element) => revealObserver.observe(element));
    }

    const faqItems = Array.from(document.querySelectorAll(".faq-item"));
    function closeFaqItem(item) {
      const button = item.querySelector(".faq-question");
      const answer = item.querySelector(".faq-answer");
      item.classList.remove("is-open");
      button.setAttribute("aria-expanded", "false");
      answer.setAttribute("aria-hidden", "true");
    }
    faqItems.forEach((item) => {
      const button = item.querySelector(".faq-question");
      const answer = item.querySelector(".faq-answer");
      button.addEventListener("click", () => {
        const shouldOpen = !item.classList.contains("is-open");
        faqItems.forEach(closeFaqItem);
        if (shouldOpen) {
          item.classList.add("is-open");
          button.setAttribute("aria-expanded", "true");
          answer.setAttribute("aria-hidden", "false");
        }
      });
    });

    const NEWS_INDEX_URL = window.StatekCholupice?.newsIndexUrl || "";
    const newsSection = document.querySelector("#novinky");
    const newsTrack = document.querySelector("[data-news-track]");
    const newsViewport = document.querySelector("[data-news-viewport]");
    const newsPrev = document.querySelector("[data-news-prev]");
    const newsNext = document.querySelector("[data-news-next]");
    const newsStatus = document.querySelector("[data-news-status]");
    const newsAllLink = document.querySelector("[data-news-all]");
    let newsCards = [];
    let newsIndex = 0;

    const getNewsVisibleCount = () => {
      if (window.innerWidth < 861) return 1;
      if (window.innerWidth < 1101) return 2;
      return 3;
    };

    function updateNewsCarousel(announce = false) {
      if (!newsCards.length) return;
      const visibleCount = getNewsVisibleCount();
      const maxIndex = Math.max(0, newsCards.length - visibleCount);
      newsIndex = Math.min(newsIndex, maxIndex);
      const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

      if (window.innerWidth < 861) {
        newsViewport.scrollTo({ left: newsCards[newsIndex].offsetLeft, behavior: reducedMotion ? "auto" : "smooth" });
      } else {
        newsTrack.style.transform = `translate3d(-${newsCards[newsIndex].offsetLeft}px, 0, 0)`;
      }

      newsPrev.disabled = newsIndex === 0;
      newsNext.disabled = newsIndex === maxIndex;
      if (announce) newsStatus.textContent = `Zobrazeny novinky ${newsIndex + 1} až ${Math.min(newsIndex + visibleCount, newsCards.length)} z ${newsCards.length}.`;
    }

    function initializeNewsCarousel() {
      if (!newsSection || !newsTrack) return;
      newsCards = Array.from(newsTrack.querySelectorAll(".news-card"));
      if (!newsCards.length) {
        newsSection.hidden = true;
        return;
      }

      newsSection.hidden = false;
      newsIndex = 0;
      newsAllLink.hidden = !NEWS_INDEX_URL;
      if (NEWS_INDEX_URL) newsAllLink.href = NEWS_INDEX_URL;
      updateNewsCarousel();
    }

    newsPrev?.addEventListener("click", () => {
      newsIndex = Math.max(0, newsIndex - 1);
      updateNewsCarousel(true);
    });
    newsNext?.addEventListener("click", () => {
      newsIndex += 1;
      updateNewsCarousel(true);
    });
    newsViewport?.addEventListener("keydown", (event) => {
      if (event.key === "ArrowLeft") {
        event.preventDefault();
        newsIndex = Math.max(0, newsIndex - 1);
        updateNewsCarousel(true);
      }
      if (event.key === "ArrowRight") {
        event.preventDefault();
        newsIndex += 1;
        updateNewsCarousel(true);
      }
    });
    window.addEventListener("resize", () => updateNewsCarousel());
    initializeNewsCarousel();

    const CONTACT_ENDPOINT = window.StatekCholupice?.contactEndpoint || "";
    const contactForm = document.querySelector("#faq-contact-form");
    const contactStatus = document.querySelector("#contact-form-status");
    if (contactForm && contactStatus) {
      const emailField = contactForm.querySelector("#contact-email");
      const messageField = contactForm.querySelector("#contact-message");
      const submitButton = contactForm.querySelector("button[type='submit']");
      const clearFieldError = (field) => field.removeAttribute("aria-invalid");

      [emailField, messageField].forEach((field) => {
        field.addEventListener("input", () => {
          clearFieldError(field);
          if (contactStatus.classList.contains("is-error")) {
            contactStatus.textContent = "";
            contactStatus.className = "form-status";
            contactStatus.setAttribute("role", "status");
          }
        });
      });

      contactForm.addEventListener("submit", async (event) => {
        event.preventDefault();
        contactStatus.textContent = "";
        contactStatus.className = "form-status";
        contactStatus.setAttribute("role", "status");
        clearFieldError(emailField);
        clearFieldError(messageField);

        const emailInvalid = !emailField.value.trim() || !emailField.validity.valid;
        const messageInvalid = !messageField.value.trim();
        if (emailInvalid || messageInvalid) {
          if (emailInvalid) emailField.setAttribute("aria-invalid", "true");
          if (messageInvalid) messageField.setAttribute("aria-invalid", "true");
          contactStatus.className = "form-status is-error";
          contactStatus.setAttribute("role", "alert");
          contactStatus.textContent = "Vyplňte prosím platný e-mail a váš dotaz.";
          (emailInvalid ? emailField : messageField).focus();
          return;
        }

        if (!CONTACT_ENDPOINT) {
          contactStatus.className = "form-status is-error";
          contactStatus.setAttribute("role", "alert");
          contactStatus.textContent = "Formulář zatím není aktivní. Napište nám prosím přímo na info@statekcholupice.cz.";
          return;
        }

        submitButton.disabled = true;
        submitButton.textContent = "Odesílám…";
        try {
          const response = await fetch(CONTACT_ENDPOINT, {
            method: "POST",
            headers: { "Content-Type": "application/json", Accept: "application/json" },
            body: JSON.stringify({
              name: contactForm.elements.name.value.trim(),
              email: emailField.value.trim(),
              message: messageField.value.trim(),
              nonce: window.StatekCholupice?.contactNonce || "",
              company: contactForm.elements.company?.value || ""
            })
          });
          const data = await response.json().catch(() => ({}));
          if (!response.ok) throw new Error(data.message || `HTTP ${response.status}`);
          contactForm.reset();
          contactStatus.className = "form-status is-success";
          contactStatus.setAttribute("role", "status");
          contactStatus.textContent = data.message || "Děkujeme. Váš dotaz jsme přijali.";
        } catch (error) {
          contactStatus.className = "form-status is-error";
          contactStatus.setAttribute("role", "alert");
          const email = window.StatekCholupice?.contactEmail || "info@statekcholupice.cz";
          contactStatus.textContent = error.message || `Dotaz se nepodařilo odeslat. Zkuste to prosím znovu nebo napište na ${email}.`;
        } finally {
          submitButton.disabled = false;
          submitButton.textContent = "Odeslat dotaz";
        }
      });
    }
  
