(() => {
	'use strict';

	const header = document.querySelector('[data-nice-header]');

	if (!header) {
		return;
	}

	const openButton = header.querySelector('[data-nice-menu-open]');
	const closeButton = header.querySelector('[data-nice-menu-close]');
	const menu = header.querySelector('[data-nice-mobile-menu]');
	const desktopQuery = window.matchMedia('(min-width: 56.25rem)');
	let condensed = false;
	let scrollFrame = 0;
	let returnFocus = null;

	const updateHeader = () => {
		const scrollPosition = window.scrollY;

		if (!condensed && scrollPosition > 96) {
			condensed = true;
			header.classList.add('is-condensed');
		} else if (condensed && scrollPosition < 40) {
			condensed = false;
			header.classList.remove('is-condensed');
		}

		scrollFrame = 0;
	};

	const requestHeaderUpdate = () => {
		if (!scrollFrame) {
			scrollFrame = window.requestAnimationFrame(updateHeader);
		}
	};

	window.addEventListener('scroll', requestHeaderUpdate, { passive: true });
	updateHeader();

	if (!openButton || !closeButton || !menu) {
		return;
	}

	const focusableSelector = [
		'a[href]',
		'button:not([disabled])',
		'[tabindex]:not([tabindex="-1"])',
	].join(',');

	const isOpen = () => menu.dataset.state === 'open';

	const openMenu = () => {
		returnFocus = document.activeElement;
		menu.dataset.state = 'open';
		menu.removeAttribute('inert');
		menu.setAttribute('aria-hidden', 'false');
		openButton.setAttribute('aria-expanded', 'true');
		document.body.classList.add('nice-menu-is-open');
		closeButton.focus();
	};

	const closeMenu = (restoreFocus = true) => {
		menu.dataset.state = 'closed';
		menu.setAttribute('inert', '');
		menu.setAttribute('aria-hidden', 'true');
		openButton.setAttribute('aria-expanded', 'false');
		document.body.classList.remove('nice-menu-is-open');

		if (restoreFocus && returnFocus instanceof HTMLElement) {
			returnFocus.focus();
		}
	};

	const trapFocus = (event) => {
		if (event.key === 'Escape') {
			closeMenu();
			return;
		}

		if (event.key !== 'Tab') {
			return;
		}

		const focusable = [...menu.querySelectorAll(focusableSelector)].filter(
			(element) => !element.hasAttribute('disabled') && element.offsetParent !== null
		);

		if (!focusable.length) {
			return;
		}

		const first = focusable[0];
		const last = focusable[focusable.length - 1];

		if (event.shiftKey && document.activeElement === first) {
			event.preventDefault();
			last.focus();
		} else if (!event.shiftKey && document.activeElement === last) {
			event.preventDefault();
			first.focus();
		}
	};

	openButton.addEventListener('click', openMenu);
	closeButton.addEventListener('click', () => closeMenu());
	menu.addEventListener('keydown', trapFocus);
	menu.querySelectorAll('a[href]').forEach((link) => {
		link.addEventListener('click', () => closeMenu(false));
	});

	desktopQuery.addEventListener('change', (event) => {
		if (event.matches && isOpen()) {
			closeMenu(false);
		}
	});

	closeMenu(false);
})();

