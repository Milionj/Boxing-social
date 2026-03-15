document.querySelectorAll('[data-post-media-widget]').forEach((widget) => {
  const input = widget.querySelector('[data-post-media-input]');
  const preview = widget.querySelector('[data-post-media-preview]');
  const previewFrame = widget.querySelector('[data-post-media-preview-frame]');
  const clearButton = widget.querySelector('[data-post-media-clear]');
  const removeCheckbox = widget.querySelector('[data-post-remove-media]');
  const currentMedia = widget.querySelector('[data-post-current-media]');

  if (!input || !preview || !previewFrame) {
    return;
  }

  let objectUrl = null;

  const revokePreviewUrl = () => {
    if (objectUrl !== null) {
      URL.revokeObjectURL(objectUrl);
      objectUrl = null;
    }
  };

  const syncCurrentMediaState = () => {
    if (!currentMedia || !removeCheckbox) {
      return;
    }

    currentMedia.classList.toggle('is-removing', removeCheckbox.checked);
  };

  const renderPreview = () => {
    const file = input.files && input.files[0] ? input.files[0] : null;

    revokePreviewUrl();
    previewFrame.innerHTML = '';

    if (removeCheckbox) {
      removeCheckbox.disabled = file !== null;
      if (file !== null) {
        removeCheckbox.checked = false;
      }
      syncCurrentMediaState();
    }

    if (file === null) {
      preview.classList.add('is-hidden');
      return;
    }

    objectUrl = URL.createObjectURL(file);

    const mediaElement = document.createElement(file.type.startsWith('video/') ? 'video' : 'img');
    mediaElement.className = 'post-media-preview__media';

    if (mediaElement instanceof HTMLVideoElement) {
      mediaElement.controls = true;
      mediaElement.muted = true;
      mediaElement.playsInline = true;
      mediaElement.preload = 'metadata';
      mediaElement.src = objectUrl;
    } else {
      mediaElement.src = objectUrl;
      mediaElement.alt = '';
    }

    previewFrame.appendChild(mediaElement);
    preview.classList.remove('is-hidden');
  };

  const clearSelectedFile = () => {
    input.value = '';
    renderPreview();
  };

  input.addEventListener('change', renderPreview);

  if (clearButton) {
    clearButton.addEventListener('click', clearSelectedFile);
  }

  if (removeCheckbox) {
    removeCheckbox.addEventListener('change', syncCurrentMediaState);
    syncCurrentMediaState();
  }

  window.addEventListener('beforeunload', revokePreviewUrl, { once: true });
});
