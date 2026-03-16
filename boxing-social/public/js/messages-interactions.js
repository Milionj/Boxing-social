(() => {
  const page = document.querySelector('[data-messages-page]');
  if (!(page instanceof HTMLElement)) {
    return;
  }

  const socialI18n = document.querySelector('[data-social-i18n]');
  const texts = {
    errorGeneric: socialI18n?.dataset.errorGeneric || 'Impossible d’envoyer le message pour le moment.',
    sent: page.dataset.messageSent || 'Message envoyé.',
    profileLink: page.dataset.messageProfileLink || 'Voir le profil',
    you: page.dataset.messageYou || 'Moi',
    other: page.dataset.messageOther || 'La personne',
    emptyThread: page.dataset.messageEmptyThread || 'Aucun message dans cette conversation.',
  };

  const requestHeaders = {
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  };

  document.addEventListener('submit', (event) => {
    const target = event.target;
    if (!(target instanceof HTMLFormElement) || !target.matches('[data-message-send-form]')) {
      return;
    }

    event.preventDefault();
    void handleMessageSend(target);
  });

  async function handleMessageSend(form) {
    const formData = new FormData(form);
    clearFeedback(form);
    setFormPending(form, true);

    try {
      const payload = await requestJson(form, formData);
      if (!payload?.conversation || !payload?.messageItem) {
        throw new Error(texts.errorGeneric);
      }

      upsertConversation(payload.conversation);
      activateConversation(payload.conversation);

      if (form.dataset.messageMode === 'new' && Array.isArray(payload.thread)) {
        renderThread(payload.thread);
      } else {
        appendMessage(payload.messageItem);
      }

      const textarea = form.querySelector('textarea[name="content"]');
      if (textarea instanceof HTMLTextAreaElement) {
        textarea.value = '';
      }

      if (form.dataset.messageMode === 'new') {
        const usernameInput = form.querySelector('input[name="receiver_username"]');
        if (usernameInput instanceof HTMLInputElement) {
          usernameInput.value = '';
        }
      }
    } catch (error) {
      showFeedback(form, resolveErrorMessage(error));
    } finally {
      setFormPending(form, false);
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

      throw new Error(texts.errorGeneric);
    }

    const payload = await response.json();
    if (!response.ok || !payload?.ok) {
      throw new Error(payload?.message || texts.errorGeneric);
    }

    return payload;
  }

  function activateConversation(conversation) {
    const thread = page.querySelector('[data-messages-thread]');
    const idleState = thread?.querySelector('[data-messages-idle-state]');
    const activeState = thread?.querySelector('[data-messages-active-state]');
    const target = thread?.querySelector('[data-messages-thread-target]');
    const replyForm = thread?.querySelector('[data-message-send-form][data-message-mode="reply"]');
    const replyReceiver = replyForm?.querySelector('input[name="receiver_id"]');
    const emptyState = thread?.querySelector('[data-messages-empty-thread]');
    const list = thread?.querySelector('[data-message-list]');

    if (!(thread instanceof HTMLElement) || !(activeState instanceof HTMLElement)) {
      return;
    }

    thread.dataset.selectedUserId = String(conversation.userId);
    thread.dataset.selectedUsername = conversation.username;

    if (idleState instanceof HTMLElement) {
      idleState.hidden = true;
    }

    activeState.hidden = false;

    if (target instanceof HTMLElement) {
      target.hidden = false;
      target.textContent = conversation.username;
    }

    if (replyReceiver instanceof HTMLInputElement) {
      replyReceiver.value = String(conversation.userId);
    }

    if (
      emptyState instanceof HTMLElement
      && (!(list instanceof HTMLElement) || list.children.length === 0)
    ) {
      emptyState.hidden = false;
      emptyState.textContent = texts.emptyThread;
    }

    if (list instanceof HTMLElement) {
      list.hidden = list.children.length === 0;
    }

    if (window.location.pathname + window.location.search !== conversation.threadUrl) {
      window.history.pushState({}, '', conversation.threadUrl);
    }
  }

  function appendMessage(message) {
    const thread = page.querySelector('[data-messages-thread]');
    const list = thread?.querySelector('[data-message-list]');
    const emptyState = thread?.querySelector('[data-messages-empty-thread]');

    if (!(list instanceof HTMLElement)) {
      return;
    }

    const existing = list.querySelector(`[data-message-id="${cssEscape(String(message.id))}"]`);
    if (existing instanceof HTMLElement) {
      return;
    }

    list.hidden = false;
    if (emptyState instanceof HTMLElement) {
      emptyState.hidden = true;
    }

    list.append(createMessageBubble(message));
    list.lastElementChild?.scrollIntoView({ behavior: 'smooth', block: 'end' });
  }

  function renderThread(messages) {
    const thread = page.querySelector('[data-messages-thread]');
    const list = thread?.querySelector('[data-message-list]');
    const emptyState = thread?.querySelector('[data-messages-empty-thread]');

    if (!(list instanceof HTMLElement)) {
      return;
    }

    list.replaceChildren();

    if (!Array.isArray(messages) || messages.length === 0) {
      list.hidden = true;
      if (emptyState instanceof HTMLElement) {
        emptyState.hidden = false;
        emptyState.textContent = texts.emptyThread;
      }
      return;
    }

    messages.forEach((message) => {
      list.append(createMessageBubble(message));
    });

    list.hidden = false;
    if (emptyState instanceof HTMLElement) {
      emptyState.hidden = true;
    }
    list.lastElementChild?.scrollIntoView({ behavior: 'smooth', block: 'end' });
  }

  function upsertConversation(conversation) {
    const list = page.querySelector('[data-messages-conversation-list]');
    const emptyState = page.querySelector('[data-messages-conversations-empty]');

    if (!(list instanceof HTMLElement)) {
      return;
    }

    let item = list.querySelector(`[data-user-id="${cssEscape(String(conversation.userId))}"]`);
    if (!(item instanceof HTMLElement)) {
      item = createConversationItem(conversation);
    }

    const dateNode = item.querySelector('[data-conversation-date]');
    if (dateNode instanceof HTMLElement) {
      dateNode.textContent = conversation.lastMessageAt;
    }

    list.hidden = false;
    list.prepend(item);

    if (emptyState instanceof HTMLElement) {
      emptyState.hidden = true;
    }

    list.querySelectorAll('[data-conversation-item]').forEach((node) => {
      node.classList.toggle('is-active', node === item);
    });
  }

  function createConversationItem(conversation) {
    const article = document.createElement('article');
    article.className = 'conversation-item is-active';
    article.dataset.conversationItem = '';
    article.dataset.userId = String(conversation.userId);

    const threadLink = document.createElement('a');
    threadLink.className = 'conversation-item__main';
    threadLink.href = conversation.threadUrl;

    const avatar = document.createElement('span');
    avatar.className = 'conversation-item__avatar';
    avatar.textContent = String(conversation.username).charAt(0).toUpperCase();

    const copy = document.createElement('span');
    copy.className = 'conversation-item__copy';

    const username = document.createElement('strong');
    username.textContent = conversation.username;

    const date = document.createElement('small');
    date.dataset.conversationDate = '';
    date.textContent = conversation.lastMessageAt;

    copy.appendChild(username);
    copy.appendChild(date);
    threadLink.appendChild(avatar);
    threadLink.appendChild(copy);

    const profileLink = document.createElement('a');
    profileLink.className = 'conversation-item__profile';
    profileLink.href = conversation.profileUrl;
    profileLink.textContent = texts.profileLink;

    article.appendChild(threadLink);
    article.appendChild(profileLink);

    return article;
  }

  function createMessageBubble(message) {
    const article = document.createElement('article');
    article.className = `message-bubble${message.isMine ? ' is-mine' : ''}`;
    article.dataset.messageItem = '';
    article.dataset.messageId = String(message.id);

    const head = document.createElement('div');
    head.className = 'message-bubble__head';

    const author = document.createElement('strong');
    author.textContent = message.isMine ? texts.you : texts.other;

    const createdAt = document.createElement('small');
    createdAt.textContent = message.createdAt;

    const content = document.createElement('p');
    content.textContent = message.content;

    head.appendChild(author);
    head.appendChild(createdAt);
    article.appendChild(head);
    article.appendChild(content);

    return article;
  }

  function showFeedback(form, message) {
    const feedback = form.querySelector('[data-interaction-feedback]');
    if (!(feedback instanceof HTMLElement)) {
      return;
    }

    feedback.textContent = message;
    feedback.hidden = false;
    feedback.classList.remove('is-success');
    feedback.classList.add('is-error');
  }

  function clearFeedback(form) {
    const feedback = form.querySelector('[data-interaction-feedback]');
    if (!(feedback instanceof HTMLElement)) {
      return;
    }

    feedback.textContent = '';
    feedback.hidden = true;
    feedback.classList.remove('is-error', 'is-success');
  }

  function setFormPending(form, pending) {
    form.querySelectorAll('button, input, textarea').forEach((element) => {
      if (
        !(element instanceof HTMLButtonElement)
        && !(element instanceof HTMLInputElement)
        && !(element instanceof HTMLTextAreaElement)
      ) {
        return;
      }

      if (pending) {
        element.dataset.wasDisabled = element.disabled ? '1' : '0';
        element.disabled = true;
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

    return texts.errorGeneric;
  }

  function cssEscape(value) {
    if (window.CSS && typeof window.CSS.escape === 'function') {
      return window.CSS.escape(value);
    }

    return String(value).replace(/["\\]/g, '\\$&');
  }
})();
