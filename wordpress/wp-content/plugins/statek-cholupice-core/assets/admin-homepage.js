(function () {
  const on = (eventName, selector, callback) => {
    document.addEventListener(eventName, (event) => {
      const target = event.target.closest(selector);
      if (!target) return;
      callback(event, target);
    });
  };

  on("click", ".statek-media-select", (event, button) => {
    event.preventDefault();
    const field = button.closest(".statek-media-field");
    if (!field || !window.wp || !window.wp.media) return;

    const frame = window.wp.media({
      title: "Vybrat obrázek",
      button: { text: "Použít obrázek" },
      multiple: false,
    });

    frame.on("select", () => {
      const attachment = frame.state().get("selection").first().toJSON();
      const idInput = field.querySelector(".statek-media-id");
      const preview = field.querySelector(".statek-media-preview");
      const altInput = field.querySelector('input[name$="[alt]"]');
      const imageUrl =
        attachment.sizes?.thumbnail?.url ||
        attachment.sizes?.medium?.url ||
        attachment.url;

      if (idInput) idInput.value = attachment.id || "";
      if (altInput && !altInput.value) altInput.value = attachment.alt || "";
      if (preview && imageUrl) {
        preview.innerHTML = "";
        const image = document.createElement("img");
        image.src = imageUrl;
        image.alt = "";
        preview.appendChild(image);
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
    if (idInput) idInput.value = "";
    if (preview) preview.innerHTML = "<em>Je použit schválený výchozí obrázek šablony.</em>";
  });

  on("click", ".statek-repeater-add", (event, button) => {
    event.preventDefault();
    const repeater = button.closest(".statek-repeater");
    if (!repeater) return;
    const items = repeater.querySelector(".statek-repeater-items");
    const template = repeater.querySelector("template");
    if (!items || !template) return;

    const max = Number.parseInt(repeater.dataset.max || "0", 10);
    if (max && items.querySelectorAll(":scope > .statek-repeater-row").length >= max) {
      return;
    }

    const index = Date.now().toString();
    const wrapper = document.createElement("div");
    wrapper.innerHTML = template.innerHTML.replaceAll("__INDEX__", index);
    Array.from(wrapper.children).forEach((child) => items.appendChild(child));
  });

  on("click", ".statek-repeater-remove", (event, button) => {
    event.preventDefault();
    const row = button.closest(".statek-repeater-row");
    if (row) row.remove();
  });
})();
