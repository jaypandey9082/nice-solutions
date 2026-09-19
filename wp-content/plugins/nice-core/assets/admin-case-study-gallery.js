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
