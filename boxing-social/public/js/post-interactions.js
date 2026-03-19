(() => {
  const body = document.body;
  if (!body) {
    return;
  }

  const defaultErrorMessage =
    body.dataset.postInteractionError || 'Impossible de mettre à jour l’interaction pour le moment.';
  const deleteCommentLabel = body.dataset.commentDeleteLabel || 'Supprimer commentaire';
  const socialI18n = document.querySelector('[data-social-i18n]');
  const csrfToken = socialI18n?.dataset.csrfToken || '';
  const trainingInterestAction = socialI18n?.dataset.trainingInterestAction || 'Cliquer sur le poing pour manifester votre intérêt';
  const trainingInterestSent = socialI18n?.dataset.trainingInterestSent || 'Intérêt déjà envoyé';
  const sportsScheduleEndpoint = body.dataset.sportsScheduleEndpoint || '';
  const sportsEventEndpoint = body.dataset.sportsEventEndpoint || '';
  const sportsPreviewTitle = socialI18n?.dataset.sportsPreviewTitle || 'Infos MMA';
  const sportsPreviewIntro = socialI18n?.dataset.sportsPreviewIntro || 'Agenda MMA dans le popup.';
  const sportsPreviewSeason = socialI18n?.dataset.sportsPreviewSeason || 'Saison';
  const sportsPreviewRefresh = socialI18n?.dataset.sportsPreviewRefresh || 'Actualiser';
  const sportsPreviewLoading = socialI18n?.dataset.sportsPreviewLoading || 'Chargement du calendrier MMA...';
  const sportsPreviewEmpty = socialI18n?.dataset.sportsPreviewEmpty || 'Aucun événement MMA trouvé pour cette saison.';
  const sportsPreviewUnavailable = socialI18n?.dataset.sportsPreviewUnavailable || 'Impossible de charger les infos MMA pour le moment.';
  const sportsPreviewNotConfigured = socialI18n?.dataset.sportsPreviewNotConfigured || 'Configuration API sports manquante.';
  const sportsPreviewSelectEvent = socialI18n?.dataset.sportsPreviewSelectEvent || 'Sélectionne un événement pour afficher ses détails.';
  const sportsPreviewStatus = socialI18n?.dataset.sportsPreviewStatus || 'Statut';
  const sportsPreviewVenue = socialI18n?.dataset.sportsPreviewVenue || 'Lieu';
  const sportsPreviewPromotion = socialI18n?.dataset.sportsPreviewPromotion || 'Organisation';
  const sportsPreviewDetails = socialI18n?.dataset.sportsPreviewDetails || 'Détails';
  const sportsPreviewFights = socialI18n?.dataset.sportsPreviewFights || 'Combats';
  const sportsPreviewUpdated = socialI18n?.dataset.sportsPreviewUpdated || 'Mis à jour';
  const feedSideCollapse = socialI18n?.dataset.feedSideCollapse || 'Replier';
  const feedSideExpand = socialI18n?.dataset.feedSideExpand || 'Déplier';
  const sportsSeasonOptions = Array.from(
    new Set([2022, 2023, 2024, new Date().getFullYear() - 1, new Date().getFullYear(), new Date().getFullYear() + 1])
  ).sort((left, right) => left - right);
  const sportsCache = new Map();
  const sportsEventCache = new Map();
  const sanitizeHtml = (html) => {
    if (window.BoxingSocialSecurity && typeof window.BoxingSocialSecurity.sanitizeHtml === 'function') {
      return window.BoxingSocialSecurity.sanitizeHtml(html);
    }

    return escapeHtml(String(html));
  };
  const setSanitizedHtml = (element, html) => {
    if (window.BoxingSocialSecurity && typeof window.BoxingSocialSecurity.setSanitizedHtml === 'function') {
      window.BoxingSocialSecurity.setSanitizedHtml(element, html);
      return;
    }

    element.innerHTML = sanitizeHtml(html);
  };

  const requestHeaders = {
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    ...(csrfToken !== '' ? { 'X-CSRF-Token': csrfToken } : {}),
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

  initSideCardToggles();
  initInlineSportsWidgets();

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
      credentials: 'same-origin',
      cache: 'no-store',
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

  function getSportsPanelRefs(sidebar) {
    const title = sidebar.querySelector('[data-sports-panel-title]');
    const intro = sidebar.querySelector('[data-sports-panel-intro]');
    const seasonLabel = sidebar.querySelector('[data-sports-panel-season-label]');
    const seasonSelect = sidebar.querySelector('[data-sports-panel-season]');
    const refreshButton = sidebar.querySelector('[data-sports-panel-refresh]');
    const status = sidebar.querySelector('[data-sports-panel-status]');
    const list = sidebar.querySelector('[data-sports-panel-list]');
    const detail = sidebar.querySelector('[data-sports-panel-detail]');

    if (
      !(title instanceof HTMLElement) ||
      !(intro instanceof HTMLElement) ||
      !(seasonLabel instanceof HTMLElement) ||
      !(seasonSelect instanceof HTMLSelectElement) ||
      !(refreshButton instanceof HTMLButtonElement) ||
      !(status instanceof HTMLElement) ||
      !(list instanceof HTMLElement) ||
      !(detail instanceof HTMLElement)
    ) {
      throw new Error('Sports preview panel is invalid.');
    }

    return {
      title,
      intro,
      seasonLabel,
      seasonSelect,
      refreshButton,
      status,
      list,
      detail,
    };
  }

  function ensureSportsPanel(sidebar) {
    let panel = sidebar.querySelector('[data-sports-panel]');
    if (!(panel instanceof HTMLElement)) {
      setSanitizedHtml(sidebar, `
        <section class="post-preview-panel" data-sports-panel>
          <div class="post-preview-panel__head">
            <div>
              <p class="post-preview-panel__eyebrow" data-sports-panel-title></p>
              <p class="post-preview-panel__intro" data-sports-panel-intro></p>
            </div>
          </div>
          <div class="post-preview-panel__controls">
            <label class="post-preview-panel__season">
              <span data-sports-panel-season-label></span>
              <select data-sports-panel-season></select>
            </label>
            <button type="button" class="post-preview-panel__refresh" data-sports-panel-refresh></button>
          </div>
          <p class="post-preview-panel__status" data-sports-panel-status></p>
          <div class="post-preview-panel__list" data-sports-panel-list></div>
          <div class="post-preview-panel__detail" data-sports-panel-detail></div>
        </section>
      `);
      panel = sidebar.querySelector('[data-sports-panel]');
      if (!(panel instanceof HTMLElement)) {
        throw new Error('Sports preview panel could not be created.');
      }

      const refs = getSportsPanelRefs(sidebar);
      refs.title.textContent = sportsPreviewTitle;
      refs.intro.textContent = sportsPreviewIntro;
      refs.seasonLabel.textContent = sportsPreviewSeason;
      refs.refreshButton.textContent = sportsPreviewRefresh;
      refs.seasonSelect.innerHTML = '';

      sportsSeasonOptions.forEach((season) => {
        const option = document.createElement('option');
        option.value = String(season);
        option.textContent = String(season);
        refs.seasonSelect.appendChild(option);
      });

      refs.seasonSelect.addEventListener('change', () => {
        const season = parseInt(refs.seasonSelect.value, 10);
        if (Number.isNaN(season)) {
          return;
        }

        sidebar.dataset.sportsSeason = String(season);
        void loadSportsPanel(sidebar, season);
      });

      refs.refreshButton.addEventListener('click', () => {
        const season = parseInt(refs.seasonSelect.value, 10);
        if (Number.isNaN(season)) {
          return;
        }

        sidebar.dataset.sportsSeason = String(season);
        void loadSportsPanel(sidebar, season, { force: true });
      });
    }

    return getSportsPanelRefs(sidebar);
  }

  function renderSportsStatus(refs, message, tone = 'muted') {
    refs.status.textContent = message;
    refs.status.dataset.tone = tone;
  }

  function renderSportsEmpty(refs, message) {
    refs.list.innerHTML = '';
    refs.detail.innerHTML = '';
    renderSportsStatus(refs, message, 'muted');
  }

  function renderSportsDetail(refs, event) {
    if (!event) {
      refs.detail.innerHTML = '';
      renderSportsStatus(refs, sportsPreviewSelectEvent, 'muted');
      return;
    }

    refs.detail.innerHTML = '';

    const card = document.createElement('article');
    card.className = 'post-preview-panel__event-detail';

    const title = document.createElement('h3');
    title.textContent = event.title || 'MMA event';
    card.appendChild(title);

    if (event.dateLabel) {
      const date = document.createElement('p');
      date.className = 'post-preview-panel__event-date';
      date.textContent = event.dateLabel;
      card.appendChild(date);
    }

    const rows = [
      [sportsPreviewStatus, event.status],
      [sportsPreviewVenue, event.venue],
      [sportsPreviewPromotion, event.promotion],
      [sportsPreviewDetails, event.headline || event.details],
    ].filter(([, value]) => typeof value === 'string' && value.trim() !== '');

    rows.forEach(([label, value]) => {
      const row = document.createElement('div');
      row.className = 'post-preview-panel__event-row';

      const strong = document.createElement('strong');
      strong.textContent = label;

      const span = document.createElement('span');
      span.textContent = value;

      row.appendChild(strong);
      row.appendChild(span);
      card.appendChild(row);
    });

    if (Array.isArray(event.fights) && event.fights.length > 0) {
      const fightsBlock = document.createElement('div');
      fightsBlock.className = 'post-preview-panel__event-fights';

      const fightsTitle = document.createElement('strong');
      fightsTitle.className = 'post-preview-panel__event-fights-title';
      fightsTitle.textContent = sportsPreviewFights;
      fightsBlock.appendChild(fightsTitle);

      const fightsList = document.createElement('div');
      fightsList.className = 'post-preview-panel__event-fights-list';

      event.fights.forEach((fight) => {
        const fightRow = document.createElement('div');
        fightRow.className = 'post-preview-panel__event-fight';

        const fightLabel = document.createElement('span');
        fightLabel.className = 'post-preview-panel__event-fight-label';
        fightLabel.textContent = fight.label || 'Fight';

        fightRow.appendChild(fightLabel);

        if (fight.meta) {
          const fightMeta = document.createElement('small');
          fightMeta.className = 'post-preview-panel__event-fight-meta';
          fightMeta.textContent = fight.meta;
          fightRow.appendChild(fightMeta);
        }

        fightsList.appendChild(fightRow);
      });

      fightsBlock.appendChild(fightsList);
      card.appendChild(fightsBlock);
    }

    refs.detail.appendChild(card);
  }

  async function selectSportsEvent(refs, event, button, list) {
    if (button instanceof HTMLButtonElement) {
      list.querySelectorAll('.post-preview-panel__event').forEach((item) => {
        item.classList.remove('is-active');
      });
      button.classList.add('is-active');
    }

    refs.list.dataset.selectedEventId = String(event.id);
    renderSportsDetail(refs, event);
    renderSportsStatus(refs, sportsPreviewLoading, 'loading');

    try {
      const detailedEvent = await fetchSportsEvent(String(event.id));
      renderSportsDetail(refs, {
        ...event,
        ...detailedEvent,
      });
      renderSportsStatus(refs, sportsPreviewUpdated + ' ' + new Date().toLocaleString(), 'info');
    } catch (error) {
      renderSportsStatus(refs, resolveErrorMessage(error), 'muted');
    }
  }

  function renderSportsList(refs, payload, selectedEventId = '') {
    refs.list.innerHTML = '';

    if (payload?.season) {
      refs.seasonSelect.value = String(payload.season);
    }

    const events = Array.isArray(payload?.events) ? payload.events : [];
    if (events.length === 0) {
      renderSportsEmpty(refs, sportsPreviewEmpty);
      return;
    }

    const visibleEvents = events.slice(0, 10);
    const list = document.createElement('div');
    list.className = 'post-preview-panel__events';

    const selected = visibleEvents.find((item) => String(item.id) === selectedEventId) || visibleEvents[0];

    visibleEvents.forEach((event) => {
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'post-preview-panel__event';
      button.dataset.eventId = String(event.id);
      button.classList.toggle('is-active', String(event.id) === String(selected.id));

      const title = document.createElement('strong');
      title.textContent = event.title || 'MMA event';

      const meta = document.createElement('span');
      meta.textContent = [event.dateLabel, event.status].filter(Boolean).join(' • ');

      button.appendChild(title);
      button.appendChild(meta);

      button.addEventListener('click', () => {
        void selectSportsEvent(refs, event, button, list);
      });

      list.appendChild(button);
    });

    refs.list.appendChild(list);

    const updatedAt = payload?.fetchedAt ? `${sportsPreviewUpdated} ${new Date(payload.fetchedAt).toLocaleString()}` : sportsPreviewSelectEvent;
    renderSportsStatus(refs, updatedAt, 'info');
    refs.list.dataset.selectedEventId = String(selected.id);
    void selectSportsEvent(refs, selected, list.querySelector(`[data-event-id="${CSS.escape(String(selected.id))}"]`), list);
  }

  async function fetchSportsSchedule(season, options = {}) {
    const { force = false } = options;

    if (!force && sportsCache.has(season)) {
      return sportsCache.get(season);
    }

    if (sportsScheduleEndpoint === '') {
      return {
        ok: true,
        configured: false,
        season,
        events: [],
      };
    }

    const url = new URL(sportsScheduleEndpoint, window.location.origin);
    url.searchParams.set('season', String(season));

    const response = await fetch(url.toString(), {
      headers: requestHeaders,
      credentials: 'same-origin',
      cache: 'no-store',
    });

    const contentType = response.headers.get('Content-Type') || '';
    if (!contentType.includes('application/json')) {
      throw new Error(sportsPreviewUnavailable);
    }

    const payload = await response.json();
    if (!response.ok || payload?.ok === false) {
      throw new Error(payload?.configured === false ? sportsPreviewNotConfigured : sportsPreviewUnavailable);
    }

    sportsCache.set(season, payload);
    return payload;
  }

  async function fetchSportsEvent(eventId, options = {}) {
    const { force = false } = options;

    if (!force && sportsEventCache.has(eventId)) {
      return sportsEventCache.get(eventId);
    }

    if (sportsEventEndpoint === '') {
      throw new Error(sportsPreviewNotConfigured);
    }

    const url = new URL(sportsEventEndpoint, window.location.origin);
    url.searchParams.set('event_id', String(eventId));

    const response = await fetch(url.toString(), {
      headers: requestHeaders,
      credentials: 'same-origin',
      cache: 'no-store',
    });

    const contentType = response.headers.get('Content-Type') || '';
    if (!contentType.includes('application/json')) {
      throw new Error(sportsPreviewUnavailable);
    }

    const payload = await response.json();
    if (!response.ok || payload?.ok === false) {
      throw new Error(payload?.configured === false ? sportsPreviewNotConfigured : sportsPreviewUnavailable);
    }

    if (!payload?.configured || !payload?.event) {
      throw new Error(sportsPreviewUnavailable);
    }

    sportsEventCache.set(eventId, payload.event);
    return payload.event;
  }

  async function loadSportsPanel(sidebar, season, options = {}) {
    const refs = ensureSportsPanel(sidebar);

    refs.seasonSelect.value = String(season);
    renderSportsStatus(refs, sportsPreviewLoading, 'loading');
    refs.list.innerHTML = '';
    refs.detail.innerHTML = '';

    try {
      const payload = await fetchSportsSchedule(season, options);
      if (!payload?.configured) {
        renderSportsEmpty(refs, sportsPreviewNotConfigured);
        return;
      }

      if (payload?.season) {
        sidebar.dataset.sportsSeason = String(payload.season);
        refs.seasonSelect.value = String(payload.season);
      }

      renderSportsList(refs, payload, refs.list.dataset.selectedEventId || '');
    } catch (error) {
      renderSportsEmpty(refs, resolveErrorMessage(error));
    }
  }

  function initInlineSportsWidgets() {
    document.querySelectorAll('[data-inline-sports-widget]').forEach((widget) => {
      if (!(widget instanceof HTMLElement)) {
        return;
      }

      void loadInlineSportsWidget(widget);
    });
  }

  function initSideCardToggles() {
    document.querySelectorAll('[data-side-card-toggle]').forEach((button) => {
      if (!(button instanceof HTMLButtonElement)) {
        return;
      }

      const card = button.closest('[data-side-card]');
      const body = card?.querySelector('[data-side-card-body]');
      if (!(card instanceof HTMLElement) || !(body instanceof HTMLElement)) {
        return;
      }

      const cardKey = card.dataset.sideCard || '';
      const collapsed = readSideCardState(cardKey);
      syncSideCard(card, button, body, collapsed);

      button.addEventListener('click', () => {
        const nextCollapsed = !card.classList.contains('is-collapsed');
        syncSideCard(card, button, body, nextCollapsed);
        writeSideCardState(cardKey, nextCollapsed);
      });
    });
  }

  function syncSideCard(card, button, body, collapsed) {
    card.classList.toggle('is-collapsed', collapsed);
    body.hidden = collapsed;
    button.setAttribute('aria-expanded', collapsed ? 'false' : 'true');

    const label = button.querySelector('[data-side-card-toggle-label]');
    if (label instanceof HTMLElement) {
      label.textContent = collapsed ? feedSideExpand : feedSideCollapse;
    }
  }

  function readSideCardState(cardKey) {
    if (cardKey === '') {
      return true;
    }

    try {
      const value = window.localStorage.getItem(`boxing-social:side-card:${cardKey}`);
      if (value === null) {
        return true;
      }

      return value === 'collapsed';
    } catch (error) {
      return true;
    }
  }

  function writeSideCardState(cardKey, collapsed) {
    if (cardKey === '') {
      return;
    }

    try {
      window.localStorage.setItem(`boxing-social:side-card:${cardKey}`, collapsed ? 'collapsed' : 'expanded');
    } catch (error) {
      // ignore storage errors
    }
  }

  function ensureInlineSportsWidget(widget) {
    let controls = widget.querySelector('[data-inline-sports-controls]');
    let status = widget.querySelector('[data-inline-sports-status]');
    let list = widget.querySelector('[data-inline-sports-list]');

    if (!(controls instanceof HTMLElement) || !(status instanceof HTMLElement) || !(list instanceof HTMLElement)) {
      setSanitizedHtml(widget, `
        <div class="feed-side-sports__controls" data-inline-sports-controls>
          <label class="feed-side-sports__season">
            <span>${escapeHtml(sportsPreviewSeason)}</span>
            <select data-inline-sports-season></select>
          </label>
          <button type="button" class="feed-side-sports__refresh" data-inline-sports-refresh>${escapeHtml(sportsPreviewRefresh)}</button>
        </div>
        <p class="feed-side-sports__status" data-inline-sports-status></p>
        <div class="feed-side-sports__list" data-inline-sports-list></div>
      `);

      controls = widget.querySelector('[data-inline-sports-controls]');
      status = widget.querySelector('[data-inline-sports-status]');
      list = widget.querySelector('[data-inline-sports-list]');
    }

    const seasonSelect = widget.querySelector('[data-inline-sports-season]');
    const refreshButton = widget.querySelector('[data-inline-sports-refresh]');

    if (
      !(controls instanceof HTMLElement) ||
      !(status instanceof HTMLElement) ||
      !(list instanceof HTMLElement) ||
      !(seasonSelect instanceof HTMLSelectElement) ||
      !(refreshButton instanceof HTMLButtonElement)
    ) {
      throw new Error('Inline sports widget is invalid.');
    }

    if (seasonSelect.options.length === 0) {
      sportsSeasonOptions.forEach((season) => {
        const option = document.createElement('option');
        option.value = String(season);
        option.textContent = String(season);
        seasonSelect.appendChild(option);
      });

      seasonSelect.addEventListener('change', () => {
        const season = parseInt(seasonSelect.value, 10);
        if (Number.isNaN(season)) {
          return;
        }

        widget.dataset.sportsSeason = String(season);
        void loadInlineSportsWidget(widget);
      });

      refreshButton.addEventListener('click', () => {
        const season = parseInt(seasonSelect.value, 10);
        if (Number.isNaN(season)) {
          return;
        }

        widget.dataset.sportsSeason = String(season);
        void loadInlineSportsWidget(widget, { force: true });
      });
    }

    return {
      seasonSelect,
      refreshButton,
      status,
      list,
    };
  }

  async function loadInlineSportsWidget(widget, options = {}) {
    const refs = ensureInlineSportsWidget(widget);
    const season = parseInt(widget.dataset.sportsSeason || String(new Date().getFullYear()), 10);
    const safeSeason = Number.isNaN(season) ? new Date().getFullYear() : season;

    refs.seasonSelect.value = String(safeSeason);
    refs.status.textContent = sportsPreviewLoading;
    refs.status.dataset.tone = 'loading';
    refs.list.innerHTML = '';

    try {
      const payload = await fetchSportsSchedule(safeSeason, options);
      if (!payload?.configured) {
        refs.status.textContent = sportsPreviewNotConfigured;
        refs.status.dataset.tone = 'muted';
        return;
      }

      if (payload?.season) {
        widget.dataset.sportsSeason = String(payload.season);
        refs.seasonSelect.value = String(payload.season);
      }

      const events = Array.isArray(payload?.events) ? payload.events : [];
      if (events.length === 0) {
        refs.status.textContent = sportsPreviewEmpty;
        refs.status.dataset.tone = 'muted';
        return;
      }

      const fragment = document.createDocumentFragment();

      events.forEach((event) => {
        const item = document.createElement('article');
        item.className = 'feed-side-sports__item';

        const title = document.createElement('strong');
        title.className = 'feed-side-sports__item-title';
        title.textContent = event.title || 'MMA fight';

        const meta = document.createElement('p');
        meta.className = 'feed-side-sports__item-meta';
        meta.textContent = [event.dateLabel, event.status].filter(Boolean).join(' • ');

        item.appendChild(title);
        item.appendChild(meta);

        if (typeof event.promotion === 'string' && event.promotion.trim() !== '') {
          const promotion = document.createElement('p');
          promotion.className = 'feed-side-sports__item-detail';
          promotion.textContent = event.promotion;
          item.appendChild(promotion);
        }

        fragment.appendChild(item);
      });

      refs.list.appendChild(fragment);
      refs.status.textContent = payload?.fetchedAt
        ? `${sportsPreviewUpdated} ${new Date(payload.fetchedAt).toLocaleString()}`
        : sportsPreviewSelectEvent;
      refs.status.dataset.tone = 'info';
    } catch (error) {
      refs.status.textContent = resolveErrorMessage(error);
      refs.status.dataset.tone = 'muted';
    }
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

  function escapeHtml(value) {
    return String(value)
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
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
      modal,
    };

    const sidebar = modal.sidebar;
    if (sidebar instanceof HTMLElement) {
      const initialSeason = parseInt(sidebar.dataset.sportsSeason || String(new Date().getFullYear()), 10);
      void loadSportsPanel(sidebar, Number.isNaN(initialSeason) ? new Date().getFullYear() : initialSeason);
    }
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

      const layout = document.createElement('div');
      layout.className = 'post-preview-modal__layout';

      const content = document.createElement('div');
      content.className = 'post-preview-modal__content';

      const sidebar = document.createElement('aside');
      sidebar.className = 'post-preview-modal__sidebar';
      sidebar.setAttribute('data-post-preview-sidebar', '');

      chrome.appendChild(closeButton);
      layout.appendChild(content);
      layout.appendChild(sidebar);
      dialog.appendChild(chrome);
      dialog.appendChild(layout);
      container.appendChild(dialog);
      document.body.appendChild(container);

      container.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof Element)) {
          return;
        }

        if (target.closest('[data-post-preview-close]')) {
          return;
        }

        if (target.closest('.post-preview-modal__dialog')) {
          return;
        }

        closePostPreview();
      });
    }

    const modal = {
      container,
      body: container.querySelector('.post-preview-modal__content'),
      sidebar: container.querySelector('[data-post-preview-sidebar]'),
    };

    if (!(modal.body instanceof HTMLDivElement) || !(modal.sidebar instanceof HTMLElement)) {
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
