/*
 * The Case Study gallery picker.
 *
 * Order is the point of this control, so it is kept in one place: the hidden
 * field. The list is rebuilt from that field after every change rather than the
 * two being edited in parallel, which is how a reorder and a removal end up
 * disagreeing about what the editor actually chose.
 */
(() => {
	'use strict';

	const strings = window.niceGalleryStrings || {};

	document.querySelectorAll('[data-nice-gallery]').forEach((control) => {
		const field = control.querySelector('[data-nice-gallery-value]');
		const list = control.querySelector('[data-nice-gallery-list]');
		const empty = control.querySelector('[data-nice-gallery-empty]');
		const addButton = control.querySelector('[data-nice-gallery-add]');
		const count = control.querySelector('[data-nice-gallery-count]');
		const max = Number.parseInt(control.dataset.max, 10) || 10;

		if (!field || !list || !addButton || !window.wp?.media) {
			return;
		}

		/* Thumbnails already rendered by PHP, so a fresh pick is all that needs fetching. */
		const known = new Map();
		list.querySelectorAll('[data-nice-gallery-item]').forEach((item) => known.set(item.dataset.id, item));

		const ids = () => field.value.split(',').map((value) => value.trim()).filter(Boolean);

		const render = () => {
			const current = ids();

			list.replaceChildren(...current.map((id) => known.get(id)).filter(Boolean));
			empty.hidden = current.length > 0;
			addButton.disabled = current.length >= max;
			count.textContent = current.length
				? (strings.count || '%1$d of %2$d').replace('%1$d', current.length).replace('%2$d', max)
				: '';
		};

		const write = (next) => {
			field.value = next.slice(0, max).join(',');
			render();
		};

		list.addEventListener('click', (event) => {
			const item = event.target.closest('[data-nice-gallery-item]');

			if (!item) {
				return;
			}

			const current = ids();
			const index = current.indexOf(item.dataset.id);

			if (index === -1) {
				return;
			}

			if (event.target.closest('[data-nice-gallery-remove]')) {
				current.splice(index, 1);
				write(current);
				return;
			}

			const move = event.target.closest('[data-nice-gallery-move]');

			if (move) {
				const to = index + Number.parseInt(move.dataset.niceGalleryMove, 10);

				if (to < 0 || to >= current.length) {
					return;
				}

				current.splice(to, 0, current.splice(index, 1)[0]);
				write(current);

				/* Keep the keyboard on the button that was pressed, not at the top. */
				const moved = list.querySelector(`[data-id="${item.dataset.id}"]`);
				moved?.querySelector(`[data-nice-gallery-move="${move.dataset.niceGalleryMove}"]`)?.focus();
			}
		});

		let frame;

		addButton.addEventListener('click', () => {
			if (!frame) {
				frame = window.wp.media({
					title: strings.frameTitle || 'Add images to this project',
					button: { text: strings.frameButton || 'Add to gallery' },
					library: { type: 'image' },
					multiple: 'add',
				});

				frame.on('select', () => {
					const current = ids();

					frame.state().get('selection').toJSON().forEach((attachment) => {
						const id = String(attachment.id);

						if (!attachment.id || current.includes(id) || current.length >= max) {
							return;
						}

						if (!known.has(id)) {
							known.set(id, buildItem(attachment));
						}

						current.push(id);
					});

					write(current);
				});
			}

			frame.open();
		});

		/**
		 * Build a thumbnail for a newly chosen attachment.
		 *
		 * @param {Object} attachment Attachment as reported by the media frame.
		 * @returns {HTMLElement}
		 */
		function buildItem(attachment) {
			const item = document.createElement('li');
			item.className = 'nice-gallery-item';
			item.dataset.niceGalleryItem = '';
			item.dataset.id = String(attachment.id);

			const image = document.createElement('img');
			image.src = attachment.sizes?.thumbnail?.url || attachment.url;
			image.alt = '';
			image.width = 150;
			image.height = 150;

			const alt = document.createElement('span');
			alt.className = `nice-gallery-item__alt ${attachment.alt ? 'is-set' : 'is-missing'}`;
			alt.textContent = attachment.alt ? (strings.altSet || 'Alt text set') : (strings.altMissing || 'No alt text');

			const controls = document.createElement('span');
			controls.className = 'nice-gallery-item__controls';
			controls.append(
				button('-1', '←', strings.moveEarlier || 'Move earlier'),
				button('1', '→', strings.moveLater || 'Move later'),
				removeButton()
			);

			item.append(image, alt, controls);
			return item;
		}

		function button(move, label, aria) {
			const element = document.createElement('button');
			element.type = 'button';
			element.className = 'button-link';
			element.dataset.niceGalleryMove = move;
			element.textContent = label;
			element.setAttribute('aria-label', aria);
			return element;
		}

		function removeButton() {
			const element = document.createElement('button');
			element.type = 'button';
			element.className = 'button-link button-link-delete';
			element.dataset.niceGalleryRemove = '';
			element.textContent = '×';
			element.setAttribute('aria-label', strings.remove || 'Remove image');
			return element;
		}

		render();
	});
})();

/*
 * The Feature Media panel.
 *
 * Two jobs that look like one: showing whichever dependent field the chooser
 * selected, and picking a video file. They share a screen with the gallery above
 * and nothing else, which is why they ride the same handle rather than carrying
 * a second enqueue for sixty lines.
 *
 * The panels are hidden by PHP from the stored value, not by this file on load.
 * Without JavaScript an editor still sees the group that is currently in use,
 * and can still change the chooser, save, and be shown the other one.
 */
(() => {
	'use strict';

	const strings = window.niceFeatureMedia || {};
	const chooser = document.querySelector('[data-nice-feature-media-type]');

	if (chooser) {
		const panels = document.querySelectorAll('[data-nice-feature-media-panel]');

		chooser.addEventListener('change', () => {
			panels.forEach((panel) => {
				panel.hidden = panel.dataset.niceFeatureMediaPanel !== chooser.value;
			});
		});
	}

	document.querySelectorAll('[data-nice-video-control]').forEach((control) => {
		const field = control.querySelector('[data-nice-video-id]');
		const preview = control.querySelector('[data-nice-video-preview]');
		const name = control.querySelector('[data-nice-video-name]');
		const selectButton = control.querySelector('[data-nice-video-select]');
		const removeButton = control.querySelector('[data-nice-video-remove]');

		if (!field || !preview || !selectButton || !window.wp?.media) {
			return;
		}

		let frame;

		/**
		 * Reflect a selection, or the absence of one, across the whole control.
		 *
		 * @param {string} id       Attachment id, or an empty string.
		 * @param {string} url      Playable URL, or an empty string.
		 * @param {string} fileName File name to show.
		 */
		const apply = (id, url, fileName) => {
			field.value = id;
			control.dataset.empty = id ? 'false' : 'true';
			selectButton.textContent = id
				? selectButton.dataset.replaceLabel
				: selectButton.dataset.selectLabel;

			if (removeButton) {
				removeButton.hidden = !id;
			}

			preview.querySelector('video')?.remove();

			if (url) {
				const video = document.createElement('video');
				video.src = url;
				video.muted = true;
				video.playsInline = true;
				video.preload = 'metadata';
				preview.prepend(video);
			}

			if (name) {
				name.textContent = fileName || strings.videoEmpty || 'No video selected';
			}
		};

		selectButton.addEventListener('click', () => {
			if (!frame) {
				frame = window.wp.media({
					title: strings.videoFrameTitle || 'Choose a video file',
					button: { text: strings.videoFrameButton || 'Use this video' },
					library: { type: 'video' },
					multiple: false,
				});

				frame.on('select', () => {
					const attachment = frame.state().get('selection').first();

					if (!attachment) {
						return;
					}

					const chosen = attachment.toJSON();

					/*
					 * Refused here rather than in the sanitizer. A .mov already on
					 * a record should keep playing for whoever it plays for; a new
					 * one should not be offered as though every browser will take
					 * it.
					 */
					if (!['video/mp4', 'video/webm'].includes(chosen.mime)) {
						window.alert(strings.videoFormat || 'Use an MP4 or WebM file.');
						return;
					}

					apply(String(chosen.id), chosen.url || '', chosen.filename || '');
				});
			}

			frame.open();
		});

		removeButton?.addEventListener('click', () => {
			apply('', '', '');
			selectButton.focus();
		});
	});
})();
