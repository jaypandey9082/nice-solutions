(() => {
	'use strict';

	const strings = window.niceStudioMedia || {};
	const emptyLabel = strings.emptyLabel || 'No image selected';

	document.querySelectorAll('[data-nice-media-control]').forEach((control) => {
		const idInput = control.querySelector('[data-nice-media-id]');
		const preview = control.querySelector('[data-nice-media-preview]');
		const selectButton = control.querySelector('[data-nice-media-select]');
		const removeButton = control.querySelector('[data-nice-media-remove]');
		const altMessage = control.querySelector('[data-nice-media-alt]');
		let frame;

		if (!idInput || !preview || !selectButton || !removeButton || !window.wp?.media) {
			return;
		}

		const setEmptyState = () => {
			idInput.value = '';
			preview.replaceChildren(Object.assign(document.createElement('span'), { textContent: emptyLabel }));
			selectButton.textContent = selectButton.dataset.selectLabel;
			removeButton.hidden = true;
			control.dataset.empty = 'true';
			if (altMessage) {
				altMessage.textContent = strings.altManaged || 'Alt text is managed in the WordPress Media Library.';
			}
		};

		selectButton.addEventListener('click', () => {
			if (!frame) {
				frame = window.wp.media({
					title: strings.frameTitle || 'Choose Studio hero image',
					button: { text: strings.frameButton || 'Use this image' },
					library: { type: 'image' },
					multiple: false,
				});

				frame.on('select', () => {
					const attachment = frame.state().get('selection').first()?.toJSON();
					if (!attachment?.id || !attachment.url) {
						return;
					}

					const image = document.createElement('img');
					image.src = attachment.sizes?.medium?.url || attachment.url;
					image.alt = '';
					idInput.value = String(attachment.id);
					preview.replaceChildren(image);
					selectButton.textContent = selectButton.dataset.replaceLabel;
					removeButton.hidden = false;
					control.dataset.empty = 'false';
					if (altMessage) {
						altMessage.textContent = attachment.alt
							? (strings.altPresent || 'Attachment alt text is set.')
							: (strings.altMissing || 'Add meaningful alt text in the Media Library before publishing.');
					}
				});
			}

			frame.open();
		});

		removeButton.addEventListener('click', setEmptyState);
	});

	document.querySelectorAll('[data-nice-focal]').forEach((input) => {
		const output = input.parentElement?.querySelector(`output[for="${input.id}"]`);
		input.addEventListener('input', () => {
			if (output) {
				output.value = `${input.value}%`;
			}
		});
	});
})();
