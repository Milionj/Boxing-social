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
})();
