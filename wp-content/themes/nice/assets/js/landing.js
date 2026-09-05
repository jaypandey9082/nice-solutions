(() => {
	'use strict';

	const elements = [...document.querySelectorAll('[data-nice-reveal]')];

	if (!elements.length) {
		return;
	}

	const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	if (reducedMotion || !('IntersectionObserver' in window)) {
		elements.forEach((element) => element.classList.add('is-visible'));
		return;
	}

	document.documentElement.classList.add('nice-has-reveal');

	const observer = new IntersectionObserver(
		(entries) => {
			entries.forEach((entry) => {
				if (!entry.isIntersecting) {
					return;
				}

				entry.target.classList.add('is-visible');
				observer.unobserve(entry.target);
			});
		},
		{
			rootMargin: '0px 0px -8% 0px',
			threshold: 0.08,
		}
	);

	elements.forEach((element) => observer.observe(element));
})();
