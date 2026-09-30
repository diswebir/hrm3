(() => {
  'use strict';
  const sidebar = document.getElementById('sidebar');
  const overlay = document.querySelector('.sidebar-overlay');
  const closeSidebar = () => {
    sidebar?.classList.remove('is-open');
    overlay?.classList.remove('is-visible');
    document.body.classList.remove('sidebar-open');
  };
  document.querySelectorAll('[data-sidebar-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
      sidebar?.classList.add('is-open');
      overlay?.classList.add('is-visible');
      document.body.classList.add('sidebar-open');
    });
  });
  document.querySelectorAll('[data-sidebar-close]').forEach((button) => button.addEventListener('click', closeSidebar));
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') closeSidebar();
    if (event.key === '/' && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName)) {
      const search = document.querySelector('.global-search input');
      if (search) { event.preventDefault(); search.focus(); }
    }
  });

  document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
      const input = button.parentElement?.querySelector('input');
      if (!input) return;
      const visible = input.type === 'password';
      input.type = visible ? 'text' : 'password';
      button.textContent = visible ? 'پنهان' : 'نمایش';
      button.setAttribute('aria-label', visible ? 'پنهان کردن گذرواژه' : 'نمایش گذرواژه');
    });
  });

  document.querySelectorAll('[data-confirm]').forEach((element) => {
    element.addEventListener('submit', (event) => {
      const message = element.getAttribute('data-confirm') || 'این عملیات انجام شود؟';
      if (!window.confirm(message)) event.preventDefault();
    });
  });

  document.querySelectorAll('[data-dismiss]').forEach((button) => {
    button.addEventListener('click', () => button.closest('.toast')?.remove());
  });
  document.querySelectorAll('.toast:not(.error)').forEach((toast) => {
    window.setTimeout(() => toast.remove(), 6500);
  });

  const featureSearch = document.querySelector('[data-feature-search]');
  if (featureSearch) {
    featureSearch.addEventListener('input', () => {
      const query = featureSearch.value.trim().toLocaleLowerCase();
      document.querySelectorAll('[data-feature-group]').forEach((group) => {
        let visibleCount = 0;
        group.querySelectorAll('[data-feature-item]').forEach((item) => {
          const match = !query || item.textContent.toLocaleLowerCase().includes(query);
          item.hidden = !match;
          if (match) visibleCount++;
        });
        group.hidden = visibleCount === 0;
      });
    });
  }

  document.querySelectorAll('input[type="file"]').forEach((input) => {
    input.addEventListener('change', () => {
      const file = input.files?.[0];
      if (file && file.size > 8 * 1024 * 1024) {
        window.alert('حجم فایل انتخاب‌شده بیش از ۸ مگابایت است.');
        input.value = '';
      }
    });
  });

  document.querySelectorAll('[data-confirm-unsaved]').forEach((form) => {
    let dirty = false;
    form.addEventListener('input', () => { dirty = true; });
    form.addEventListener('change', () => { dirty = true; });
    form.addEventListener('submit', () => { dirty = false; });
    window.addEventListener('beforeunload', (event) => {
      if (dirty) {
        event.preventDefault();
        event.returnValue = '';
      }
    });
  });
})();
