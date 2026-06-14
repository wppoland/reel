/**
 * Reel — admin settings enhancements.
 *
 * Progressive enhancement only: the page is fully usable without JS (CSS shows
 * help bubbles on hover/focus, the number input is the source of truth). This
 * adds (1) click/keyboard toggling of help tooltips for touch users, and
 * (2) a live readout that mirrors the zoom-scale range slider into its number
 * field. No jQuery, deferred, in-footer.
 */
(() => {
  'use strict';

  const root = document.querySelector('.reel-admin');

  if (!root) {
    return;
  }

  /* Help tooltips: toggle aria-expanded so touch + keyboard users (who can't
     hover) can open the bubble; close on Escape or outside click. */
  const helpButtons = root.querySelectorAll('.reel-help__btn');

  const closeAll = (except) => {
    helpButtons.forEach((btn) => {
      if (btn !== except) {
        btn.setAttribute('aria-expanded', 'false');
      }
    });
  };

  helpButtons.forEach((btn) => {
    btn.setAttribute('aria-expanded', 'false');

    btn.addEventListener('click', (event) => {
      event.preventDefault();
      const open = btn.getAttribute('aria-expanded') === 'true';
      closeAll(btn);
      btn.setAttribute('aria-expanded', open ? 'false' : 'true');
    });
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      closeAll(null);
    }
  });

  document.addEventListener('click', (event) => {
    if (!event.target.closest('.reel-help')) {
      closeAll(null);
    }
  });

  /* Zoom-scale: keep the range slider, its live readout and the persisted
     number input in sync. The number input is the field that actually saves. */
  const range = root.querySelector('[data-reel-zoom-range]');
  const number = root.querySelector('[data-reel-zoom-number]');
  const output = root.querySelector('[data-reel-zoom-output]');

  if (range && number) {
    const fmt = (value) => `${Number(value).toFixed(2)}×`;

    const sync = (value) => {
      number.value = value;
      range.value = value;
      if (output) {
        output.textContent = fmt(value);
      }
    };

    if (output) {
      output.textContent = fmt(number.value || range.value);
    }

    range.addEventListener('input', () => sync(range.value));
    number.addEventListener('input', () => sync(number.value));
  }
})();
