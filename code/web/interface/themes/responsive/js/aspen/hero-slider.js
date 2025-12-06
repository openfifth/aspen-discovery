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
		},

		initDigitalSignage(options) {
			if (!options.autoRotate) {
				return;
			}

			const slides = document.querySelectorAll('.signage-slide');
			if (!slides.length) {
				console.error('No slides found for digital signage.');
				return;
			}

			let currentSlide = 0;

			function showSlide(index) {
				slides.forEach((slide, i) => {
					if (i === index) {
						slide.classList.add('active');
					} else {
						slide.classList.remove('active');
					}
				});
			}

			function nextSlide() {
				currentSlide = (currentSlide + 1) % slides.length;
				showSlide(currentSlide);

				// Schedule next transition based on current slide's duration.
				const currentDuration = parseInt(slides[currentSlide].dataset.duration) * 1000;
				setTimeout(nextSlide, currentDuration);
			}

			// Start rotation after first slide's duration.
			const firstDuration = parseInt(slides[0].dataset.duration) * 1000;
			setTimeout(nextSlide, firstDuration);
		}
	};
})();
