import { initializeApp } from 'https://www.gstatic.com/firebasejs/12.7.0/firebase-app.js';
import { addDoc, collection, getFirestore, serverTimestamp } from 'https://www.gstatic.com/firebasejs/12.7.0/firebase-firestore.js';

const form = document.querySelector('[data-contact-client-form]');

if (form instanceof HTMLFormElement) {
  const successBox = document.querySelector('[data-contact-success]');
  const errorBox = document.querySelector('[data-contact-error]');
  const submitButton = form.querySelector('[data-contact-submit]');

  const config = {
    apiKey: form.dataset.firebaseApiKey || '',
    authDomain: form.dataset.firebaseAuthDomain || '',
    projectId: form.dataset.firebaseProjectId || '',
    storageBucket: form.dataset.firebaseStorageBucket || '',
    messagingSenderId: form.dataset.firebaseMessagingSenderId || '',
    appId: form.dataset.firebaseAppId || '',
    measurementId: form.dataset.firebaseMeasurementId || '',
  };

  const texts = {
    success: form.dataset.contactSuccessMessage || 'Message envoyé avec succès.',
    configError: form.dataset.contactErrorConfig || 'Configuration Firestore incomplète.',
    invalid: form.dataset.contactErrorInvalid || 'Vérifie les champs du formulaire avant envoi.',
    recaptcha: form.dataset.contactErrorRecaptcha || 'Valide le reCAPTCHA pour continuer.',
    send: form.dataset.contactSendLabel || 'Envoyer',
    sending: form.dataset.contactSendingLabel || 'Envoi...',
  };

  const hasConfig = config.apiKey && config.projectId && config.appId;

  const showMessage = (type, message) => {
    if (successBox instanceof HTMLElement) {
      successBox.hidden = type !== 'success';
      successBox.textContent = type === 'success' ? message : '';
    }

    if (errorBox instanceof HTMLElement) {
      errorBox.hidden = type !== 'error';
      errorBox.textContent = type === 'error' ? message : '';
    }
  };

  if (!hasConfig) {
    showMessage('error', texts.configError);
  } else {
    const app = initializeApp(config);
    const db = getFirestore(app);

    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      showMessage('success', '');
      showMessage('error', '');

      const formData = new FormData(form);
      const email = String(formData.get('email') ?? '').trim();
      const subject = String(formData.get('subject') ?? '').trim();
      const message = String(formData.get('message') ?? '').trim();
      const honeypot = String(formData.get('website') ?? '').trim();
      const recaptchaToken = String(formData.get('g-recaptcha-response') ?? '').trim();

      if (honeypot !== '') {
        form.reset();
        showMessage('success', texts.success);
        return;
      }

      if (!email || !subject || message.length < 20) {
        showMessage('error', texts.invalid);
        return;
      }

      if (recaptchaToken === '') {
        showMessage('error', texts.recaptcha);
        return;
      }

      if (submitButton instanceof HTMLButtonElement) {
        submitButton.disabled = true;
        submitButton.textContent = texts.sending;
      }

      try {
        await addDoc(collection(db, 'contact_messages'), {
          email,
          subject,
          message,
          createdAt: serverTimestamp(),
          source: 'boxing-social-contact-form-client-fallback',
        });

        form.reset();
        if (window.grecaptcha && typeof window.grecaptcha.reset === 'function') {
          window.grecaptcha.reset();
        }
        showMessage('success', texts.success);
      } catch (error) {
        const messageText = error instanceof Error ? error.message : 'Erreur inconnue.';
        showMessage('error', 'Envoi impossible vers Firestore : ' + messageText);
      } finally {
        if (submitButton instanceof HTMLButtonElement) {
          submitButton.disabled = false;
          submitButton.textContent = texts.send;
        }
      }
    });
  }
}
