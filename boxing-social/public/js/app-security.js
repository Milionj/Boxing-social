(() => {
  const body = document.body;
  if (!(body instanceof HTMLBodyElement)) {
    return;
  }

  const AJAX_FORM_SELECTORS = [
    '[data-like-form]',
    '[data-comment-form]',
    '[data-comment-delete-form]',
    '[data-interest-form]',
    '[data-notification-read-form]',
    '[data-notifications-mark-all-form]',
    '[data-friend-send-form]',
    '[data-friend-accept-form]',
    '[data-friend-decline-form]',
    '[data-friend-cancel-form]',
    '[data-friend-remove-form]',
    '[data-message-send-form]',
    '[data-messages-open-form]',
  ];

  const AUTH_STORAGE_KEYS = [
    'jwt',
    'JWT',
    'token',
    'access_token',
    'refresh_token',
    'auth_token',
    'remember_token',
  ];

  window.BoxingSocialSecurity = Object.freeze({
    sanitizeHtml,
    setSanitizedHtml,
  });

  initBodyTheme();
  initCookieNotice();
  initScrollTop();
  initNotificationsDrawer();
  initSubmitProtection();
  cleanupLogoutArtifacts();

  window.addEventListener('pageshow', () => {
    document.querySelectorAll('form[data-submitting="1"]').forEach((form) => {
      if (form instanceof HTMLFormElement) {
        unlockForm(form);
      }
    });
  });

  function initBodyTheme() {
    const themeSource = document.querySelector('[data-body-theme]');
    if (!(themeSource instanceof HTMLElement)) {
      return;
    }

    const theme = String(themeSource.dataset.bodyTheme || '').trim();
    if (theme !== '') {
      body.dataset.theme = theme;
    }
  }

  function initCookieNotice() {
    const notice = document.querySelector('[data-cookie-notice]');
    const dismissButton = document.querySelector('[data-cookie-notice-dismiss]');
    if (!(notice instanceof HTMLElement) || !(dismissButton instanceof HTMLButtonElement)) {
      return;
    }

    dismissButton.addEventListener('click', () => {
      const cookieName = notice.dataset.cookieName || '';
      if (cookieName !== '') {
        const secureFlag = window.location.protocol === 'https:' ? '; Secure' : '';
        document.cookie = `${encodeURIComponent(cookieName)}=1; Max-Age=${180 * 24 * 60 * 60}; Path=/; SameSite=Lax${secureFlag}`;
      }

      notice.remove();
    });
  }

  function initScrollTop() {
    const button = document.querySelector('[data-scroll-top]');
    if (!(button instanceof HTMLAnchorElement)) {
      return;
    }

    const threshold = 260;
    const syncVisibility = () => {
      button.classList.toggle('is-visible', window.scrollY > threshold);
    };

    syncVisibility();
    window.addEventListener('scroll', syncVisibility, { passive: true });
  }

  function initNotificationsDrawer() {
    const toggle = document.querySelector('[data-notifications-toggle]');
    const drawer = document.querySelector('[data-notifications-drawer]');

    if (!(toggle instanceof HTMLAnchorElement) || !(drawer instanceof HTMLElement)) {
      return;
    }

    const closeButton = drawer.querySelector('[data-notifications-close]');
    const closeUrl = drawer.dataset.closeUrl || '/';

    const syncState = (isOpen, syncUrl) => {
      drawer.classList.toggle('is-open', isOpen);
      drawer.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
      toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      toggle.classList.toggle('is-open', isOpen);
      document.body.classList.toggle('notifications-drawer-open', isOpen);

      if (syncUrl) {
        history.replaceState(null, '', isOpen ? toggle.getAttribute('href') || '/' : closeUrl);
      }
    };

    if (drawer.dataset.open === '1') {
      syncState(true, false);
    }

    toggle.addEventListener('click', (event) => {
      if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0) {
        return;
      }

      event.preventDefault();
      syncState(!drawer.classList.contains('is-open'), true);
    });

    if (closeButton instanceof HTMLButtonElement) {
      closeButton.addEventListener('click', () => {
        syncState(false, true);
      });
    }

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && drawer.classList.contains('is-open')) {
        syncState(false, true);
      }
    });

    document.addEventListener('click', (event) => {
      if (!drawer.classList.contains('is-open')) {
        return;
      }

      const target = event.target;
      if (!(target instanceof Node)) {
        return;
      }

      if (drawer.contains(target) || toggle.contains(target)) {
        return;
      }

      syncState(false, true);
    });
  }

  function initSubmitProtection() {
    document.addEventListener(
      'submit',
      (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) {
          return;
        }

        const confirmMessage = resolveConfirmMessage(form, event.submitter);
        if (confirmMessage !== '' && !window.confirm(confirmMessage)) {
          event.preventDefault();
          return;
        }

        if (isAjaxManagedForm(form)) {
          return;
        }

        if (form.dataset.submitting === '1') {
          event.preventDefault();
          return;
        }

        lockForm(form);
      },
      true
    );
  }

  function isAjaxManagedForm(form) {
    return AJAX_FORM_SELECTORS.some((selector) => form.matches(selector));
  }

  function resolveConfirmMessage(form, submitter) {
    if (submitter instanceof HTMLElement) {
      const submitterMessage = String(submitter.getAttribute('data-confirm') || '').trim();
      if (submitterMessage !== '') {
        return submitterMessage;
      }
    }

    return String(form.getAttribute('data-confirm') || '').trim();
  }

  function lockForm(form) {
    form.dataset.submitting = '1';

    const fields = form.querySelectorAll('button, input[type="submit"], input[type="button"]');
    fields.forEach((field) => {
      if (!(field instanceof HTMLButtonElement || field instanceof HTMLInputElement)) {
        return;
      }

      if (field.disabled) {
        return;
      }

      field.dataset.wasDisabled = '0';
      field.disabled = true;
      field.setAttribute('aria-disabled', 'true');
    });
  }

  function unlockForm(form) {
    delete form.dataset.submitting;

    const fields = form.querySelectorAll('[data-was-disabled="0"]');
    fields.forEach((field) => {
      if (!(field instanceof HTMLButtonElement || field instanceof HTMLInputElement)) {
        return;
      }

      field.disabled = false;
      field.removeAttribute('aria-disabled');
      delete field.dataset.wasDisabled;
    });
  }

  function cleanupLogoutArtifacts() {
    if (body.dataset.logoutCleanup !== '1') {
      return;
    }

    AUTH_STORAGE_KEYS.forEach((key) => {
      try {
        window.localStorage.removeItem(key);
      } catch (error) {
        // ignore storage failures
      }

      try {
        window.sessionStorage.removeItem(key);
      } catch (error) {
        // ignore storage failures
      }

      expireCookie(key);
    });
  }

  function expireCookie(name) {
    const secureFlag = window.location.protocol === 'https:' ? '; Secure' : '';
    document.cookie = `${encodeURIComponent(name)}=; Max-Age=0; Path=/; SameSite=Lax${secureFlag}`;
  }

  function sanitizeHtml(html) {
    if (window.DOMPurify && typeof window.DOMPurify.sanitize === 'function') {
      return window.DOMPurify.sanitize(html, {
        USE_PROFILES: { html: true },
      });
    }

    const template = document.createElement('template');
    template.textContent = String(html);
    return template.innerHTML;
  }

  function setSanitizedHtml(element, html) {
    if (!(element instanceof Element)) {
      return;
    }

    element.innerHTML = sanitizeHtml(html);
  }
})();
