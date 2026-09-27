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


/*
 * Click-to-play for a project's YouTube or Vimeo film.
 *
 * The page ships the featured image with a button over it and builds the iframe
 * on the press. A lazy iframe would still be an iframe: honoured inconsistently
 * by browsers, ~700KB of player script the moment it does load, and a request to
 * Google for every reader who never wanted the film. This way the first request
 * to either provider is one a reader asked for.
 *
 * Its own block rather than part of the lazy-video one above, which returns
 * early on a page that has no video file to hydrate.
 */
(() => {
	'use strict';

	document.querySelectorAll('[data-nice-video-embed]').forEach((button) => {
		button.addEventListener('click', () => {
			const frame = button.closest('.nice-video');

			if (!frame) {
				return;
			}

			const iframe = document.createElement('iframe');

			iframe.src = button.dataset.niceVideoEmbed;
			iframe.title = button.dataset.niceVideoTitle || '';
			iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
			iframe.referrerPolicy = 'strict-origin-when-cross-origin';
			iframe.allowFullscreen = true;

			/* Replaces the poster and the button together, into the same box. */
			frame.replaceChildren(iframe);
			iframe.focus();
		});
	});
})();
