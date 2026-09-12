(() => {
  'use strict';
  document.querySelectorAll('[data-nice-picker]').forEach((picker) => {
    const input = picker.querySelector('[data-nice-media-id]');
    const image = picker.querySelector('[data-nice-media-preview]');
    const empty = picker.querySelector('[data-nice-media-empty]');
    const select = picker.querySelector('[data-nice-media-select]');
    const remove = picker.querySelector('[data-nice-media-remove]');
    const status = picker.querySelector('[data-nice-media-status]');
    let frame;
    const setImage = (id, url) => {
      input.value = id || 0;
      if (url) image.src = url;
      else image.removeAttribute('src');
      image.hidden = !id;
      empty.hidden = !!id;
      remove.hidden = !id;
      select.textContent = id ? 'Replace' : 'Select';
      status.textContent = id ? 'Image selected. Save the page to apply.' : 'Image removed. Save the page to apply.';
      input.dispatchEvent(new Event('change', { bubbles: true }));
    };
    select.addEventListener('click', () => {
      if (!window.wp?.media) return;
      if (!frame) {
        frame = wp.media({ title: 'Choose an image', button: { text: 'Use image' }, library: { type: 'image' }, multiple: false });
        frame.on('open', () => {
          const selection = frame.state().get('selection');
          selection.reset();
          if (Number(input.value)) selection.add(wp.media.attachment(Number(input.value)));
        });
        frame.on('select', () => {
          const attachment = frame.state().get('selection').first()?.toJSON();
          if (attachment) setImage(attachment.id, attachment.sizes?.medium?.url || attachment.url);
        });
      }
      frame.open();
    });
    remove.addEventListener('click', () => {
      setImage(0, '');
      select.focus();
    });
  });
  document.querySelectorAll('.nice-platform-slot').forEach((slot) => {
    const fields = [...slot.querySelectorAll('input[type="number"]')];
    const updateFocal = () => {
      const [x, y] = fields.map((field) => Math.max(0, Math.min(100, Number(field.value) || 0)));
      slot.querySelectorAll('[data-nice-media-preview]').forEach((image) => { image.style.objectPosition = `${x}% ${y}%`; });
    };
    fields.forEach((field) => field.addEventListener('input', updateFocal));
    updateFocal();
  });
})();
