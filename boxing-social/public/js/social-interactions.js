(() => {
  const i18n = document.querySelector('[data-social-i18n]');
  if (!i18n) {
    return;
  }

  const texts = {
    errorGeneric: i18n.dataset.errorGeneric || 'Impossible de mettre à jour l’interaction pour le moment.',
    friendRequestSent: i18n.dataset.friendRequestSent || 'Demande envoyée.',
    friendRequestCancelled: i18n.dataset.friendRequestCancelled || 'Demande annulée.',
    friendsOpenProfile: i18n.dataset.friendsOpenProfile || 'Voir le profil',
    friendsPendingWith: i18n.dataset.friendsPendingWith || 'Invitation en attente pour',
    friendsCancel: i18n.dataset.friendsCancel || 'Annuler',
    friendsRemove: i18n.dataset.friendsRemove || 'Retirer',
    friendsRemoved: i18n.dataset.friendsRemoved || 'Ami retiré.',
    friendsEmptyIncoming: i18n.dataset.friendsEmptyIncoming || 'Aucune demande reçue.',
    friendsEmptyOutgoing: i18n.dataset.friendsEmptyOutgoing || 'Aucune demande envoyée.',
    friendsEmptyFriends: i18n.dataset.friendsEmptyFriends || 'Tu n’as pas encore d’amis.',
    notificationsEmpty: i18n.dataset.notificationsEmpty || 'Aucune notification pour le moment.',
    notificationsUnreadSuffix: i18n.dataset.notificationsUnreadSuffix || 'non lues',
  };

  const requestHeaders = {
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  };

  document.addEventListener('submit', (event) => {
    const target = event.target;
    if (!(target instanceof HTMLFormElement)) {
      return;
    }

    if (target.matches('[data-notification-read-form]')) {
      event.preventDefault();
      void handleNotificationRead(target);
      return;
    }

    if (target.matches('[data-notifications-mark-all-form]')) {
      event.preventDefault();
      void handleNotificationsReadAll(target);
      return;
    }

    if (target.matches('[data-friend-send-form]')) {
      event.preventDefault();
      void handleFriendSend(target);
      return;
    }

    if (target.matches('[data-friend-accept-form]')) {
      event.preventDefault();
      void handleFriendReply(target, 'accept');
      return;
    }

    if (target.matches('[data-friend-decline-form]')) {
      event.preventDefault();
      void handleFriendReply(target, 'decline');
      return;
    }

    if (target.matches('[data-friend-cancel-form]')) {
      event.preventDefault();
      void handleFriendCancel(target);
      return;
    }

    if (target.matches('[data-friend-remove-form]')) {
      event.preventDefault();
      void handleFriendRemove(target);
    }
  });

  document.addEventListener('click', (event) => {
    const target = event.target;
    if (!(target instanceof Element)) {
      return;
    }

    const notificationItem = target.closest('[data-notification-item]');
    if (!(notificationItem instanceof HTMLElement)) {
      return;
    }

    if (target.closest('a, button, form, input, textarea, select, label')) {
      return;
    }

    const openUrl = notificationItem.dataset.notificationOpenUrl || '';
    if (openUrl === '') {
      return;
    }

    window.location.assign(openUrl);
  });

  async function handleNotificationRead(form) {
    const scope = findScope(form, 'notifications');
    const formData = new FormData(form);
    clearFeedback(scope);
    setFormPending(form, true);

    try {
      const payload = await requestJson(form, formData);
      const item = form.closest('[data-notification-item]');
      if (item instanceof HTMLElement) {
        item.classList.remove('is-unread');
      }
      form.remove();
      updateNotificationCounts(payload.unreadCount);
    } catch (error) {
      showFeedback(scope, resolveErrorMessage(error));
    } finally {
      setFormPending(form, false);
    }
  }

  async function handleNotificationsReadAll(form) {
    const scope = findScope(form, 'notifications');
    const formData = new FormData(form);
    clearFeedback(scope);
    setFormPending(form, true);

    try {
      const payload = await requestJson(form, formData);
      scope?.querySelectorAll('[data-notification-item].is-unread').forEach((item) => {
        item.classList.remove('is-unread');
      });
      scope?.querySelectorAll('[data-notification-read-form]').forEach((readForm) => {
        readForm.remove();
      });
      updateNotificationCounts(payload.unreadCount);
    } catch (error) {
      showFeedback(scope, resolveErrorMessage(error));
    } finally {
      setFormPending(form, false);
    }
  }

  async function handleFriendSend(form) {
    const scope = findScope(form, 'social');
    const formData = new FormData(form);
    clearFeedback(scope);
    setFormPending(form, true);

    try {
      const payload = await requestJson(form, formData);
      updateFriendCounts(payload.counts);

      if (payload.outgoing) {
        prependOutgoingRequest(payload.outgoing);
      }

      const usernameInput = form.querySelector('input[name="username"][type="text"]');
      const button = form.querySelector('[data-friend-send-button]');
      if (usernameInput instanceof HTMLInputElement) {
        usernameInput.value = '';
      } else if (button instanceof HTMLButtonElement) {
        button.textContent = button.dataset.labelSent || texts.friendRequestSent;
        button.disabled = true;
      }

      showFeedback(scope, payload.message || texts.friendRequestSent, 'success');
      syncFriendsEmptyStates();
    } catch (error) {
      showFeedback(scope, resolveErrorMessage(error));
    } finally {
      const keepDisabled = !(form.querySelector('input[name="username"][type="text"]') instanceof HTMLInputElement)
        && form.querySelector('[data-friend-send-button]')?.disabled === true;
      setFormPending(form, false, { keepButtonsDisabled: keepDisabled });
    }
  }

  async function handleFriendReply(form, action) {
    const scope = findScope(form, 'social');
    const formData = new FormData(form);
    clearFeedback(scope);
    setFormPending(form, true);

    try {
      const payload = await requestJson(form, formData);
      const row = form.closest('[data-friendship-row]');
      if (row instanceof HTMLElement) {
        row.remove();
      }

      updateFriendCounts(payload.counts);

      if (action === 'accept' && payload.friend) {
        prependFriendTile(payload.friend);
      }

      syncFriendsEmptyStates();
    } catch (error) {
      showFeedback(scope, resolveErrorMessage(error));
      setFormPending(form, false);
    }
  }

  async function handleFriendCancel(form) {
    const scope = findScope(form, 'social');
    const formData = new FormData(form);
    clearFeedback(scope);
    setFormPending(form, true);

    try {
      const payload = await requestJson(form, formData);
      const row = form.closest('[data-friendship-row]');
      if (row instanceof HTMLElement) {
        row.remove();
      }

      updateFriendCounts(payload.counts);
      syncFriendsEmptyStates();
    } catch (error) {
      showFeedback(scope, resolveErrorMessage(error));
      setFormPending(form, false);
    }
  }

  async function handleFriendRemove(form) {
    const scope = findScope(form, 'social');
    const formData = new FormData(form);
    clearFeedback(scope);
    setFormPending(form, true);

    try {
      const payload = await requestJson(form, formData);
      const tile = form.closest('[data-friendship-row]');
      if (tile instanceof HTMLElement) {
        tile.remove();
      }

      updateFriendCounts(payload.counts);
      syncFriendsEmptyStates();
    } catch (error) {
      showFeedback(scope, resolveErrorMessage(error));
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

  function updateNotificationCounts(count) {
    document.querySelectorAll('[data-notifications-badge]').forEach((badge) => {
      const format = badge.dataset.countFormat || 'suffix';
      badge.textContent = format === 'suffix' ? `${count} ${texts.notificationsUnreadSuffix}` : String(count);
    });

    document.querySelectorAll('[data-notifications-nav-count]').forEach((badge) => {
      if (!(badge instanceof HTMLElement)) {
        return;
      }

      badge.textContent = String(count);
      badge.hidden = count === 0;
    });
  }

  function updateFriendCounts(counts) {
    if (!counts) {
      return;
    }

    Object.entries(counts).forEach(([key, value]) => {
      document.querySelectorAll(`[data-friends-count="${key}"]`).forEach((node) => {
        node.textContent = String(value);
      });
    });
  }

  function prependOutgoingRequest(outgoing) {
    const list = document.querySelector('[data-friends-outgoing-list]');
    if (!(list instanceof HTMLElement)) {
      return;
    }

    if (list.querySelector(`[href="${cssEscape(outgoing.profileUrl)}"]`)) {
      return;
    }

    list.hidden = false;
    list.prepend(createOutgoingRow(outgoing));
  }

  function prependFriendTile(friend) {
    const grid = document.querySelector('[data-friends-grid]');
    if (!(grid instanceof HTMLElement)) {
      return;
    }

    if (grid.querySelector(`[href="${cssEscape(friend.profileUrl)}"]`)) {
      return;
    }

    grid.hidden = false;
    grid.prepend(createFriendTile(friend));
  }

  function syncFriendsEmptyStates() {
    syncListEmptyState('[data-friends-incoming-list]', '[data-friends-empty="incoming"]', '[data-friendship-row]', texts.friendsEmptyIncoming);
    syncListEmptyState('[data-friends-outgoing-list]', '[data-friends-empty="outgoing"]', '.friend-row', texts.friendsEmptyOutgoing);
    syncListEmptyState('[data-friends-grid]', '[data-friends-empty="friends"]', '.friend-tile', texts.friendsEmptyFriends);
  }

  function syncListEmptyState(listSelector, emptySelector, itemSelector, fallbackText) {
    const list = document.querySelector(listSelector);
    const empty = document.querySelector(emptySelector);

    if (!(list instanceof HTMLElement) || !(empty instanceof HTMLElement)) {
      return;
    }

    const hasItems = list.querySelector(itemSelector) !== null;
    list.hidden = !hasItems;
    empty.hidden = hasItems;

    if (!hasItems && empty.textContent?.trim() === '') {
      empty.textContent = fallbackText;
    }
  }

  function createOutgoingRow(outgoing) {
    const article = document.createElement('article');
    article.className = 'friend-row friend-row--simple';
    article.dataset.socialScope = '';
    article.dataset.friendshipRow = '';
    article.dataset.friendshipId = String(outgoing.friendshipId);

    const identity = document.createElement('div');
    identity.className = 'friend-row__identity';

    const avatar = document.createElement('div');
    avatar.className = 'friend-row__avatar';
    avatar.textContent = String(outgoing.username).charAt(0).toUpperCase();

    const copy = document.createElement('div');

    const title = document.createElement('h3');
    const titleLink = document.createElement('a');
    titleLink.href = outgoing.profileUrl;
    titleLink.textContent = outgoing.username;
    title.appendChild(titleLink);

    const description = document.createElement('p');
    description.textContent = texts.friendsPendingWith;

    copy.appendChild(title);
    copy.appendChild(description);
    identity.appendChild(avatar);
    identity.appendChild(copy);

    const actions = document.createElement('div');
    actions.className = 'friend-row__actions';

    const profileLink = document.createElement('a');
    profileLink.className = 'friend-row__profile-link';
    profileLink.href = outgoing.profileUrl;
    profileLink.textContent = texts.friendsOpenProfile;

    const cancelForm = document.createElement('form');
    cancelForm.method = 'post';
    cancelForm.action = '/friends/cancel';
    cancelForm.dataset.friendCancelForm = '';

    const cancelInput = document.createElement('input');
    cancelInput.type = 'hidden';
    cancelInput.name = 'friendship_id';
    cancelInput.value = String(outgoing.friendshipId);

    const cancelButton = document.createElement('button');
    cancelButton.className = 'button-secondary';
    cancelButton.type = 'submit';
    cancelButton.textContent = texts.friendsCancel;

    cancelForm.appendChild(cancelInput);
    cancelForm.appendChild(cancelButton);

    actions.appendChild(profileLink);
    actions.appendChild(cancelForm);

    const feedback = document.createElement('p');
    feedback.className = 'interaction-feedback';
    feedback.dataset.interactionFeedback = '';
    feedback.hidden = true;

    article.appendChild(identity);
    article.appendChild(actions);
    article.appendChild(feedback);

    return article;
  }

  function createFriendTile(friend) {
    const article = document.createElement('article');
    article.className = 'friend-tile';
    article.dataset.socialScope = '';
    article.dataset.friendshipRow = '';
    article.dataset.friendshipId = String(friend.friendshipId);

    const avatar = document.createElement('div');
    avatar.className = 'friend-tile__avatar';
    avatar.textContent = String(friend.username).charAt(0).toUpperCase();

    const title = document.createElement('h3');
    const titleLink = document.createElement('a');
    titleLink.href = friend.profileUrl;
    titleLink.textContent = friend.username;
    title.appendChild(titleLink);

    const link = document.createElement('a');
    link.className = 'friend-tile__link';
    link.href = friend.profileUrl;
    link.textContent = texts.friendsOpenProfile;

    const actions = document.createElement('div');
    actions.className = 'friend-tile__actions';

    const removeForm = document.createElement('form');
    removeForm.method = 'post';
    removeForm.action = '/friends/remove';
    removeForm.dataset.friendRemoveForm = '';

    const removeInput = document.createElement('input');
    removeInput.type = 'hidden';
    removeInput.name = 'friendship_id';
    removeInput.value = String(friend.friendshipId);

    const removeButton = document.createElement('button');
    removeButton.className = 'button-secondary';
    removeButton.type = 'submit';
    removeButton.textContent = texts.friendsRemove;

    removeForm.appendChild(removeInput);
    removeForm.appendChild(removeButton);

    actions.appendChild(link);
    actions.appendChild(removeForm);

    const feedback = document.createElement('p');
    feedback.className = 'interaction-feedback';
    feedback.dataset.interactionFeedback = '';
    feedback.hidden = true;

    article.appendChild(avatar);
    article.appendChild(title);
    article.appendChild(actions);
    article.appendChild(feedback);

    return article;
  }

  function showFeedback(scope, message, kind = 'error') {
    const feedback = scope?.querySelector('[data-interaction-feedback]');
    if (!(feedback instanceof HTMLElement)) {
      return;
    }

    feedback.textContent = message;
    feedback.hidden = false;
    feedback.classList.remove('is-error', 'is-success');
    feedback.classList.add(kind === 'success' ? 'is-success' : 'is-error');
  }

  function clearFeedback(scope) {
    const feedback = scope?.querySelector('[data-interaction-feedback]');
    if (!(feedback instanceof HTMLElement)) {
      return;
    }

    feedback.textContent = '';
    feedback.hidden = true;
    feedback.classList.remove('is-error', 'is-success');
  }

  function findScope(form, fallback) {
    return form.closest('[data-social-scope], [data-notifications-scope]') || (
      fallback === 'social' ? document.querySelector('[data-friends-page]') : document.body
    );
  }

  function setFormPending(form, pending, options = {}) {
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

    return texts.errorGeneric;
  }

  function cssEscape(value) {
    if (window.CSS && typeof window.CSS.escape === 'function') {
      return window.CSS.escape(value);
    }

    return String(value).replace(/["\\]/g, '\\$&');
  }
})();
