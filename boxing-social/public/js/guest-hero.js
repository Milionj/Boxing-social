document.addEventListener('DOMContentLoaded', function () {
  const title = document.querySelector('[data-hero-title]');

  if (!title) {
    return;
  }

  const lines = Array.from(title.querySelectorAll('.guest-title__line'));
  let delayIndex = 0;

  // On découpe chaque ligne en mots pour créer un reveal progressif
  // type GSAP, sans dépendance externe supplémentaire.
  lines.forEach(function (line) {
    const words = line.textContent.trim().split(/\s+/);
    line.textContent = '';

    words.forEach(function (word, wordIndex) {
      const token = document.createElement('span');
      token.className = 'guest-title__word';
      token.textContent = word;
      token.style.transitionDelay = (delayIndex * 70) + 'ms';
      line.appendChild(token);

      if (wordIndex < words.length - 1) {
        line.appendChild(document.createTextNode(' '));
      }

      delayIndex += 1;
    });
  });

  requestAnimationFrame(function () {
    title.classList.add('is-visible');
  });
});
