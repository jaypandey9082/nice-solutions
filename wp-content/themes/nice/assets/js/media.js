(() => {
	'use strict';

	const videos = [...document.querySelectorAll('video[data-nice-lazy-video]')];

	if (!videos.length) {
		return;
	}

	const hydrateVideo = (video) => {
		video.querySelectorAll('source[data-src]').forEach((source) => {
			source.src = source.dataset.src;
			source.removeAttribute('data-src');
		});
		video.removeAttribute('data-nice-lazy-video');
		video.load();
	};

	if (!('IntersectionObserver' in window)) {
		videos.forEach(hydrateVideo);
		return;
	}

	const observer = new IntersectionObserver(
		(entries) => {
			entries.forEach((entry) => {
				if (!entry.isIntersecting) {
					return;
				}

				hydrateVideo(entry.target);
				observer.unobserve(entry.target);
			});
		},
		{ rootMargin: '320px 0px' }
	);

	videos.forEach((video) => observer.observe(video));
})();

