(() => {
	'use strict';

	/**
	 * The NICE motion system.
	 *
	 * Physical scrolling is never touched. There is no wheel handler, no
	 * touchmove handler and no scroll hijacking of any kind: a wheel, a
	 * trackpad, a finger, the scrollbar and the keyboard all move the page at
	 * exactly the speed the operating system says they should.
	 *
	 * What this file does own is the motion a visitor asks for deliberately —
	 * clicking a link to a section — and the one-time reveal of content as it
	 * arrives. Both are opacity and transform only, and both stop entirely when
	 * the visitor has asked for reduced motion.
	 */

	const root = document.documentElement;
	const motionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');

	const prefersReducedMotion = () => motionQuery.matches;

	/* Shared with theme.json so the two never drift apart. */
	const readDuration = (token, fallback) => {
		const value = getComputedStyle(root).getPropertyValue(`--wp--custom--motion--${token}`).trim();
		const parsed = Number.parseFloat(value);

		return Number.isFinite(parsed) ? parsed : fallback;
	};

	const ANCHOR_DURATION = 650;
	const STAGGER_STEP = 70;
	const STAGGER_CAP = 210;

	/* ───────────────────────── Deliberate anchor travel ───────────────────── */

	/**
	 * cubic-bezier(0.22, 1, 0.36, 1), solved for y at a given x.
	 *
	 * The same curve the CSS uses, so a click and a transition feel like one
	 * system rather than two.
	 */
	const easeOutQuint = (t) => 1 - Math.pow(1 - t, 5);

	const headerOffset = () => {
		const header = document.querySelector('[data-nice-header] .nice-nav-shell');

		if (!header) {
			return 16;
		}

		return header.getBoundingClientRect().height + 24;
	};

	let travel = null;

	const stopTravel = () => {
		if (travel) {
			window.cancelAnimationFrame(travel.frame);
			travel = null;
		}
	};

	/**
	 * Move keyboard focus to a destination without moving the page.
	 *
	 * @param {HTMLElement} target Destination element.
	 */
	const focusDestination = (target) => {
		if (!target.hasAttribute('tabindex')) {
			target.setAttribute('tabindex', '-1');
			target.addEventListener('blur', () => target.removeAttribute('tabindex'), { once: true });
		}

		target.focus({ preventScroll: true });
	};

	/**
	 * Travel to a destination, easing only when motion is welcome.
	 *
	 * @param {HTMLElement} target Destination element.
	 */
	const travelTo = (target) => {
		stopTravel();

		const maximum = Math.max(0, document.documentElement.scrollHeight - window.innerHeight);
		const destination = Math.min(
			maximum,
			Math.max(0, window.scrollY + target.getBoundingClientRect().top - headerOffset())
		);

		if (prefersReducedMotion()) {
			window.scrollTo(0, destination);
			focusDestination(target);
			return;
		}

		const start = window.scrollY;
		const distance = destination - start;

		if (Math.abs(distance) < 2) {
			focusDestination(target);
			return;
		}

		const began = performance.now();

		const step = (now) => {
			if (!travel) {
				return;
			}

			/*
			 * If the page is not where this animation last put it, the visitor has
			 * taken over — a wheel, a drag of the scrollbar, a key. Their input
			 * wins immediately. This is why no wheel or touch listener is needed
			 * to stay out of the way.
			 */
			if (travel.last !== null && Math.abs(window.scrollY - travel.last) > 2) {
				travel = null;
				return;
			}

			const elapsed = now - began;
			const progress = Math.min(1, elapsed / ANCHOR_DURATION);
			const position = Math.round(start + distance * easeOutQuint(progress));

			window.scrollTo(0, position);
			travel.last = position;

			if (progress < 1) {
				travel.frame = window.requestAnimationFrame(step);
				return;
			}

			travel = null;
			focusDestination(target);
		};

		travel = { frame: window.requestAnimationFrame(step), last: null };
	};

	/**
	 * Resolve a link that points at a section of this same page.
	 *
	 * @param {HTMLAnchorElement} link Candidate link.
	 * @returns {HTMLElement|null}
	 */
	const samePageTarget = (link) => {
		if (link.target && '_self' !== link.target) {
			return null;
		}

		const url = new URL(link.href, window.location.href);

		if (url.origin !== window.location.origin || url.pathname !== window.location.pathname) {
			return null;
		}

		if (!url.hash || '#' === url.hash) {
			return null;
		}

		try {
			return document.querySelector(url.hash);
		} catch {
			return null;
		}
	};

	document.addEventListener('click', (event) => {
		if (event.defaultPrevented || event.button || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
			return;
		}

		const link = event.target.closest('a[href]');

		if (!link) {
			return;
		}

		const target = samePageTarget(link);

		if (!target) {
			return;
		}

		event.preventDefault();

		/* A history entry, so Back returns where the visitor came from. */
		const hash = new URL(link.href, window.location.href).hash;

		if (window.location.hash !== hash) {
			window.history.pushState(null, '', hash);
		}

		travelTo(target);
	});

	/* Back and Forward move immediately: a restoration is not a deliberate journey. */
	window.addEventListener('popstate', () => {
		stopTravel();

		if (!window.location.hash) {
			return;
		}

		let target = null;

		try {
			target = document.querySelector(window.location.hash);
		} catch {
			target = null;
		}

		if (target) {
			window.scrollTo(0, Math.max(0, window.scrollY + target.getBoundingClientRect().top - headerOffset()));
		}
	});

	/* ────────────────────────────── Reveals ───────────────────────────────── */

	const revealElements = [...document.querySelectorAll('[data-nice-reveal], [data-nice-editorial-reveal]')];

	if (!revealElements.length) {
		return;
	}

	/**
	 * Mark everything visible and stop observing.
	 *
	 * The state content must end in, whatever happens: reduced motion, a missing
	 * IntersectionObserver, or a preference changed halfway down the page.
	 */
	const revealEverything = () => {
		revealElements.forEach((element) => element.classList.add('is-visible'));
	};

	if (prefersReducedMotion() || !('IntersectionObserver' in window)) {
		revealEverything();
		return;
	}

	/* Split an editorial heading into words that can rise independently. */
	const splitIntoWords = (element) => {
		const text = (element.innerText || element.textContent || '').replace(/\s+/g, ' ').trim();
		const words = text.split(/\s+/).filter(Boolean);

		if (!words.length) {
			return;
		}

		const fragment = document.createDocumentFragment();

		words.forEach((word, index) => {
			const mask = document.createElement('span');
			mask.className = 'nice-reveal-mask';
			mask.setAttribute('aria-hidden', 'true');

			const unit = document.createElement('span');
			unit.className = 'nice-reveal-unit';
			unit.textContent = word;
			unit.style.setProperty('--nice-word-delay', `${Math.min(index * STAGGER_STEP, STAGGER_CAP)}ms`);

			mask.appendChild(unit);
			fragment.appendChild(mask);

			if (index < words.length - 1) {
				fragment.appendChild(document.createTextNode(' '));
			}
		});

		/* The readable text stays in the accessibility tree, unsplit. */
		const readable = document.createElement('span');
		readable.className = 'nice-sr-only';
		readable.textContent = text;

		element.textContent = '';
		element.appendChild(readable);
		element.appendChild(fragment);
	};

	document.querySelectorAll('[data-nice-editorial-reveal]').forEach((element) => {
		splitIntoWords(element.querySelector('h1, h2, h3, p') || element);
	});

	/*
	 * Stagger by position among revealing siblings, so a row of three projects
	 * arrives as a row rather than three unrelated events. Capped, because a
	 * long list must not leave its last item waiting.
	 */
	const seen = new Map();

	revealElements.forEach((element) => {
		const parent = element.parentElement;
		const index = seen.get(parent) ?? 0;

		seen.set(parent, index + 1);

		if (index) {
			element.style.setProperty('--nice-reveal-delay', `${Math.min(index * STAGGER_STEP, STAGGER_CAP)}ms`);
		}

		/* The media inside a revealing block settles from a slight scale. */
		element.querySelectorAll('img, video').forEach((media) => media.classList.add('nice-motion-media'));
	});

	const mediaDuration = readDuration('duration-media', 720);

	/**
	 * Bring one element in, and tidy up after it.
	 *
	 * @param {HTMLElement} element Revealing element.
	 */
	const reveal = (element) => {
		element.classList.add('nice-reveal-pending', 'is-visible');

		/* will-change is a promise to the compositor, not a decoration: release it. */
		window.setTimeout(() => {
			element.classList.remove('nice-reveal-pending');
			element.querySelectorAll('.nice-motion-media').forEach((media) => media.classList.add('nice-motion-media-ready'));
		}, mediaDuration + STAGGER_CAP);
	};

	/*
	 * Content that is already on screen when the page loads has not been
	 * scrolled to, so it does not animate: a heading and its primary action are
	 * simply there. Marked before the reveal class goes on the document, so
	 * there is no frame in which they are invisible.
	 */
	const viewportHeight = window.innerHeight;

	revealElements.forEach((element) => {
		const box = element.getBoundingClientRect();

		if (box.top < viewportHeight * 0.9 && box.bottom > 0) {
			element.classList.add('is-visible');
			element.querySelectorAll('.nice-motion-media').forEach((media) => media.classList.add('nice-motion-media-ready'));
		}
	});

	root.classList.add('nice-has-reveal');

	/* One observer for every kind of reveal, rather than several competing. */
	const observer = new IntersectionObserver(
		(entries) => {
			entries.forEach((entry) => {
				if (!entry.isIntersecting) {
					return;
				}

				reveal(entry.target);
				observer.unobserve(entry.target);
			});
		},
		{
			/* Positive, so a section is already moving as the visitor reaches it. */
			rootMargin: '0px 0px 10% 0px',
			threshold: 0,
		}
	);

	const observePending = () => {
		revealElements.forEach((element) => {
			if (!element.classList.contains('is-visible')) {
				observer.observe(element);
			}
		});
	};

	observePending();

	/*
	 * A fast scroll — the scrollbar dragged to the bottom, End, a hard fling —
	 * can carry a section from below the viewport to above it between two
	 * observer callbacks. It never intersects, so no threshold is ever crossed
	 * and the observer is never called: the section would sit at opacity 0
	 * forever, waiting to be scrolled back to. Anything now behind the visitor
	 * is simply shown, with no animation, because there is nothing left to
	 * animate into view.
	 *
	 * This is a passive scroll listener throttled to a frame. It reads position
	 * and never changes it; wheel, touch and scrollbar remain entirely the
	 * browser's.
	 */
	let sweepFrame = 0;

	const sweepPassedSections = () => {
		sweepFrame = 0;

		revealElements.forEach((element) => {
			if (element.classList.contains('is-visible')) {
				return;
			}

			if (element.getBoundingClientRect().bottom < 0) {
				element.classList.add('is-visible');
				element.querySelectorAll('.nice-motion-media').forEach((media) => media.classList.add('nice-motion-media-ready'));
				observer.unobserve(element);
			}
		});
	};

	const requestSweep = () => {
		if (!sweepFrame) {
			sweepFrame = window.requestAnimationFrame(sweepPassedSections);
		}
	};

	window.addEventListener('scroll', requestSweep, { passive: true });

	/* Nothing needs observing while nobody is looking. */
	document.addEventListener('visibilitychange', () => {
		if (document.hidden) {
			stopTravel();
			observer.disconnect();
			return;
		}

		if (!prefersReducedMotion()) {
			observePending();
		}
	});

	/* A preference changed mid-visit takes effect on the next interaction, not the next page. */
	motionQuery.addEventListener('change', (event) => {
		if (!event.matches) {
			return;
		}

		stopTravel();
		observer.disconnect();
		root.classList.remove('nice-has-reveal');
		revealEverything();
	});
})();
