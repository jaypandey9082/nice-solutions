(() => {
  'use strict';

  // Load deferred. The modal must sit outside the header, main and footer.
  // Keep normal header navigation visible in HTML; CSS enables the trigger only
  // after a complete, inert-capable dialog has been found. No heading rewriting.
  // Integration QA: verify keyboard/Escape, anchor focus and admin-bar offsets,
  // close/reopen during exit, crossing 900px, Back/Forward with an open menu,
  // live reduced motion, missing observers and JS disabled. Repeat in the
  // assembled theme: isolated tests cannot verify its stacking/crop/layout CSS.
  const init = () => {
    const root = document.documentElement;
    const body = document.body;
    const header = document.querySelector('[data-site-header]');
    const menu = document.getElementById('nice-menu');
    const toggles = [...document.querySelectorAll('[data-menu-toggle][aria-controls="nice-menu"]')];
    const closeButton = menu?.querySelector('[data-menu-close]');
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
    const desktop = window.matchMedia('(min-width: 900px)');
    const reveals = [...document.querySelectorAll('[data-reveal]')];
    let observer;
    let menuOpen = false;
    let menuRevision = 0;
    let opener;
    let inertState = [];
    let scrollState;
    let scrollFrame;
    let anchorTimer;
    let anchorFrame;
    let userInteracted = false;

    const reveal = (element) => {
      element.classList.add('is-visible');
      observer?.unobserve(element);
    };
    const revealViewport = () => {
      reveals.forEach((element) => {
        if (element.getBoundingClientRect().top < window.innerHeight) reveal(element);
      });
    };
    const revealTarget = (target) => {
      for (let element = target; element; element = element.parentElement) {
        if (element.matches('[data-reveal]')) reveal(element);
      }
    };
    const focusWithoutScroll = (target) => {
      if (!target) return;
      revealTarget(target);
      const temporary = !target.hasAttribute('tabindex') &&
        !target.matches('a[href], button, input, select, textarea, summary, [contenteditable="true"]');
      if (temporary) {
        target.setAttribute('tabindex', '-1');
        target.addEventListener('blur', () => target.removeAttribute('tabindex'), { once: true });
      }
      target.focus({ preventScroll: true });
    };
    const updateHeader = () => {
      header?.classList.toggle('is-scrolled', (scrollState?.y ?? window.scrollY) > 24);
      const bar = document.getElementById('wpadminbar');
      const obstruction = (element) => {
        if (!element || !['fixed', 'sticky'].includes(getComputedStyle(element).position)) return 0;
        const rect = element.getBoundingClientRect();
        return rect.top < window.innerHeight && rect.bottom > 0 ? rect.bottom : 0;
      };
      root.style.setProperty('--nice-anchor-offset', `${Math.ceil(Math.max(obstruction(header), obstruction(bar)) + 16)}px`);
    };
    const stopAnchorScroll = () => {
      window.clearTimeout(anchorTimer);
      window.cancelAnimationFrame(anchorFrame);
      root.classList.remove('is-anchor-scrolling');
    };
    const startAnchorScroll = () => {
      stopAnchorScroll();
      if (reduced.matches) return;
      root.classList.add('is-anchor-scrolling');
      // scrollend handles normal completion; the timer covers engines without it
      // and clicks whose destination was already in view.
      anchorTimer = window.setTimeout(stopAnchorScroll, 2000);
    };
    const fragmentTarget = (hash) => {
      if (!hash || hash === '#') return root;
      let id;
      try { id = decodeURIComponent(hash.slice(1)); } catch { return null; }
      return document.getElementById(id) || document.getElementsByName(id)[0] ||
        (id.toLowerCase() === 'top' ? root : null);
    };
    const samePageTarget = (link) => {
      const raw = link.getAttribute('href');
      if (!raw || !raw.includes('#') || link.hasAttribute('download') ||
          (link.target && link.target !== '_self')) return null;
      let url;
      try { url = new URL(link.href, window.location.href); } catch { return null; }
      if (url.origin !== location.origin || url.pathname !== location.pathname ||
          url.search !== location.search) return null;
      return fragmentTarget(url.hash);
    };

    const backgrounds = [...document.querySelectorAll('[data-site-header], main#main-content, footer')];
    const dialogReady = menu && closeButton && toggles.length && 'inert' in HTMLElement.prototype &&
      !backgrounds.some((element) => element.contains(menu));
    const focusables = () => [...menu.querySelectorAll(
      'a[href], button, input, select, textarea, summary, [tabindex], [contenteditable="true"]'
    )].filter((element) => element.tabIndex >= 0 && !element.matches(':disabled') &&
      !element.closest('[inert]') && element.getClientRects().length &&
      getComputedStyle(element).visibility !== 'hidden');

    const unlock = () => {
      inertState.forEach(([element, wasInert]) => { element.inert = wasInert; });
      inertState = [];
      if (!scrollState) return;
      const saved = scrollState;
      scrollState = undefined;
      saved.styles.forEach(([property, value, priority]) => {
        if (value) body.style.setProperty(property, value, priority);
        else body.style.removeProperty(property);
      });
      window.scrollTo({ left: saved.x, top: saved.y, behavior: 'instant' });
    };
    const finishClose = (restoreFocus) => {
      menu.hidden = true;
      menu.classList.remove('is-menu-closing');
      body.classList.remove('is-menu-open');
      unlock();
      if (restoreFocus && opener?.isConnected && opener.getClientRects().length) focusWithoutScroll(opener);
      updateHeader();
    };
    const closeMenu = ({ immediate = false, restoreFocus = true } = {}) => {
      if (!dialogReady || (!menuOpen && menu.hidden)) return;
      menuOpen = false;
      const revision = ++menuRevision;
      toggles.forEach((toggle) => toggle.setAttribute('aria-expanded', 'false'));
      menu.classList.add('is-menu-closing');
      if (immediate || reduced.matches || !menu.getAnimations) {
        finishClose(restoreFocus);
        return;
      }
      // Flush the exit animation before collecting its completion promises.
      void menu.offsetWidth;
      Promise.allSettled(menu.getAnimations().map((animation) => animation.finished)).then(() => {
        if (revision === menuRevision && !menuOpen) finishClose(restoreFocus);
      });
    };
    const openMenu = (toggle) => {
      if (!dialogReady || desktop.matches || menuOpen) return;
      stopAnchorScroll();
      ++menuRevision;
      opener = toggle;
      menuOpen = true;
      if (!scrollState) {
        const properties = ['position', 'top', 'left', 'width', 'overflow', 'padding-right'];
        scrollState = {
          x: window.scrollX, y: window.scrollY,
          styles: properties.map((property) => [property, body.style.getPropertyValue(property), body.style.getPropertyPriority(property)])
        };
        const gutter = window.innerWidth - root.clientWidth;
        const padding = parseFloat(getComputedStyle(body).paddingRight) || 0;
        Object.entries({ position: 'fixed', top: `${-scrollState.y}px`, left: `${-scrollState.x}px`,
          width: '100%', overflow: 'hidden', 'padding-right': `${padding + gutter}px`
        }).forEach(([property, value]) => body.style.setProperty(property, value));
        inertState = backgrounds.map((element) => [element, element.inert]);
        backgrounds.forEach((element) => { element.inert = true; });
      }
      menu.hidden = false;
      menu.classList.remove('is-menu-closing');
      body.classList.add('is-menu-open');
      toggles.forEach((button) => button.setAttribute('aria-expanded', 'true'));
      focusWithoutScroll(closeButton);
    };

    if (dialogReady) {
      menu.hidden = true;
      menu.setAttribute('role', 'dialog');
      menu.setAttribute('aria-modal', 'true');
      if (!menu.hasAttribute('aria-label') && !menu.hasAttribute('aria-labelledby')) menu.setAttribute('aria-label', 'Navigation');
      toggles.forEach((toggle) => {
        toggle.hidden = false;
        toggle.setAttribute('aria-expanded', 'false');
        toggle.addEventListener('click', () => menuOpen ? closeMenu() : openMenu(toggle));
      });
      menu.querySelectorAll('[data-menu-close]').forEach((button) => button.addEventListener('click', () => closeMenu()));
      root.classList.add('has-navigation');
      document.addEventListener('keydown', (event) => {
        if (menu.hidden) return;
        if (event.key === 'Escape') { event.preventDefault(); closeMenu(); }
        if (event.key !== 'Tab') return;
        const items = focusables();
        const first = items[0] || closeButton;
        const last = items[items.length - 1] || closeButton;
        if (!menu.contains(document.activeElement) || (event.shiftKey && document.activeElement === first)) {
          event.preventDefault(); focusWithoutScroll(event.shiftKey ? last : first);
        } else if (!event.shiftKey && document.activeElement === last) {
          event.preventDefault(); focusWithoutScroll(first);
        }
      });
      document.addEventListener('focusin', (event) => {
        if (!menu.hidden && !menu.contains(event.target)) focusWithoutScroll(closeButton);
      });
    }

    const prepareMotion = () => {
      observer?.disconnect();
      root.classList.remove('has-motion');
      if (reduced.matches || !('IntersectionObserver' in window)) {
        reveals.forEach(reveal);
        return;
      }
      try {
        observer = new IntersectionObserver((entries) => {
          entries.forEach((entry) => { if (entry.isIntersecting) reveal(entry.target); });
        }, { rootMargin: '0px 0px -8% 0px', threshold: 0 });
        revealViewport();
        reveals.filter((element) => !element.classList.contains('is-visible')).forEach((element) => observer.observe(element));
        root.classList.add('has-motion');
      } catch {
        observer?.disconnect();
        reveals.forEach(reveal);
      }
    };
    document.addEventListener('focusin', (event) => revealTarget(event.target));
    document.addEventListener('click', (event) => {
      if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
      const link = event.target.closest('a[href]');
      if (!link) return;
      const target = samePageTarget(link);
      if (dialogReady && !menu.hidden && menu.contains(link)) {
        closeMenu({ immediate: true, restoreFocus: !target });
      }
      if (!target) return;
      updateHeader();
      startAnchorScroll();
      focusWithoutScroll(target);
      // The browser performs the default fragment navigation and history update.
    });
    const handleScroll = () => {
      if (scrollFrame) return;
      scrollFrame = requestAnimationFrame(() => { scrollFrame = undefined; updateHeader(); });
    };
    window.addEventListener('scroll', handleScroll, { passive: true });
    window.addEventListener('scrollend', () => {
      anchorFrame = requestAnimationFrame(stopAnchorScroll);
    });
    window.addEventListener('resize', () => {
      if (dialogReady && desktop.matches && !menu.hidden) {
        const active = document.activeElement;
        const href = active?.closest('a[href]')?.getAttribute('href');
        closeMenu({ immediate: true, restoreFocus: false });
        const links = [...(header?.querySelectorAll('a[href]') || [])];
        focusWithoutScroll(links.find((link) => link.getAttribute('href') === href && link.getClientRects().length) ||
          links.find((link) => link.getClientRects().length) || document.getElementById('main-content'));
      }
      updateHeader();
    });
    if ('ResizeObserver' in window) {
      const sizeObserver = new ResizeObserver(updateHeader);
      [header, document.getElementById('wpadminbar')].filter(Boolean).forEach((element) => sizeObserver.observe(element));
    }
    ['pointerdown', 'wheel', 'touchstart', 'keydown'].forEach((type) => {
      window.addEventListener(type, () => { userInteracted = true; }, { once: true, passive: true });
    });
    const onPreferenceChange = () => {
      stopAnchorScroll();
      if (dialogReady && !menuOpen && !menu.hidden) closeMenu({ immediate: true });
      prepareMotion();
    };
    if (reduced.addEventListener) reduced.addEventListener('change', onPreferenceChange);
    else reduced.addListener(onPreferenceChange);

    const cleanUpHistory = () => {
      stopAnchorScroll();
      closeMenu({ immediate: true, restoreFocus: Boolean(menu?.contains(document.activeElement)) });
      updateHeader();
      // Revealed items stay revealed, including restored views after fast scroll.
      revealViewport();
      requestAnimationFrame(revealViewport);
    };
    window.addEventListener('pagehide', cleanUpHistory);
    window.addEventListener('pageshow', cleanUpHistory);
    window.addEventListener('popstate', cleanUpHistory);
    window.addEventListener('hashchange', () => {
      const target = fragmentTarget(location.hash);
      if (target) revealTarget(target);
    });
    updateHeader();
    prepareMotion();
    const alignInitialFragment = () => {
      if (userInteracted || !location.hash || performance.getEntriesByType('navigation')[0]?.type === 'back_forward') return;
      updateHeader();
      const target = fragmentTarget(location.hash);
      if (target) {
        revealTarget(target);
        target.scrollIntoView({ block: 'start', behavior: 'instant' });
      }
    };
    if (document.readyState === 'complete') alignInitialFragment();
    else window.addEventListener('load', alignInitialFragment, { once: true });
  };

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, { once: true });
  else init();
})();
