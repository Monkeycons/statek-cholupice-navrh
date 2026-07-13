(function () {
  const replaceTemplateIndex = (markup, index) =>
    String(markup).replace(/__INDEX__/g, String(index));

  const initialRepeaterIndex = (indexes) => {
    const numericIndexes = Array.from(indexes, (value) =>
      Number.parseInt(String(value), 10),
    ).filter(Number.isFinite);
    return numericIndexes.length ? Math.max(...numericIndexes) + 1 : 0;
  };

  const nextAvailableOrder = (values, maximum) => {
    const max = Number.parseInt(String(maximum || 0), 10);
    const orders = Array.from(values, (value) =>
      Number.parseInt(String(value), 10),
    ).filter((value) => value >= 1 && (!max || value <= max));

    if (!orders.length) return 1;
    const highest = Math.max(...orders);
    if (!max || highest < max) return highest + 1;

    const used = new Set(orders);
    for (let order = 1; order <= max; order += 1) {
      if (!used.has(order)) return order;
    }
    return max;
  };

  if (typeof module !== "undefined" && module.exports) {
    module.exports = {
      initialRepeaterIndex,
      nextAvailableOrder,
      replaceTemplateIndex,
    };
  }

  if (typeof document === "undefined") return;

  const on = (eventName, selector, callback) => {
    document.addEventListener(eventName, (event) => {
      const target = event.target.closest(selector);
      if (!target) return;
      callback(event, target);
    });
  };

  const directChild = (element, selector) =>
    Array.from(element.children).find((child) => child.matches(selector)) || null;

  const directRows = (items) =>
    items
      ? Array.from(items.children).filter((child) =>
          child.matches(".statek-repeater-row"),
        )
      : [];

  const focusableIn = (element) =>
    element?.querySelector(
      'input:not([type="hidden"]):not([disabled]), textarea:not([disabled]), select:not([disabled]), button:not([disabled])',
    );

  let repeaterIndex = initialRepeaterIndex(
    Array.from(document.querySelectorAll("[data-repeater-index]"), (row) =>
      row.getAttribute("data-repeater-index"),
    ),
  );

  on("click", ".statek-media-select", (event, button) => {
    event.preventDefault();
    const field = button.closest(".statek-media-field");
    if (!field || !window.wp || !window.wp.media) return;

    const frame = window.wp.media({
      title: "Vybrat obrázek",
      button: { text: "Použít obrázek" },
      library: { type: "image" },
      multiple: false,
    });

    frame.on("select", () => {
      const attachment = frame.state().get("selection").first().toJSON();
      const idInput = field.querySelector(".statek-media-id");
      const preview = field.querySelector(".statek-media-preview");
      const altInput = field.querySelector('input[name$="[alt]"]');
      const status = field.querySelector(".statek-media-status");
      const imageUrl =
        attachment.sizes?.thumbnail?.url ||
        attachment.sizes?.medium?.url ||
        attachment.url;

      if (attachment.type !== "image" || !attachment.id || !imageUrl) {
        if (status) status.textContent = "Vyberte platný obrázek z knihovny médií.";
        return;
      }

      if (idInput) idInput.value = attachment.id;
      if (altInput) altInput.value = attachment.alt || "";
      if (preview) {
        preview.replaceChildren();
        const image = document.createElement("img");
        image.src = imageUrl;
        image.alt = "";
        preview.appendChild(image);
      }
      if (status) {
        status.textContent = altInput
          ? "Obrázek byl vybrán. Zkontrolujte jeho alt text."
          : "Obrázek byl vybrán.";
      }
    });

    frame.open();
  });

  on("click", ".statek-media-clear", (event, button) => {
    event.preventDefault();
    const field = button.closest(".statek-media-field");
    if (!field) return;
    const idInput = field.querySelector(".statek-media-id");
    const preview = field.querySelector(".statek-media-preview");
    const altInput = field.querySelector('input[name$="[alt]"]');
    const status = field.querySelector(".statek-media-status");
    if (idInput) idInput.value = "";
    if (altInput) altInput.value = "";
    if (preview) {
      const fallback = document.createElement("em");
      fallback.textContent =
        field.dataset.fallbackText ||
        "Je použit schválený výchozí obrázek šablony.";
      preview.replaceChildren(fallback);
    }
    if (status) status.textContent = "Vlastní obrázek byl odebrán.";
  });

  on("click", ".statek-repeater-add", (event, button) => {
    event.preventDefault();
    const repeater = button.closest(".statek-repeater");
    if (!repeater) return;
    const items = directChild(repeater, ".statek-repeater-items");
    const template = directChild(repeater, "template");
    if (!items || !template) return;

    const rows = directRows(items);
    const max = Number.parseInt(repeater.dataset.max || "0", 10);
    if (max && rows.length >= max) return;

    const order = nextAvailableOrder(
      rows.map((row) => row.querySelector("[data-order-field]")?.value || ""),
      max,
    );
    const wrapper = document.createElement("div");
    wrapper.innerHTML = replaceTemplateIndex(template.innerHTML, repeaterIndex);
    repeaterIndex += 1;

    const newRows = Array.from(wrapper.children);
    newRows.forEach((row) => {
      const orderInput = row.querySelector("[data-order-field]");
      if (orderInput) orderInput.value = String(order);
      items.appendChild(row);
    });

    focusableIn(newRows[0])?.focus();
  });

  on("click", ".statek-repeater-remove", (event, button) => {
    event.preventDefault();
    const row = button.closest(".statek-repeater-row");
    const repeater = button.closest(".statek-repeater");
    if (!row || !repeater) return;

    const hasContent = Array.from(
      row.querySelectorAll('input:not([type="hidden"]), textarea, select'),
    ).some((field) => !field.matches("[data-order-field]") && field.value.trim());
    if (hasContent && !window.confirm("Opravdu chcete vyplněnou položku odebrat?")) {
      return;
    }

    const sibling = row.previousElementSibling || row.nextElementSibling;
    row.remove();
    const focusTarget = focusableIn(sibling) || repeater.querySelector(".statek-repeater-add");
    focusTarget?.focus();
  });
})();
