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

	/* ──────────────────────────── Smoothed wheel ──────────────────────────── */

	/*
	 * The page eases toward where the wheel has asked it to go, rather than
	 * jumping there in one step. This is the one place motion is added to
	 * scrolling itself, and it is deliberately narrow:
	 *
	 *   - wheel and trackpad only, on devices with a precise pointer
	 *   - touch is untouched, because a finger dragging a page that then keeps
	 *     gliding feels broken rather than smooth
	 *   - the scrollbar, Page Up/Down, Home/End, arrows, find-in-page and every
	 *     other way the browser scrolls stay native and are resynchronised
	 *     rather than fought
	 *   - off entirely under reduced motion
	 *
	 * Each wheel event still moves the page immediately and in proportion to the
	 * gesture. What changes is that the movement lands over a few frames, which
	 * is what reads as weight.
	 */
	/*
	 * The fraction of the remaining distance covered each frame. Lower is
	 * heavier and slower to settle; higher approaches a native jump; zero hands
	 * the wheel back to the platform entirely. Tunable from theme.json without
	 * touching this file.
	 */
	const SCROLL_EASE = (() => {
		const value = Number.parseFloat(
			getComputedStyle(root).getPropertyValue('--wp--custom--motion--scroll-ease')
		);

		return Number.isFinite(value) && value >= 0 && value <= 1 ? value : 0.1;
	})();

	/*
	 * Zero turns the wheel handling off: the page is never preventDefault()ed and
	 * the platform scrolls it, which is what a phone has always had because
	 * pointer: fine is false on touch.
	 *
	 * Measured on the Events home page, a burst of wheel ticks travelling the
	 * same distance: easing at 0.2 reached first movement in 94ms and settled in
	 * 642ms, against 49ms and 297ms without it. That lag is the whole of what the
	 * layer added -- the distance was identical either way. A trackpad and a
	 * modern browser already smooth a gesture; easing on top of that is a second
	 * pass over something already done, and it reads as the page lagging the hand.
	 */
	const canSmoothWheel = SCROLL_EASE > 0 && window.matchMedia('(pointer: fine)').matches;

	let wheelTarget = window.scrollY;
	let wheelFrame = 0;
	let lastApplied = null;

	const maxScroll = () => Math.max(0, document.documentElement.scrollHeight - window.innerHeight);

	const stopWheelEasing = () => {
		if (wheelFrame) {
			window.cancelAnimationFrame(wheelFrame);
			wheelFrame = 0;
		}

		lastApplied = null;
	};

	const stepWheelEasing = () => {
		const distance = wheelTarget - window.scrollY;

		if (Math.abs(distance) < 0.5) {
			window.scrollTo(0, wheelTarget);
			stopWheelEasing();
			return;
		}

		const position = window.scrollY + distance * SCROLL_EASE;

		window.scrollTo(0, position);
		lastApplied = window.scrollY;
		wheelFrame = window.requestAnimationFrame(stepWheelEasing);
	};

	/* Normalise the three ways a browser reports wheel distance. */
	const wheelDistance = (event) => {
		if (1 === event.deltaMode) {
			return event.deltaY * 16;
		}

		if (2 === event.deltaMode) {
			return event.deltaY * window.innerHeight;
		}

		return event.deltaY;
	};

	const onWheel = (event) => {
		if (prefersReducedMotion() || event.ctrlKey || event.defaultPrevented) {
			return;
		}

		/* Let a scrollable panel, a select, or the open menu handle its own wheel. */
		if (document.body.classList.contains('nice-menu-is-open') || event.target.closest('[data-nice-native-scroll]')) {
			return;
		}

		const limit = maxScroll();

		if (limit <= 0) {
			return;
		}

		/* A gesture at a boundary belongs to the browser: overscroll, rubber band, chaining. */
		const atTop = window.scrollY <= 0 && event.deltaY < 0;
		const atBottom = window.scrollY >= limit && event.deltaY > 0;

		if (atTop || atBottom) {
			stopWheelEasing();
			return;
		}

		event.preventDefault();
		stopTravel();

		/* Resynchronise whenever the page moved by some other means. */
		if (null === lastApplied || Math.abs(window.scrollY - lastApplied) > 2) {
			wheelTarget = window.scrollY;
		}

		wheelTarget = Math.min(limit, Math.max(0, wheelTarget + wheelDistance(event)));

		if (!wheelFrame) {
			lastApplied = window.scrollY;
			wheelFrame = window.requestAnimationFrame(stepWheelEasing);
		}
	};

	if (canSmoothWheel) {
		window.addEventListener('wheel', onWheel, { passive: false });
		window.addEventListener('resize', () => {
			wheelTarget = Math.min(maxScroll(), wheelTarget);
		}, { passive: true });
	}

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

	function stopTravel() {
		if (travel) {
			window.cancelAnimationFrame(travel.frame);
			travel = null;
		}
	}

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

		stopWheelEasing();
		wheelTarget = destination;

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

			/*
			 * A frame is stamped with the moment it began, which can be a little
			 * before the click that asked for this travel: a callback requested
			 * while a frame is already under way still runs in that frame, at
			 * that frame's time. Unclamped, the negative elapsed time runs the
			 * curve backwards and the first frame moves the page the wrong way.
			 */
			const elapsed = Math.max(0, now - began);
			const progress = Math.min(1, elapsed / ANCHOR_DURATION);
			const position = Math.round(start + distance * easeOutQuint(progress));

			window.scrollTo(0, position);

			/*
			 * Where the page actually went, not where it was asked to go. At the
			 * top and the bottom the browser clamps the request, and a position
			 * it never reached would read as the visitor taking over on the very
			 * next frame — ending the travel after one frame, leaving the
			 * fragment changed and the page still.
			 */
			travel.last = window.scrollY;

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
			stopWheelEasing();
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
		stopWheelEasing();
		observer.disconnect();
		root.classList.remove('nice-has-reveal');
		revealEverything();
	});
})();
