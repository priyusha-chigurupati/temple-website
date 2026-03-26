document.addEventListener('DOMContentLoaded', () => {
  const body = document.body;
  const header = document.querySelector('.site-header');
  const navToggle = document.querySelector('[data-nav-toggle]');
  const primaryNav = document.querySelector('#primary-navigation');

  if (header && navToggle && primaryNav) {
    const setNavState = (isOpen) => {
      header.classList.toggle('is-nav-open', isOpen);
      navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    };

    navToggle.addEventListener('click', () => {
      const isOpen = navToggle.getAttribute('aria-expanded') === 'true';
      setNavState(!isOpen);
    });

    primaryNav.querySelectorAll('a').forEach((link) => {
      link.addEventListener('click', () => setNavState(false));
    });

    window.addEventListener('resize', () => {
      if (window.innerWidth > 820) {
        setNavState(false);
      }
    });
  }

  const adminSidebar = document.querySelector('[data-admin-sidebar]');
  const adminNavToggle = document.querySelector('[data-admin-nav-toggle]');

  if (adminSidebar && adminNavToggle) {
    const setAdminNavState = (isOpen) => {
      adminSidebar.classList.toggle('is-open', isOpen);
      adminNavToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      body.classList.toggle('admin-nav-open', isOpen);
    };

    adminNavToggle.addEventListener('click', () => {
      const isOpen = adminNavToggle.getAttribute('aria-expanded') === 'true';
      setAdminNavState(!isOpen);
    });

    adminSidebar.querySelectorAll('.admin-sidebar__link[href]').forEach((link) => {
      link.addEventListener('click', () => {
        if (window.innerWidth <= 820) {
          setAdminNavState(false);
        }
      });
    });

    window.addEventListener('resize', () => {
      if (window.innerWidth > 820) {
        setAdminNavState(false);
      }
    });
  }

  const setModalState = (modal, isOpen) => {
    if (!modal) {
      return;
    }

    modal.hidden = !isOpen;

    if (isOpen) {
      body.classList.add('modal-open');
      return;
    }

    if (!document.querySelector('.gallery-lightbox:not([hidden]), .gallery-submit-modal:not([hidden])')) {
      body.classList.remove('modal-open');
    }
  };

  const galleryItems = Array.from(document.querySelectorAll('[data-gallery-item]'));
  const lightbox = document.querySelector('[data-gallery-lightbox]');

  if (lightbox && galleryItems.length > 0) {
    const frame = lightbox.querySelector('.gallery-lightbox__frame');
    const image = lightbox.querySelector('[data-gallery-lightbox-image]');
    const title = lightbox.querySelector('[data-gallery-lightbox-title]');
    const category = lightbox.querySelector('[data-gallery-lightbox-category]');
    const prevButton = lightbox.querySelector('[data-gallery-prev]');
    const nextButton = lightbox.querySelector('[data-gallery-next]');
    const zoomButton = lightbox.querySelector('[data-gallery-zoom]');
    let currentIndex = 0;

    const renderItem = (index) => {
      const item = galleryItems[index];
      if (!item || !image || !title || !category) {
        return;
      }

      currentIndex = index;
      image.src = item.dataset.image || '';
      image.alt = item.dataset.alt || '';
      title.textContent = item.dataset.title || '';
      category.textContent = item.dataset.category || '';
      frame?.classList.remove('is-zoomed');
      if (zoomButton) {
        zoomButton.textContent = 'Zoom';
      }
    };

    const openLightbox = (index) => {
      renderItem(index);
      setModalState(lightbox, true);
    };

    const step = (direction) => {
      const total = galleryItems.length;
      const nextIndex = (currentIndex + direction + total) % total;
      renderItem(nextIndex);
    };

    galleryItems.forEach((item, index) => {
      item.addEventListener('click', () => openLightbox(index));
    });

    prevButton?.addEventListener('click', () => step(-1));
    nextButton?.addEventListener('click', () => step(1));
    lightbox.querySelectorAll('[data-gallery-close]').forEach((element) => {
      element.addEventListener('click', () => setModalState(lightbox, false));
    });

    zoomButton?.addEventListener('click', () => {
      const zoomed = frame?.classList.toggle('is-zoomed');
      zoomButton.textContent = zoomed ? 'Fit' : 'Zoom';
    });

    image?.addEventListener('click', () => {
      const zoomed = frame?.classList.toggle('is-zoomed');
      if (zoomButton) {
        zoomButton.textContent = zoomed ? 'Fit' : 'Zoom';
      }
    });

    document.addEventListener('keydown', (event) => {
      if (lightbox.hidden) {
        return;
      }

      if (event.key === 'Escape') {
        setModalState(lightbox, false);
      }

      if (event.key === 'ArrowLeft') {
        step(-1);
      }

      if (event.key === 'ArrowRight') {
        step(1);
      }
    });
  }

  const submitModal = document.querySelector('[data-gallery-submit-modal]');
  const openSubmitButton = document.querySelector('[data-gallery-open-submit]');

  if (submitModal) {
    openSubmitButton?.addEventListener('click', () => setModalState(submitModal, true));

    submitModal.querySelectorAll('[data-gallery-submit-close]').forEach((element) => {
      element.addEventListener('click', () => setModalState(submitModal, false));
    });

    document.addEventListener('keydown', (event) => {
      if (submitModal.hidden) {
        return;
      }

      if (event.key === 'Escape') {
        setModalState(submitModal, false);
      }
    });
  }
});
