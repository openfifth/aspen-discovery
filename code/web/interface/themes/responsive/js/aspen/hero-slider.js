/* global Swiper, $ */
AspenDiscovery.HeroSlider = (function(){
	return {
		/**
		 * @param {Object} options
		 * @param {boolean} options.autoRotate
		 * @param {number} options.defaultInterval
		 */
		initWebsiteSlider(options){
			const slides = document.querySelectorAll('.hero-slide');
			if (!slides.length) {
				console.error('No slides found for hero slider.');
				return;
			}

			const heroSliderEl = document.querySelector('.hero-slider');
			let navHideTimer = null;
			let lastPointerType = 'mouse';
			function showNavTemporarily() {
				if (!heroSliderEl) return;
				heroSliderEl.classList.add('nav-visible');
				if (navHideTimer) {
					clearTimeout(navHideTimer);
				}
				navHideTimer = setTimeout(() => {
					heroSliderEl.classList.remove('nav-visible');
				}, 3000);
			}

			function hideNav() {
				if (!heroSliderEl) return;
				heroSliderEl.classList.remove('nav-visible');
				if (navHideTimer) {
					clearTimeout(navHideTimer);
					navHideTimer = null;
				}
			}

			if (heroSliderEl) {
				heroSliderEl.addEventListener('mouseenter', () => {
					lastPointerType = 'mouse';
					showNavTemporarily();
				});
				heroSliderEl.addEventListener('mousemove', () => {
					lastPointerType = 'mouse';
					showNavTemporarily();
				});
				heroSliderEl.addEventListener('pointerdown', (event) => {
					lastPointerType = event.pointerType || 'mouse';
					showNavTemporarily();
				});
				heroSliderEl.addEventListener('pointerup', showNavTemporarily);
				heroSliderEl.addEventListener('mouseleave', hideNav);
				heroSliderEl.addEventListener('pointerleave', hideNav);
				heroSliderEl.addEventListener('focusin', showNavTemporarily);
				heroSliderEl.addEventListener('focusout', (event) => {
					if (!heroSliderEl.contains(event.relatedTarget)) {
						hideNav();
					}
				});
				heroSliderEl.addEventListener('keydown', () => {
					lastPointerType = 'keyboard';
				});
			}

			let swiperOptions = {
				direction: 'horizontal',
				loop: true,
				speed: 1000,
				navigation: {
					nextEl: '.swiper-button-next',
					prevEl: '.swiper-button-prev',
				},
				keyboard: {
					enabled: true,
				},
				a11y: {
					enabled: true
				},
				effect: 'slide'
			};

			if (options.autoRotate) {
				// Build array of delays for per-slide duration.
				let delays = [];
				slides.forEach(slide => {
					let duration = parseInt(slide.dataset.duration) || (options.defaultInterval / 1000);
					delays.push(duration * 1000);
				});

				swiperOptions.autoplay = {
					delay: delays[0] || options.defaultInterval,
					disableOnInteraction: false,
				};

				swiperOptions.pagination = {
					el: '.swiper-pagination',
					clickable: true,
				};

				// Update delay on slide change to use per-slide duration.
				swiperOptions.on = {
					slideChange: function() {
						const realIndex = this.realIndex;
						if (delays[realIndex]) {
							this.params.autoplay.delay = delays[realIndex];
							if (this.autoplay.running) {
								this.autoplay.stop();
								this.autoplay.start();
							}
						}
					}
				};
			}

			const swiper = new Swiper('.hero-slider', swiperOptions);

			const navButtons = heroSliderEl ? heroSliderEl.querySelectorAll('.swiper-button-next, .swiper-button-prev') : [];
			navButtons.forEach(button => {
				button.addEventListener('click', () => {
					showNavTemporarily();
					if (lastPointerType !== 'keyboard') {
						button.blur();
					}
				});
			});

			// Accessibility: Keep off-screen slides out of tab order.
			swiper.on('slideChangeTransitionEnd', function() {
                // noinspection JSUnresolvedReference
				$(".hero-slider .swiper-slide:not(.swiper-slide-visible) a, .hero-slider .swiper-slide:not(.swiper-slide-visible) img")
                    .prop("tabindex", "-1");
                // noinspection JSUnresolvedReference
				$(".hero-slider .swiper-slide-visible a, .hero-slider .swiper-slide-visible img")
                    .removeAttr("tabindex");
			});

			// Pause/play button for auto-rotation.
			if (options.autoRotate) {
				const pauseButton = document.querySelector('.swiper-button-pause');
				if (pauseButton) {
					let isPaused = false;
					pauseButton.addEventListener('click', () => {
						if (isPaused) {
							swiper.autoplay.start();
							pauseButton.innerHTML = '<i class="fas fa-pause"></i>';
							pauseButton.setAttribute('aria-label', 'Pause auto-rotation');
							pauseButton.setAttribute('title', 'Pause');
							isPaused = false;
						} else {
							swiper.autoplay.stop();
							pauseButton.innerHTML = '<i class="fas fa-play"></i>';
							pauseButton.setAttribute('aria-label', 'Resume auto-rotation');
							pauseButton.setAttribute('title', 'Play');
							isPaused = true;
						}
					});
				}
			}
		},

		/**
		 * @param {Object} options
		 * @param {boolean} options.autoRotate
		 * @param {number} options.locationId
		 * @param {boolean} [options.reload]
		 */
		initDigitalSignage(options) {
			if (!options.autoRotate) {
				return;
			}

			let slides = document.querySelectorAll('.signage-slide');
			if (!slides.length) {
				console.error('No slides found for digital signage.');
				return;
			}

			const container = document.querySelector('.digital-signage-container');
			let currentSlide = 0;
			let rotationTimer = null;
			let pendingUpdate = null;

			function getDuration(slide) {
				return parseInt(slide.dataset.duration, 10) || 0;
			}

			function getEnabledIndex(fromEnd) {
				if (fromEnd) {
					for (let i = slides.length - 1; i >= 0; i--) {
						if (getDuration(slides[i]) !== 0) return i;
					}
				} else {
					for (let i = 0; i < slides.length; i++) {
						if (getDuration(slides[i]) !== 0) return i;
					}
				}
				return -1;
			}

			function showSlide(index) {
				slides.forEach((slide, i) => {
					slide.classList.toggle('active', i === index);
				});
			}

			function scheduleNext() {
				const duration = getDuration(slides[currentSlide]) * 1000;
				rotationTimer = setTimeout(nextSlide, duration);
			}

			function nextSlide() {
				const firstEnabled = getEnabledIndex(false);
				const lastEnabled = getEnabledIndex(true);

				// Find next enabled slide.
				let nextIndex = (currentSlide + 1) % slides.length;
				let attempts = 0;
				while (getDuration(slides[nextIndex]) === 0 && attempts < slides.length) {
					nextIndex = (nextIndex + 1) % slides.length;
					attempts++;
				}

				if (attempts >= slides.length) {
					return; // All slides disabled.
				}

				// Apply pending update when looping back to first slide.
				if (nextIndex === firstEnabled && currentSlide === lastEnabled && pendingUpdate) {
					applyPendingUpdate();
					return;
				}

				currentSlide = nextIndex;
				showSlide(currentSlide);
				scheduleNext();

				// Prefetch on last slide if reload enabled.
				if (options.reload && currentSlide === lastEnabled && !pendingUpdate) {
					prefetchContent();
				}
			}

			function prefetchContent() {
                // noinspection JSUnresolvedFunction
				$.getJSON('/API/HeroSliderAPI', {method: 'getSlides', id: options.locationId})
					.done(function(data) {
						if (data.success && data.slides && data.slides.length > 0) {
							preloadImages(data.slides, function() {
								buildPendingUpdate(data.slides);
							});
						}
					})
					.fail(function() {
						console.error('Failed to prefetch content');
					});
			}

			/**
			 * @param {{imageUrl: string, altText: string, duration: number, pageLink: string}[]} slidesData
			 * @param {function} callback
			 */
			function preloadImages(slidesData, callback) {
				let remaining = slidesData.length;
				slidesData.forEach(function(slideData) {
					const img = new Image();
					img.onload = img.onerror = function() {
						if (--remaining === 0) callback();
					};
					img.src = slideData.imageUrl;
				});
			}

			/**
			 * @param {{imageUrl: string, altText: string, duration: number, pageLink: string}[]} slidesData
			 */
			function buildPendingUpdate(slidesData) {
				const fragment = document.createDocumentFragment();
				let firstEnabled = null;
				slidesData.forEach(function(slideData, index) {
					const slideDiv = document.createElement('div');
					slideDiv.className = 'signage-slide';
					slideDiv.dataset.duration = String(slideData.duration);

					const img = document.createElement('img');
					img.src = slideData.imageUrl;
					img.alt = slideData.altText;

					if (slideData.pageLink) {
						const link = document.createElement('a');
						link.href = slideData.pageLink;
						link.target = '_blank';
						link.appendChild(img);
						slideDiv.appendChild(link);
					} else {
						slideDiv.appendChild(img);
					}

					if (firstEnabled === null && slideData.duration !== 0) {
						slideDiv.classList.add('active');
						firstEnabled = index;
					}

					fragment.appendChild(slideDiv);
				});

				pendingUpdate = {fragment: fragment, firstEnabled: firstEnabled};
			}

			function applyPendingUpdate() {
				if (!pendingUpdate) return;

				clearTimeout(rotationTimer);

				const newSlides = Array.from(pendingUpdate.fragment.children);
				container.replaceChildren(...newSlides);
				slides = container.querySelectorAll('.signage-slide');

				if (pendingUpdate.firstEnabled !== null) {
					currentSlide = pendingUpdate.firstEnabled;
					scheduleNext();
				}

				pendingUpdate = null;
			}

			// Initialize: find first enabled slide and start rotation.
			const startIndex = getEnabledIndex(false);
			if (startIndex === -1) {
				return;
			}

			currentSlide = startIndex;
			showSlide(currentSlide);
			scheduleNext();
		}
	};
})();
