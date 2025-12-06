<?php

require_once ROOT_DIR . '/Action.php';

class API_HeroSliderAPI extends Action {
	function launch(): void {
		$method = $_REQUEST['method'] ?? '';

		header('Content-type: text/html');
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

		if ($method == 'getHeroSlider') {
			echo $this->getHeroSlider();
		} else {
			echo '<p>Error: Invalid method</p>';
		}
	}

	function getHeroSlider(): string {
		global $interface;
		require_once ROOT_DIR . '/sys/HeroSlider/HeroSliderLocation.php';
		require_once ROOT_DIR . '/sys/HeroSlider/HeroSliderPlaylist.php';
		require_once ROOT_DIR . '/sys/File/ImageUpload.php';

		$id = $_REQUEST['id'] ?? null;
		if (!$id) {
			return '<p>Error: No id specified.</p>';
		}

		$location = new HeroSliderLocation();
		$location->id = $id;
		if (!$location->find(true)) {
			return '<p>Error: Location not found.</p>';
		}
		$interface->assign('location', $location);

		$playlist = new HeroSliderPlaylist();
		$playlist->id = $location->playlistId;
		if (!$playlist->find(true)) {
			return '<p>Error: Playlist not found.</p>';
		}

		$activeImages = $playlist->getActiveImages(
			$location->aspectRatioWidth,
			$location->aspectRatioHeight
		);

		if (empty($activeImages)) {
			return '<p>No active images in playlist.</p>';
		}

		$interface->assign('slides', $activeImages);

		// Check for reload parameter (for digital signage).
		if (isset($_REQUEST['reload'])) {
			$interface->assign('reload', true);
			// Calculate total duration for meta refresh
			$totalDuration = 0;
			foreach ($activeImages as $slide) {
				$totalDuration += $slide['duration'];
			}
			$interface->assign('totalDuration', $totalDuration);
		} else {
			$interface->assign('reload', false);
		}

		if ($location->displayStyle == 'digital_signage') {
			return $interface->fetch('HeroSlider/heroSliderDigitalSignage.tpl');
		} else {
			return $interface->fetch('HeroSlider/heroSliderWebsite.tpl');
		}
	}

	function getBreadcrumbs(): array {
		return [];
	}
}
