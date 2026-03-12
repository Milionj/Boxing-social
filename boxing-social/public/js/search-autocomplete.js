document.addEventListener('DOMContentLoaded', () => {
  const inputs = document.querySelectorAll('[data-user-autocomplete]');

  for (const input of inputs) {
    const endpoint = input.getAttribute('data-autocomplete-endpoint');
    const container = input.parentElement?.querySelector('.autocomplete-list');

    if (!endpoint || !container) {
      continue;
    }

    let activeIndex = -1;

    const closeList = () => {
      container.innerHTML = '';
      container.hidden = true;
      activeIndex = -1;
    };

    const openList = (items) => {
      container.innerHTML = '';

      items.forEach((item, index) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'autocomplete-item';
        button.textContent = item.username;
        button.dataset.url = item.url;
        button.addEventListener('click', () => {
          input.value = item.username;
          closeList();
        });

        if (index === activeIndex) {
          button.classList.add('is-active');
        }

        container.appendChild(button);
      });

      container.hidden = items.length === 0;
    };

    input.addEventListener('input', async () => {
      const value = input.value.trim();

      if (value.length < 2) {
        closeList();
        return;
      }

      try {
        const response = await fetch(`${endpoint}?q=${encodeURIComponent(value)}`, {
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const data = await response.json();
        activeIndex = -1;
        openList(Array.isArray(data.items) ? data.items : []);
      } catch {
        closeList();
      }
    });

    input.addEventListener('keydown', (event) => {
      const items = Array.from(container.querySelectorAll('.autocomplete-item'));

      if (items.length === 0) {
        return;
      }

      if (event.key === 'ArrowDown') {
        event.preventDefault();
        activeIndex = (activeIndex + 1) % items.length;
      } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        activeIndex = (activeIndex - 1 + items.length) % items.length;
      } else if (event.key === 'Enter' && activeIndex >= 0) {
        event.preventDefault();
        items[activeIndex].click();
        return;
      } else if (event.key === 'Escape') {
        closeList();
        return;
      } else {
        return;
      }

      items.forEach((item, index) => {
        item.classList.toggle('is-active', index === activeIndex);
      });
    });

    document.addEventListener('click', (event) => {
      if (!container.contains(event.target) && event.target !== input) {
        closeList();
      }
    });
  }
});
