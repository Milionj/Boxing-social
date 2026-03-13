import { initializeApp } from 'https://www.gstatic.com/firebasejs/12.7.0/firebase-app.js';
import { getFirestore, collection, addDoc, serverTimestamp } from 'https://www.gstatic.com/firebasejs/12.7.0/firebase-firestore.js';

const form = document.querySelector('[data-contact-form]');

if (form) {
  const successBox = document.querySelector('[data-contact-success]');
  const errorBox = document.querySelector('[data-contact-error]');
  const submitButton = document.querySelector('[data-contact-submit]');
  const config = window.BOXING_SOCIAL_FIREBASE_CONFIG ?? {};

  const hasConfig = config.apiKey && config.projectId && config.appId;

  const showMessage = (type, message) => {
    if (successBox) {
      successBox.hidden = type !== 'success';
      successBox.textContent = type === 'success' ? message : '';
    }

    if (errorBox) {
      errorBox.hidden = type !== 'error';
      errorBox.textContent = type === 'error' ? message : '';
    }
  };

  if (!hasConfig) {
    showMessage('error', 'Configuration Firestore incomplete. Verifie les variables FIREBASE dans .env.');
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

      if (!email || !subject || message.length < 10) {
        showMessage('error', 'Verifie les champs du formulaire avant envoi.');
        return;
      }

      if (submitButton instanceof HTMLButtonElement) {
        submitButton.disabled = true;
        submitButton.textContent = 'Envoi...';
      }

      try {
        await addDoc(collection(db, 'contact_messages'), {
          email,
          subject,
          message,
          createdAt: serverTimestamp(),
          source: 'boxing-social-contact-form',
        });

        form.reset();
        showMessage('success', 'Message envoye avec succes.');
      } catch (error) {
        const messageText = error instanceof Error ? error.message : 'Erreur inconnue.';
        showMessage('error', 'Envoi impossible vers Firestore: ' + messageText);
      } finally {
        if (submitButton instanceof HTMLButtonElement) {
          submitButton.disabled = false;
          submitButton.textContent = 'Envoyer';
        }
      }
    });
  }
}
