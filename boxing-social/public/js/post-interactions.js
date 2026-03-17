(() => {
  const body = document.body;
  if (!body) {
    return;
  }

  const defaultErrorMessage =
    body.dataset.postInteractionError || 'Impossible de mettre à jour l’interaction pour le moment.';
  const deleteCommentLabel = body.dataset.commentDeleteLabel || 'Supprimer commentaire';
  const socialI18n = document.querySelector('[data-social-i18n]');
  const trainingInterestAction = socialI18n?.dataset.trainingInterestAction || 'Cliquer sur le poing pour manifester votre intérêt';
  const trainingInterestSent = socialI18n?.dataset.trainingInterestSent || 'Intérêt déjà envoyé';

  const requestHeaders = {
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  };
  let previewState = null;

  document.addEventListener('submit', (event) => {
    const target = event.target;
    if (!(target instanceof HTMLFormElement)) {
      return;
    }

    if (target.matches('[data-like-form]')) {
      event.preventDefault();
      void handleLikeSubmit(target);
      return;
    }

    if (target.matches('[data-comment-form]')) {
      event.preventDefault();
      void handleCommentSubmit(target);
      return;
    }

    if (target.matches('[data-comment-delete-form]')) {
      event.preventDefault();
      void handleCommentDelete(target);
      return;
    }

    if (target.matches('[data-interest-form]')) {
      event.preventDefault();
      void handleInterestSubmit(target);
    }
  });

  document.addEventListener('click', (event) => {
    const target = event.target;
    if (!(target instanceof Element)) {
      return;
    }

    const previewToggle = target.closest('[data-post-preview-toggle]');
    if (previewToggle instanceof HTMLButtonElement) {
      event.preventDefault();
      togglePostPreview(previewToggle);
      return;
    }

    if (target.matches('[data-post-preview-close]')) {
      event.preventDefault();
      closePostPreview();
      return;
    }

  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      closePostPreview();
    }
  });

  async function handleLikeSubmit(form) {
    const scope = findInteractionScope(form);
    const formData = new FormData(form);
    clearFeedback(scope);
    setFormPending(form, true);

    try {
      const payload = await requestJson(form, formData);
      if (!payload) {
        return;
      }

      const countElement = scope?.querySelector('[data-like-count]');
      if (countElement) {
        setCountText(countElement, payload.likesCount);
      }

      const button = form.querySelector('[data-like-button]');
      if (button instanceof HTMLButtonElement) {
        button.textContent = payload.liked
          ? button.dataset.labelActive || button.textContent
          : button.dataset.labelDefault || button.textContent;
      }
    } catch (error) {
      showFeedback(scope, resolveErrorMessage(error));
    } finally {
      setFormPending(form, false);
    }
  }

  async function handleCommentSubmit(form) {
    const scope = findInteractionScope(form);
    const textarea = form.querySelector('textarea[name="content"]');
    const formData = new FormData(form);
    clearFeedback(scope);
    setFormPending(form, true);

    try {
      const payload = await requestJson(form, formData);
      if (!payload) {
        return;
      }

      const list = scope?.querySelector('[data-comment-list]');
      const redirectInput = form.querySelector('input[name="redirect_to"]');
      const redirectTo = redirectInput instanceof HTMLInputElement && redirectInput.value !== ''
        ? redirectInput.value
        : window.location.pathname + window.location.search;

      if (list && payload.comment) {
        const emptyState = scope?.querySelector('[data-comment-empty]');
        const commentElement = createCommentElement(payload.comment, redirectTo);
        list.prepend(commentElement);

        if (emptyState instanceof HTMLElement) {
          emptyState.hidden = true;
        }
      }

      updateCommentCount(scope, payload.commentsCount);

      if (textarea instanceof HTMLTextAreaElement) {
        textarea.value = '';
      }
    } catch (error) {
      showFeedback(scope, resolveErrorMessage(error));
    } finally {
      setFormPending(form, false);
    }
  }

  async function handleCommentDelete(form) {
    const scope = findInteractionScope(form);
    const commentElement = form.closest('[data-comment-id]');
    const formData = new FormData(form);
    clearFeedback(scope);
    setFormPending(form, true);

    try {
      const payload = await requestJson(form, formData);
      if (!payload) {
        return;
      }

      if (commentElement instanceof HTMLElement) {
        commentElement.remove();
      }

      updateCommentCount(scope, payload.commentsCount);

      const emptyState = scope?.querySelector('[data-comment-empty]');
      if (emptyState instanceof HTMLElement) {
        emptyState.hidden = payload.commentsCount !== 0;
      }
    } catch (error) {
      showFeedback(scope, resolveErrorMessage(error));
    } finally {
      setFormPending(form, false);
    }
  }

  async function handleInterestSubmit(form) {
    const scope = findInteractionScope(form);
    const formData = new FormData(form);
    clearFeedback(scope);
    setFormPending(form, true);

    try {
      const payload = await requestJson(form, formData);
      if (!payload) {
        return;
      }

      const countElement = form.querySelector('[data-interest-count]');
      if (countElement) {
        setCountText(countElement, payload.interestCount);
      }

      const hint = form.querySelector('[data-interest-hint]');
      if (hint instanceof HTMLElement) {
        hint.textContent = payload.interested ? trainingInterestSent : trainingInterestAction;
      }

      const button = form.querySelector('[data-interest-button]');
      if (button instanceof HTMLButtonElement) {
        button.classList.toggle('is-active', Boolean(payload.interested));
        button.disabled = Boolean(payload.interested);
      }
    } catch (error) {
      showFeedback(scope, resolveErrorMessage(error));
    } finally {
      setFormPending(form, false, {
        keepButtonsDisabled: form.querySelector('[data-interest-button]')?.disabled === true,
      });
    }
  }

  async function requestJson(form, body) {
    const response = await fetch(form.action, {
      method: (form.method || 'POST').toUpperCase(),
      body: body || new FormData(form),
      headers: requestHeaders,
    });

    const contentType = response.headers.get('Content-Type') || '';
    if (!contentType.includes('application/json')) {
      if (response.redirected && response.url) {
        window.location.assign(response.url);
        return null;
      }

      throw new Error(defaultErrorMessage);
    }

    const payload = await response.json();
    if (!response.ok || !payload?.ok) {
      throw new Error(payload?.message || defaultErrorMessage);
    }

    return payload;
  }

  function createCommentElement(comment, redirectTo) {
    const article = document.createElement('article');
    article.className = 'comment';
    article.dataset.commentId = String(comment.id);

    const meta = document.createElement('div');
    meta.className = 'comment__meta';

    const authorLine = document.createElement('div');
    authorLine.className = 'comment__authorline';

    const strong = document.createElement('strong');
    const authorLink = document.createElement('a');
    authorLink.href = comment.authorUrl;
    authorLink.textContent = comment.username;
    strong.appendChild(authorLink);

    const createdAt = document.createElement('small');
    createdAt.textContent = comment.createdAt;

    authorLine.appendChild(strong);
    authorLine.appendChild(createdAt);
    meta.appendChild(authorLine);

    if (comment.canDelete) {
      const deleteForm = document.createElement('form');
      deleteForm.className = 'comment__delete';
      deleteForm.method = 'post';
      deleteForm.action = '/comments/delete';
      deleteForm.setAttribute('data-comment-delete-form', '');

      const commentIdInput = document.createElement('input');
      commentIdInput.type = 'hidden';
      commentIdInput.name = 'comment_id';
      commentIdInput.value = String(comment.id);

      const redirectInput = document.createElement('input');
      redirectInput.type = 'hidden';
      redirectInput.name = 'redirect_to';
      redirectInput.value = redirectTo;

      const deleteButton = document.createElement('button');
      deleteButton.type = 'submit';
      deleteButton.textContent = deleteCommentLabel;

      deleteForm.appendChild(commentIdInput);
      deleteForm.appendChild(redirectInput);
      deleteForm.appendChild(deleteButton);
      meta.appendChild(deleteForm);
    }

    const content = document.createElement('p');
    content.className = 'comment__text';
    content.textContent = comment.content;

    article.appendChild(meta);
    article.appendChild(content);

    return article;
  }

  function findInteractionScope(form) {
    return form.closest('[data-interaction-scope]');
  }

  function updateCommentCount(scope, count) {
    const countElement = scope?.querySelector('[data-comment-count]');
    if (countElement) {
      setCountText(countElement, count);
    }
  }

  function setCountText(element, count) {
    const format = element.dataset.countFormat || '';
    element.textContent = format === 'parentheses' ? `(${count})` : String(count);
  }

  function showFeedback(scope, message) {
    const feedback = scope?.querySelector('[data-interaction-feedback]');
    if (!(feedback instanceof HTMLElement)) {
      return;
    }

    feedback.textContent = message;
    feedback.hidden = false;
  }

  function clearFeedback(scope) {
    const feedback = scope?.querySelector('[data-interaction-feedback]');
    if (!(feedback instanceof HTMLElement)) {
      return;
    }

    feedback.textContent = '';
    feedback.hidden = true;
  }

  function setFormPending(form, pending, options = {}) {
    const elements = form.querySelectorAll('button, textarea');
    elements.forEach((element) => {
      if (!(element instanceof HTMLButtonElement || element instanceof HTMLTextAreaElement)) {
        return;
      }

      if (pending) {
        element.dataset.wasDisabled = element.disabled ? '1' : '0';
        element.disabled = true;
        return;
      }

      if (element instanceof HTMLButtonElement && options.keepButtonsDisabled) {
        delete element.dataset.wasDisabled;
        return;
      }

      element.disabled = element.dataset.wasDisabled === '1';
      delete element.dataset.wasDisabled;
    });
  }

  function resolveErrorMessage(error) {
    if (error instanceof Error && error.message.trim() !== '') {
      return error.message;
    }

    return defaultErrorMessage;
  }

  function togglePostPreview(button) {
    const card = button.closest('[data-post-card]');
    if (!(card instanceof HTMLElement)) {
      return;
    }

    if (previewState?.card === card) {
      closePostPreview();
      return;
    }

    openPostPreview(card, button);
  }

  function openPostPreview(card, trigger) {
    closePostPreview({ restoreFocus: false });

    const modal = ensurePreviewModal();
    const placeholder = document.createComment('post-preview-placeholder');
    card.after(placeholder);

    modal.body.appendChild(card);
    modal.container.hidden = false;
    body.classList.add('has-post-preview');
    card.classList.add('post--preview-open', 'post--in-preview');
    trigger.setAttribute('aria-expanded', 'true');

    previewState = {
      card,
      trigger,
      placeholder,
    };
  }

  function closePostPreview(options = {}) {
    if (!previewState) {
      return;
    }

    const { restoreFocus = true } = options;
    const modal = ensurePreviewModal();
    const { card, trigger, placeholder } = previewState;

    card.classList.remove('post--preview-open', 'post--in-preview');
    trigger.setAttribute('aria-expanded', 'false');

    if (placeholder.parentNode) {
      placeholder.parentNode.insertBefore(card, placeholder);
      placeholder.remove();
    }

    modal.container.hidden = true;
    body.classList.remove('has-post-preview');

    if (restoreFocus) {
      trigger.focus();
    }

    previewState = null;
  }

  function ensurePreviewModal() {
    if (previewState?.modal) {
      return previewState.modal;
    }

    let container = document.querySelector('[data-post-preview-root]');
    if (!(container instanceof HTMLDivElement)) {
      container = document.createElement('div');
      container.className = 'post-preview-modal';
      container.hidden = true;
      container.setAttribute('data-post-preview-root', '');
      container.setAttribute('data-post-preview-modal', '');

      const dialog = document.createElement('div');
      dialog.className = 'post-preview-modal__dialog';
      dialog.setAttribute('role', 'dialog');
      dialog.setAttribute('aria-modal', 'true');
      dialog.setAttribute('aria-label', 'Aperçu de la publication');

      const chrome = document.createElement('div');
      chrome.className = 'post-preview-modal__chrome';

      const closeButton = document.createElement('button');
      closeButton.type = 'button';
      closeButton.className = 'post-preview-modal__close';
      closeButton.setAttribute('data-post-preview-close', '');
      closeButton.setAttribute('aria-label', 'Fermer');
      closeButton.textContent = '×';

      const content = document.createElement('div');
      content.className = 'post-preview-modal__content';

      chrome.appendChild(closeButton);
      dialog.appendChild(chrome);
      dialog.appendChild(content);
      container.appendChild(dialog);
      document.body.appendChild(container);

      container.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof Element)) {
          return;
        }

        if (target.closest('.post--in-preview') || target.closest('[data-post-preview-close]')) {
          return;
        }

        closePostPreview();
      });
    }

    const modal = {
      container,
      body: container.querySelector('.post-preview-modal__content'),
    };

    if (!(modal.body instanceof HTMLDivElement)) {
      throw new Error('Preview modal container is invalid.');
    }

    if (previewState) {
      previewState.modal = modal;
    } else {
      previewState = { modal };
    }

    return modal;
  }
})();
