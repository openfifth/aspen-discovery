AspenDiscovery.HeroSlider = (function(){
	return {
		initWebsiteSlider(options){
			const slides = document.querySelectorAll('.hero-slide');
			if (!slides.length) {
				console.error('No slides found for hero slider.');
				return;
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

			// Accessibility: Keep off-screen slides out of tab order.
			swiper.on('slideChangeTransitionEnd', function() {
				$(".hero-slider .swiper-slide:not(.swiper-slide-visible) a, .hero-slider .swiper-slide:not(.swiper-slide-visible) img")
					.prop("tabindex", "-1");
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

		initDigitalSignage(options) {
			if (!options.autoRotate) {
				return;
			}

			let slides = document.querySelectorAll('.signage-slide');
			if (!slides.length) {
				console.error('No slides found for digital signage.');
				return;
			}

			let currentSlide = 0;
			let rotationTimer = null;
			let refreshTimer = null;
			let isRotating = false;

			function showSlide(index) {
				slides.forEach((slide, i) => {
					if (i === index) {
						slide.classList.add('active');
					} else {
						slide.classList.remove('active');
					}
				});
			}

			function stopRotation() {
				if (rotationTimer) {
					clearTimeout(rotationTimer);
					rotationTimer = null;
				}
				if (refreshTimer) {
					clearTimeout(refreshTimer);
					refreshTimer = null;
				}
				isRotating = false;
			}

			function nextSlide() {
				if (!isRotating) return;

				let nextIndex = (currentSlide + 1) % slides.length;
				let attempts = 0;
				let firstEnabledIndex = -1;
				for (let i = 0; i < slides.length; i++) {
					if (parseInt(slides[i].dataset.duration) !== 0) {
						firstEnabledIndex = i;
						break;
					}
				}

				// Skip slides with duration=0 (temporarily disabled).
				while (parseInt(slides[nextIndex].dataset.duration) === 0 && attempts < slides.length) {
					nextIndex = (nextIndex + 1) % slides.length;
					attempts++;
				}
				// If all slides are disabled, stop rotation.
				if (attempts >= slides.length) {
					stopRotation();
					return;
				}

				// If we're about to loop back to first enabled slide and reload is enabled, refresh content instead.
				if (nextIndex === firstEnabledIndex && currentSlide !== firstEnabledIndex && options.reload) {
					refreshContent();
					return;
				}

				currentSlide = nextIndex;
				showSlide(currentSlide);
				const currentDuration = parseInt(slides[currentSlide].dataset.duration) * 1000;
				rotationTimer = setTimeout(nextSlide, currentDuration);
			}

			function refreshContent() {
				const url = '/API/HeroSliderAPI';
				const params = {
					method: 'getSlides',
					id: options.locationId
				};

				$.getJSON(url, params)
					.done(function(data) {
						if (data.success && data.slides && data.slides.length > 0) {
							// Preload all new images before updating DOM.
							let imagesToLoad = data.slides.length;
							let imagesLoaded = 0;

							const checkAllLoaded = function() {
								imagesLoaded++;
								if (imagesLoaded === imagesToLoad) {
									updateSlides(data.slides);
								}
							};

							data.slides.forEach(slideData => {
								const tempImg = new Image();
								tempImg.onload = checkAllLoaded;
								tempImg.onerror = checkAllLoaded;
								tempImg.src = slideData.imageUrl;
							});
						} else {
							isRotating = true;
							const firstDuration = parseInt(slides[0].dataset.duration) * 1000;
							rotationTimer = setTimeout(nextSlide, firstDuration);
						}
					})
					.fail(function() {
						console.error('Failed to refresh content');
						isRotating = true;
						const firstDuration = parseInt(slides[0].dataset.duration) * 1000;
						rotationTimer = setTimeout(nextSlide, firstDuration);
					});
			}

			function updateSlides(newSlidesData) {
				stopRotation();

				// Update DOM directly (last slide is still showing, so transition is seamless)
                // TODO: This does not work yet, causes a flicker on first slide.
				const container = document.querySelector('.digital-signage-container');
				container.innerHTML = '';

				newSlidesData.forEach(slideData => {
					const slideDiv = document.createElement('div');
					slideDiv.className = 'signage-slide';
					slideDiv.setAttribute('data-duration', slideData.duration);

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

					container.appendChild(slideDiv);
				});

				slides = document.querySelectorAll('.signage-slide');
				let startIndex = 0;
				while (parseInt(slides[startIndex].dataset.duration) === 0 && startIndex < slides.length) {
					startIndex++;
				}

				if (startIndex < slides.length) {
					currentSlide = startIndex;
					isRotating = true;
					showSlide(currentSlide);

					const firstDuration = parseInt(slides[currentSlide].dataset.duration) * 1000;
					rotationTimer = setTimeout(nextSlide, firstDuration);
				}
			}

			let startIndex = 0;
			while (parseInt(slides[startIndex].dataset.duration) === 0 && startIndex < slides.length) {
				startIndex++;
			}

			if (startIndex >= slides.length) {
				return;
			}

			currentSlide = startIndex;
			isRotating = true;
			showSlide(currentSlide);
			const firstDuration = parseInt(slides[currentSlide].dataset.duration) * 1000;
			rotationTimer = setTimeout(nextSlide, firstDuration);

			if (options.reload && options.reloadDuration) {
				refreshTimer = setTimeout(refreshContent, options.reloadDuration);
			}
		}
	};
})();
