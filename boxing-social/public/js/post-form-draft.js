document.querySelectorAll('[data-post-form-draft]').forEach((form) => {
  const draftKey = form.getAttribute('data-post-form-draft');
  if (!draftKey) {
    return;
  }

  const storageKey = `boxing-social:${draftKey}`;
  const restoreDraft = form.getAttribute('data-restore-draft') === '1';
  const fields = Array.from(form.elements).filter((field) => {
    if (!(field instanceof HTMLElement) || !('name' in field)) {
      return false;
    }

    const name = field.name;
    if (!name || field instanceof HTMLButtonElement) {
      return false;
    }

    return !(field instanceof HTMLInputElement && field.type === 'file');
  });

  const saveDraft = () => {
    const payload = {};

    fields.forEach((field) => {
      if (!(field instanceof HTMLInputElement || field instanceof HTMLTextAreaElement || field instanceof HTMLSelectElement)) {
        return;
      }

      if (field instanceof HTMLInputElement && field.type === 'checkbox') {
        payload[field.name] = field.checked;
        return;
      }

      payload[field.name] = field.value;
    });

    sessionStorage.setItem(storageKey, JSON.stringify(payload));
  };

  const clearDraft = () => {
    sessionStorage.removeItem(storageKey);
  };

  const restore = () => {
    const raw = sessionStorage.getItem(storageKey);
    if (!raw) {
      return;
    }

    try {
      const payload = JSON.parse(raw);
      fields.forEach((field) => {
        if (!(field instanceof HTMLInputElement || field instanceof HTMLTextAreaElement || field instanceof HTMLSelectElement)) {
          return;
        }

        if (!(field.name in payload)) {
          return;
        }

        if (field instanceof HTMLInputElement && field.type === 'checkbox') {
          field.checked = Boolean(payload[field.name]);
          field.dispatchEvent(new Event('change', { bubbles: true }));
          return;
        }

        field.value = String(payload[field.name] ?? '');
        field.dispatchEvent(new Event('change', { bubbles: true }));
        field.dispatchEvent(new Event('input', { bubbles: true }));
      });
    } catch (_error) {
      clearDraft();
    }
  };

  if (restoreDraft) {
    restore();
  } else {
    clearDraft();
  }

  fields.forEach((field) => {
    field.addEventListener('input', saveDraft);
    field.addEventListener('change', saveDraft);
  });
});
