import './stimulus_bootstrap.js';

function initializePasswordToggles() {
	if (document.body.dataset.passwordTogglesInitialized) {
		return;
	}

	document.body.dataset.passwordTogglesInitialized = 'true';
	document.addEventListener('click', (event) => {
		const button = event.target.closest('[data-password-toggle-button]');

		if (!button) {
			return;
		}

		const input = button.closest('[data-password-toggle]')?.querySelector('input');

		if (!input) {
			return;
		}

		const isPassword = input.type === 'password';
		input.type = isPassword ? 'text' : 'password';
		button.setAttribute('aria-label', isPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
	});
}

initializePasswordToggles();

function renderRecaptchas() {
	if (!window.grecaptcha) {
		return;
	}

	window.grecaptcha.ready(() => {
		document.querySelectorAll('.g-recaptcha').forEach((widget) => {
			if (widget.dataset.recaptchaRendered) {
				return;
			}

			const siteKey = widget.dataset.sitekey;

			if (!siteKey) {
				return;
			}

			window.grecaptcha.render(widget, { sitekey: siteKey });
			widget.dataset.recaptchaRendered = 'true';
		});
	});
}

window.onRecaptchaLoad = renderRecaptchas;
document.addEventListener('DOMContentLoaded', renderRecaptchas, { once: true });
document.addEventListener('turbo:load', renderRecaptchas);

let galleryImages = [];
let galleryIndex = 0;

function initializeGallery() {
	const image = document.getElementById('gallery-image');

	if (!image) {
		galleryImages = [];
		galleryIndex = 0;
		return;
	}

	try {
		galleryImages = JSON.parse(image.dataset.images || '[]');
	} catch (error) {
		galleryImages = [];
	}

	galleryIndex = 0;
	updateGalleryImage();
}

function updateGalleryImage() {
	const image = document.getElementById('gallery-image');
	const counter = document.getElementById('gallery-counter');
	const thumbnails = document.querySelectorAll('.gallery-thumbnail');

	if (!image || !counter || !galleryImages[galleryIndex]) {
		return;
	}

	image.src = '/image/' + galleryImages[galleryIndex];
	counter.textContent = 'Galerie - ' + galleryImages.length + ' photos';

	thumbnails.forEach((thumbnail, thumbnailIndex) => {
		thumbnail.classList.toggle('border-[#2b7bc6]', thumbnailIndex === galleryIndex);
		thumbnail.classList.toggle('border-transparent', thumbnailIndex !== galleryIndex);
	});
}

function handleGalleryClick(event) {
	const control = event.target.closest('[data-gallery-action]');

	if (!control) {
		return;
	}

	event.preventDefault();
	event.stopPropagation();

	const action = control.dataset.galleryAction;

	if (action === 'thumbnail') {
		galleryIndex = Number.parseInt(control.dataset.galleryIndex || '0', 10);
		updateGalleryImage();
	}

	if (action === 'previous' || action === 'next') {
		if (!galleryImages.length) {
			return;
		}

		const step = action === 'next' ? 1 : -1;
		galleryIndex = (galleryIndex + step + galleryImages.length) % galleryImages.length;
		updateGalleryImage();
	}

	if (action === 'fullscreen') {
		const image = document.getElementById('gallery-image');

		if (!image) {
			return;
		}

		if (document.fullscreenElement) {
			document.exitFullscreen?.().catch(() => {});
		} else {
			const fullscreenTarget = image.closest('section') || image;
			fullscreenTarget.requestFullscreen?.().catch(() => {});
		}
	}
}

document.addEventListener('click', handleGalleryClick);
document.addEventListener('DOMContentLoaded', initializeGallery, { once: true });
document.addEventListener('turbo:load', initializeGallery);

/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');
