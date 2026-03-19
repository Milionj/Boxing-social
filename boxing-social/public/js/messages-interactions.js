(() => {
  const page = document.querySelector('[data-messages-page]');
  if (!(page instanceof HTMLElement)) {
    return;
  }

  const threadPanel = page.querySelector('[data-messages-thread]');
  const conversationList = page.querySelector('[data-messages-conversation-list]');
  const conversationsEmpty = page.querySelector('[data-messages-conversations-empty]');
  const idleState = threadPanel?.querySelector('[data-messages-idle-state]');
  const activeState = threadPanel?.querySelector('[data-messages-active-state]');
  const threadTarget = threadPanel?.querySelector('[data-messages-thread-target]');
  const emptyThread = threadPanel?.querySelector('[data-messages-empty-thread]');
  const messageList = threadPanel?.querySelector('[data-message-list]');
  const replyForm = threadPanel?.querySelector('[data-message-send-form][data-message-mode="reply"]');
  const replyReceiver = replyForm?.querySelector('input[name="receiver_id"]');
  const openForm = page.querySelector('[data-messages-open-form]');
  const socialI18n = document.querySelector('[data-social-i18n]');

  const texts = {
    errorGeneric: socialI18n?.dataset.errorGeneric || 'Impossible d’envoyer le message pour le moment.',
    sent: page.dataset.messageSent || 'Message envoyé.',
    profileLink: page.dataset.messageProfileLink || 'Voir le profil',
    you: page.dataset.messageYou || 'Moi',
    other: page.dataset.messageOther || 'La personne',
    emptyThread: page.dataset.messageEmptyThread || 'Aucun message dans cette conversation.',
    read: page.dataset.messageRead || 'Lu',
    unread: page.dataset.messageUnread || 'Non lu',
  };

  const endpoints = {
    thread: page.dataset.messageThreadEndpoint || '/messages/thread',
    poll: page.dataset.messagePollEndpoint || '/messages/poll',
  };

  const requestHeaders = {
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  };

  const state = {
    pollTimer: 0,
    pollInFlight: false,
  };

  document.addEventListener('submit', (event) => {
    const target = event.target;
    if (!(target instanceof HTMLFormElement)) {
      return;
    }

    if (target.matches('[data-message-send-form]')) {
      event.preventDefault();
      void handleMessageSend(target);
      return;
    }

    if (target.matches('[data-messages-open-form]')) {
      event.preventDefault();
      void handleConversationLookup(target);
    }
  });

  document.addEventListener('click', (event) => {
    const target = event.target;
    if (!(target instanceof Element)) {
      return;
    }

    const trigger = target.closest('[data-conversation-open]');
    if (!(trigger instanceof HTMLAnchorElement)) {
      return;
    }

    if (
      event.defaultPrevented
      || event.button !== 0
      || event.metaKey
      || event.ctrlKey
      || event.shiftKey
      || event.altKey
      || trigger.target === '_blank'
    ) {
      return;
    }

    const item = trigger.closest('[data-conversation-item]');
    const userId = Number(item?.getAttribute('data-user-id') || 0);
    if (userId <= 0) {
      return;
    }

    event.preventDefault();
    void openConversation({ userId });
  });

  window.addEventListener('popstate', () => {
    const params = new URLSearchParams(window.location.search);
    const userId = Number(params.get('user_id') || 0);
    const username = (params.get('username') || '').trim();

    if (userId > 0 || username !== '') {
      void openConversation({ userId, username, pushHistory: false, restoreOnFailure: false });
      return;
    }

    deactivateConversation();
  });

  if (getSelectedUserId() > 0) {
    startPolling();
    applyReadState(getLatestReadMessageIdFromDom());
  }

  async function handleMessageSend(form) {
    const formData = new FormData(form);
    clearFeedback(form);
    setFormPending(form, true);

    try {
      const payload = await requestJson(form.action, {
        method: (form.method || 'POST').toUpperCase(),
        body: formData,
      });

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

      applyReadState(Number(payload.lastReadMessageId || 0));

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

  async function handleConversationLookup(form) {
    const usernameInput = form.querySelector('input[name="username"]');
    if (!(usernameInput instanceof HTMLInputElement)) {
      return;
    }

    const username = usernameInput.value.trim();
    if (username === '') {
      showFeedback(form, texts.errorGeneric);
      return;
    }

    clearFeedback(form);
    setFormPending(form, true);

    try {
      await openConversation({ username });
      usernameInput.value = '';
    } catch (error) {
      showFeedback(form, resolveErrorMessage(error));
    } finally {
      setFormPending(form, false);
    }
  }

  async function openConversation({ userId = 0, username = '', pushHistory = true, restoreOnFailure = true }) {
    const previousUserId = getSelectedUserId();
    const url = new URL(endpoints.thread, window.location.origin);

    if (userId > 0) {
      url.searchParams.set('user_id', String(userId));
    } else if (username !== '') {
      url.searchParams.set('username', username);
    } else {
      throw new Error('Aucune conversation sélectionnée.');
    }

    try {
      const payload = await requestJson(url.toString());
      applyThreadPayload(payload, pushHistory);
      return payload;
    } catch (error) {
      if (restoreOnFailure && previousUserId > 0) {
        setConversationActive(previousUserId);
      }

      throw error;
    }
  }

  function applyThreadPayload(payload, pushHistory) {
    if (!payload?.conversation || !Array.isArray(payload.thread) || !Array.isArray(payload.conversations)) {
      throw new Error(texts.errorGeneric);
    }

    syncConversations(payload.conversations, payload.conversation.userId);
    activateConversation(payload.conversation, pushHistory);
    renderThread(payload.thread);
    applyReadState(Number(payload.lastReadMessageId || 0));
  }

  async function pollConversation() {
    const selectedUserId = getSelectedUserId();
    if (selectedUserId <= 0 || state.pollInFlight) {
      return;
    }

    state.pollInFlight = true;

    try {
      const url = new URL(endpoints.poll, window.location.origin);
      url.searchParams.set('user_id', String(selectedUserId));
      url.searchParams.set('after_id', String(getLatestMessageId()));

      const payload = await requestJson(url.toString());
      if (getSelectedUserId() !== selectedUserId) {
        return;
      }

      if (Array.isArray(payload.conversations)) {
        syncConversations(payload.conversations, selectedUserId);
      }

      if (Array.isArray(payload.messages)) {
        payload.messages.forEach((message) => appendMessage(message, false));
      }

      applyReadState(Number(payload.lastReadMessageId || 0));
    } catch (error) {
      // On garde le polling silencieux pour éviter de polluer l'UI à chaque tentative ratée.
      console.error(error);
    } finally {
      state.pollInFlight = false;
      schedulePoll();
    }
  }

  async function requestJson(url, options = {}) {
    const response = await fetch(url, {
      ...options,
      headers: {
        ...requestHeaders,
        ...(options.headers || {}),
      },
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

  function activateConversation(conversation, pushHistory = true) {
    if (!(threadPanel instanceof HTMLElement) || !(activeState instanceof HTMLElement)) {
      return;
    }

    threadPanel.dataset.selectedUserId = String(conversation.userId);
    threadPanel.dataset.selectedUsername = conversation.username;

    if (idleState instanceof HTMLElement) {
      idleState.hidden = true;
    }

    activeState.hidden = false;

    if (threadTarget instanceof HTMLElement) {
      threadTarget.hidden = false;
      threadTarget.textContent = conversation.username;
    }

    if (replyReceiver instanceof HTMLInputElement) {
      replyReceiver.value = String(conversation.userId);
    }

    if (emptyThread instanceof HTMLElement && (!(messageList instanceof HTMLElement) || messageList.children.length === 0)) {
      emptyThread.hidden = false;
      emptyThread.textContent = texts.emptyThread;
    }

    if (messageList instanceof HTMLElement) {
      messageList.hidden = messageList.children.length === 0;
    }

    setConversationActive(conversation.userId);

    if (pushHistory && window.location.pathname + window.location.search !== conversation.threadUrl) {
      window.history.pushState({}, '', conversation.threadUrl);
    }

    startPolling();
  }

  function deactivateConversation() {
    if (!(threadPanel instanceof HTMLElement)) {
      return;
    }

    threadPanel.dataset.selectedUserId = '0';
    threadPanel.dataset.selectedUsername = '';

    if (idleState instanceof HTMLElement) {
      idleState.hidden = false;
    }

    if (activeState instanceof HTMLElement) {
      activeState.hidden = true;
    }

    setConversationActive(0);
    stopPolling();
  }

  function appendMessage(message, smooth = true) {
    if (!(messageList instanceof HTMLElement)) {
      return;
    }

    const existing = messageList.querySelector(`[data-message-id="${cssEscape(String(message.id))}"]`);
    if (existing instanceof HTMLElement) {
      return;
    }

    messageList.hidden = false;
    if (emptyThread instanceof HTMLElement) {
      emptyThread.hidden = true;
    }

    const bubble = createMessageBubble(message);
    messageList.append(bubble);
    bubble.scrollIntoView({ behavior: smooth ? 'smooth' : 'auto', block: 'end' });
  }

  function renderThread(messages) {
    if (!(messageList instanceof HTMLElement)) {
      return;
    }

    messageList.replaceChildren();

    if (!Array.isArray(messages) || messages.length === 0) {
      messageList.hidden = true;
      if (emptyThread instanceof HTMLElement) {
        emptyThread.hidden = false;
        emptyThread.textContent = texts.emptyThread;
      }
      return;
    }

    messages.forEach((message) => {
      messageList.append(createMessageBubble(message));
    });

    messageList.hidden = false;
    if (emptyThread instanceof HTMLElement) {
      emptyThread.hidden = true;
    }
    messageList.lastElementChild?.scrollIntoView({ behavior: 'auto', block: 'end' });
  }

  function syncConversations(conversations, activeUserId) {
    if (!(conversationList instanceof HTMLElement)) {
      return;
    }

    if (!Array.isArray(conversations) || conversations.length === 0) {
      conversationList.replaceChildren();
      conversationList.hidden = true;
      if (conversationsEmpty instanceof HTMLElement) {
        conversationsEmpty.hidden = false;
      }
      return;
    }

    const fragment = document.createDocumentFragment();
    conversations.forEach((conversation) => {
      fragment.append(createConversationItem(conversation, activeUserId));
    });

    conversationList.replaceChildren(fragment);
    conversationList.hidden = false;
    if (conversationsEmpty instanceof HTMLElement) {
      conversationsEmpty.hidden = true;
    }
  }

  function upsertConversation(conversation) {
    if (!(conversationList instanceof HTMLElement)) {
      return;
    }

    const activeUserId = Number(conversation.userId || 0);
    const existing = conversationList.querySelector(`[data-user-id="${cssEscape(String(activeUserId))}"]`);
    const item = createConversationItem(conversation, activeUserId);

    if (existing instanceof HTMLElement) {
      existing.replaceWith(item);
    }

    conversationList.prepend(item);
    conversationList.hidden = false;
    if (conversationsEmpty instanceof HTMLElement) {
      conversationsEmpty.hidden = true;
    }

    setConversationActive(activeUserId);
  }

  function createConversationItem(conversation, activeUserId) {
    const article = document.createElement('article');
    const isActive = Number(conversation.userId) === Number(activeUserId);
    const unreadCount = Math.max(0, Number(conversation.unreadCount || 0));

    article.className = `conversation-item${isActive ? ' is-active' : ''}${unreadCount > 0 ? ' is-unread' : ''}`;
    article.dataset.conversationItem = '';
    article.dataset.userId = String(conversation.userId);

    const threadLink = document.createElement('a');
    threadLink.className = 'conversation-item__main';
    threadLink.href = conversation.threadUrl;
    threadLink.dataset.conversationOpen = '';

    const avatar = document.createElement('span');
    avatar.className = 'conversation-item__avatar';
    avatar.textContent = String(conversation.username).charAt(0).toUpperCase();

    const copy = document.createElement('span');
    copy.className = 'conversation-item__copy';

    const username = document.createElement('strong');
    username.textContent = conversation.username;

    const meta = document.createElement('span');
    meta.className = 'conversation-item__meta';

    const date = document.createElement('small');
    date.dataset.conversationDate = '';
    date.textContent = conversation.lastMessageAt || '';

    const unreadBadge = document.createElement('span');
    unreadBadge.className = 'conversation-item__unread';
    unreadBadge.dataset.conversationUnread = '';
    unreadBadge.textContent = String(unreadCount);
    unreadBadge.hidden = unreadCount <= 0;

    meta.appendChild(date);
    meta.appendChild(unreadBadge);
    copy.appendChild(username);
    copy.appendChild(meta);
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
    article.dataset.messageIsMine = message.isMine ? '1' : '0';

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

    if (message.isMine) {
      const stateLabel = document.createElement('small');
      stateLabel.dataset.messageReadState = '';
      stateLabel.className = `message-bubble__state ${message.isRead ? 'is-read' : 'is-unread'}`;
      stateLabel.textContent = message.isRead ? texts.read : texts.unread;
      article.appendChild(stateLabel);
    }

    return article;
  }

  function applyReadState(lastReadMessageId) {
    if (!(messageList instanceof HTMLElement)) {
      return;
    }

    messageList.querySelectorAll('[data-message-item][data-message-is-mine="1"]').forEach((node) => {
      if (!(node instanceof HTMLElement)) {
        return;
      }

      const messageId = Number(node.dataset.messageId || 0);
      setMessageReadState(node, messageId > 0 && messageId <= lastReadMessageId);
    });
  }

  function setMessageReadState(node, isRead) {
    const label = node.querySelector('[data-message-read-state]');
    if (!(label instanceof HTMLElement)) {
      return;
    }

    label.textContent = isRead ? texts.read : texts.unread;
    label.classList.toggle('is-read', isRead);
    label.classList.toggle('is-unread', !isRead);
  }

  function setConversationActive(activeUserId) {
    if (!(conversationList instanceof HTMLElement)) {
      return;
    }

    conversationList.querySelectorAll('[data-conversation-item]').forEach((node) => {
      if (!(node instanceof HTMLElement)) {
        return;
      }

      node.classList.toggle('is-active', Number(node.dataset.userId || 0) === Number(activeUserId));
    });
  }

  function getSelectedUserId() {
    return Number(threadPanel?.getAttribute('data-selected-user-id') || 0);
  }

  function getLatestMessageId() {
    if (!(messageList instanceof HTMLElement)) {
      return 0;
    }

    const last = messageList.lastElementChild;
    if (!(last instanceof HTMLElement)) {
      return 0;
    }

    return Number(last.dataset.messageId || 0);
  }

  function getLatestReadMessageIdFromDom() {
    if (!(messageList instanceof HTMLElement)) {
      return 0;
    }

    let lastReadMessageId = 0;
    messageList.querySelectorAll('[data-message-item][data-message-is-mine="1"]').forEach((node) => {
      if (!(node instanceof HTMLElement)) {
        return;
      }

      const state = node.querySelector('[data-message-read-state]');
      if (!(state instanceof HTMLElement) || !state.classList.contains('is-read')) {
        return;
      }

      lastReadMessageId = Math.max(lastReadMessageId, Number(node.dataset.messageId || 0));
    });

    return lastReadMessageId;
  }

  function startPolling() {
    stopPolling();
    schedulePoll();
  }

  function stopPolling() {
    if (state.pollTimer > 0) {
      window.clearTimeout(state.pollTimer);
      state.pollTimer = 0;
    }
  }

  function schedulePoll() {
    stopPolling();

    if (getSelectedUserId() <= 0) {
      return;
    }

    state.pollTimer = window.setTimeout(() => {
      void pollConversation();
    }, 5000);
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
