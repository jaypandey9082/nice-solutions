(() => {
	'use strict';

	const header = document.querySelector('[data-nice-header]');

	if (!header) {
		return;
	}

	const openButton = header.querySelector('[data-nice-menu-open]');
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

	if (!openButton || !menu) {
		return;
	}

	const isOpen = () => menu.dataset.state === 'open';

	/*
	 * The bar's toggle is the only control: three rules while shut, a cross
	 * while open. That rules out making the background inert, because the
	 * header is part of the background — inerting it would disable the very
	 * button a visitor reaches for to close the panel. So this is a disclosure
	 * rather than a modal, and the panel says nothing about being one.
	 */
	const openMenu = () => {
		menu.dataset.state = 'open';
		menu.removeAttribute('inert');
		menu.setAttribute('aria-hidden', 'false');
		openButton.setAttribute('aria-expanded', 'true');
		openButton.setAttribute('aria-label', openButton.dataset.labelClose || 'Close menu');
		document.body.classList.add('nice-menu-is-open');
	};

	const closeMenu = (restoreFocus = true) => {
		menu.dataset.state = 'closed';
		menu.setAttribute('inert', '');
		menu.setAttribute('aria-hidden', 'true');
		openButton.setAttribute('aria-expanded', 'false');
		openButton.setAttribute('aria-label', openButton.dataset.labelOpen || 'Open menu');
		document.body.classList.remove('nice-menu-is-open');

		/*
		 * Focus belongs on the toggle either way: it is where the visitor
		 * pressed, and it is what they press again to reopen.
		 */
		if (restoreFocus) {
			openButton.focus();
		}
	};

	openButton.addEventListener('click', () => {
		if (isOpen()) {
			closeMenu();
			return;
		}

		openMenu();
		openButton.focus();
	});

	document.addEventListener('keydown', (event) => {
		if ('Escape' === event.key && isOpen()) {
			closeMenu();
		}
	});

	/* Tapping the page behind the panel dismisses it, as a dropdown should. */
	document.addEventListener('click', (event) => {
		if (!isOpen() || menu.contains(event.target) || openButton.contains(event.target)) {
			return;
		}

		closeMenu(false);
	});

	menu.querySelectorAll('a[href]').forEach((link) => {
		link.addEventListener('click', () => closeMenu(false));
	});

	desktopQuery.addEventListener('change', (event) => {
		if (event.matches && isOpen()) {
			closeMenu(false);
		}
	});

	closeMenu(false);

	/*
	 * The gateway's Contact disclosure.
	 *
	 * A disclosure rather than a menu widget: the two children are ordinary
	 * links, so the button only needs to say whether they are showing. Present
	 * on the gateway alone, hence the early return everywhere else.
	 */
	const connect = document.querySelector('[data-nice-connect]');

	if (!connect) {
		return;
	}

	const connectToggle = connect.querySelector('[data-nice-connect-toggle]');
	const connectMenu = connect.querySelector('[data-nice-connect-menu]');

	if (!connectToggle || !connectMenu) {
		return;
	}

	const setConnect = (expanded) => {
		connectToggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
		connectMenu.hidden = !expanded;
	};

	connectToggle.addEventListener('click', () => {
		setConnect(connectToggle.getAttribute('aria-expanded') !== 'true');
	});

	/* Clicking away closes it, which is what a disclosure in a bar should do. */
	document.addEventListener('click', (event) => {
		if (!connect.contains(event.target)) {
			setConnect(false);
		}
	});

	connect.addEventListener('keydown', (event) => {
		if ('Escape' !== event.key) {
			return;
		}

		setConnect(false);
		connectToggle.focus();
	});

	/* Leaving the disclosure by keyboard closes it too. */
	connect.addEventListener('focusout', (event) => {
		if (!connect.contains(event.relatedTarget)) {
			setConnect(false);
		}
	});
})();
