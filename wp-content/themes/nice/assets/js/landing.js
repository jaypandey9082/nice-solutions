(() => {
	'use strict';

	const revealElements = [...document.querySelectorAll('[data-nice-reveal]')];
	const editorialElements = [...document.querySelectorAll('[data-nice-editorial-reveal]')];

	if (!revealElements.length && !editorialElements.length) {
		return;
	}

	const motionPreference = window.matchMedia('(prefers-reduced-motion: reduce)');
	const reducedMotion = motionPreference.matches;

	if (reducedMotion || !('IntersectionObserver' in window)) {
		revealElements.forEach((el) => el.classList.add('is-visible'));
		editorialElements.forEach((el) => el.classList.add('is-visible'));
		return;
	}

	document.documentElement.classList.add('nice-has-reveal');

	/* ── Standard reveal ── */
	const revealObserver = new IntersectionObserver(
		(entries) => {
			entries.forEach((entry) => {
				if (!entry.isIntersecting) {
					return;
				}
				entry.target.classList.add('is-visible');
				revealObserver.unobserve(entry.target);
			});
		},
		{
			rootMargin: '0px 0px -8% 0px',
			threshold: 0.08,
		}
	);

	revealElements.forEach((el) => revealObserver.observe(el));

	/* ── Editorial reveal (word-by-word) ── */
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
			unit.style.transitionDelay = `${Math.min(index * 60, 180)}ms`;

			mask.appendChild(unit);
			fragment.appendChild(mask);

			if (index < words.length - 1) {
				fragment.appendChild(document.createTextNode(' '));
			}
		});

		/* Preserve accessible text */
		const srOnly = document.createElement('span');
		srOnly.className = 'nice-sr-only';
		srOnly.textContent = text;

		element.textContent = '';
		element.appendChild(srOnly);
		element.appendChild(fragment);
	};

	editorialElements.forEach((el) => {
		/* Only split direct text content inside the element or its first text-bearing child */
		const target = el.querySelector('h1, h2, h3, p') || el;
		splitIntoWords(target);
	});

	const editorialObserver = new IntersectionObserver(
		(entries) => {
			entries.forEach((entry) => {
				if (!entry.isIntersecting) {
					return;
				}
				entry.target.classList.add('is-visible');
				editorialObserver.unobserve(entry.target);
			});
		},
		{
			rootMargin: '0px 0px -8% 0px',
			threshold: 0.08,
		}
	);

	editorialElements.forEach((el) => editorialObserver.observe(el));

	const disableMotion = (event) => {
		if (!event.matches) {
			return;
		}

		revealObserver.disconnect();
		editorialObserver.disconnect();
		document.documentElement.classList.remove('nice-has-reveal');
		revealElements.forEach((el) => el.classList.add('is-visible'));
		editorialElements.forEach((el) => el.classList.add('is-visible'));
	};

	motionPreference.addEventListener?.('change', disableMotion);

	document.addEventListener('click', (event) => {
		const link = event.target.closest('a[href^="#"]');
		if (!link) {
			return;
		}

		const target = document.querySelector(link.getAttribute('href'));
		if (!target) {
			return;
		}

		window.setTimeout(() => {
			target.setAttribute('tabindex', '-1');
			target.focus({ preventScroll: true });
			target.addEventListener('blur', () => target.removeAttribute('tabindex'), { once: true });
		}, motionPreference.matches ? 0 : 380);
	});
})();
