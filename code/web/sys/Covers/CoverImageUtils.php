<?php

/**
 * Wraps text to fit within a max width/height by adjusting font size and spacing.
 *
 * @param string $font Path to TTF font file.
 * @param string $text Text to wrap.
 * @param float $fontSize Starting font size in points.
 * @param float $lineSpacing Spacing between lines (in pixels).
 * @param int $maxWidth Max width in pixels.
 * @param int $maxHeight Optional max height (0 = no limit).
 *
 * @return array{0: float, 1: string[], 2: float} Total height, array of lines, final font size.
 */
function wrapTextForDisplay(string $font, string $text, float $fontSize, float $lineSpacing, int $maxWidth, int $maxHeight = 0): array {
	if (trim($text) === '') {
		return [0, [], $fontSize];
	}

	$maxAttempts = 20; // Replaced the potentially infinite loop; 20 should be sufficient.
	// Determine if we should wrap by word (space-delimited scripts) or character (CJK).
	$isSpaceDelimited = preg_match('/\p{Latin}|\p{Arabic}|\p{Cyrillic}|\p{Armenian}|\p{Hebrew}|\p{Georgian}|\p{Greek}/u', $text) && preg_match('/\s/u', $text);
	$lines = [];
	$totalHeight = 0;

	for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
		$lines = [];
		$currentLine = '';

		if ($isSpaceDelimited) {
			$words = preg_split('/\s+/u', $text);
			foreach ($words as $word) {
				$candidate = $currentLine === '' ? $word : $currentLine . ' ' . $word;
				$bbox = imageftbbox($fontSize, 0, $font, $candidate);
				$width = abs($bbox[4] - $bbox[6]);

				if ($width > $maxWidth && $currentLine !== '') {
					$lines[] = $currentLine;
					$currentLine = $word;
				} else {
					$currentLine = $candidate;
				}
			}
		}
		else {
			// Split the text into an array of individual Unicode characters.
			$chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);

			foreach ($chars as $ch) {
				$candidate = $currentLine . $ch;
				// Measure how wide the current line would be with this added character.
				$bbox = imageftbbox($fontSize, 0, $font, $candidate);
				$candidateWidth = abs($bbox[4] - $bbox[6]);

				// If the new line would exceed the allowed max width:
				//  - Save the current line and start a new line with the character that caused the overflow.
				//  - Else it is safe to add the character to the current line.
				if ($candidateWidth > $maxWidth && $currentLine !== '') {
					$lines[] = $currentLine;
					$currentLine = $ch;
				} else {
					$currentLine = $candidate;
				}
			}

		}
		if ($currentLine !== '') {
			$lines[] = $currentLine;
		}

		// Compute total height of wrapped lines.
		$totalHeight = 0;
		foreach ($lines as $line) {
			$bbox = imageftbbox($fontSize, 0, $font, $line);
			$lineHeight = abs($bbox[3] - $bbox[5]);
			$totalHeight += $lineHeight + $lineSpacing;
		}

		// If it fits, break.
		if ($maxHeight === 0 || $totalHeight <= $maxHeight) {
			break;
		}

		// Otherwise, shrink and try again.
		$fontSize *= 0.95;
		$lineSpacing *= 0.95;
	}

	return [
		$totalHeight,
		$lines,
		$fontSize,
	];
}

function addWrappedTextToImage($imageHandle, $font, $lines, $fontSize, $lineSpacing, $startX, $startY, $color) {
	foreach ($lines as $line) {
		//Get the width of this line
		$lineBox = imageftbbox($fontSize, 0, $font, $line);
		//$lineWidth = abs($lineBox[4] - $lineBox[6]);
		$lineHeight = abs($lineBox[3] - $lineBox[5]);
		//Get the starting position for the text
		$startY += $lineHeight;

		//Write the text to the image
		if (!imagefttext($imageHandle, $fontSize, 0, $startX, $startY, $color, $font, $line)) {
			echo("Failed to write text");
		}
		$startY += $lineSpacing;
	}
	return $startY;
}

function addCenteredWrappedTextToImage($imageHandle, $font, $lines, $fontSize, $lineSpacing, $startX, $startY, $width, $color) {
	if (!is_array($lines)) {
		$lines = [$lines];
	}
	foreach ($lines as $line) {
		//Get the width of this line
		$lineBox = imageftbbox($fontSize, 0, $font, $line);
		$lineWidth = abs($lineBox[4] - $lineBox[6]);
		$lineHeight = abs($lineBox[3] - $lineBox[5]);
		//Get the starting position for the text
		$startXOfLine = $startX + ($width - $lineWidth) / 2;
		$startY += $lineHeight;
		//Write the text to the image
		if (!imagefttext($imageHandle, $fontSize, 0, $startXOfLine, $startY, $color, $font, $line)) {
			echo("Failed to write text");
		}
		$startY += $lineSpacing;
	}
	return $startY;
}

function _map($value, $iStart, $iStop, $oStart, $oStop) {
	return $oStart + ($oStop - $oStart) * (($value - $iStart) / ($iStop - $iStart));
}

function _clip($value, $lower, $upper) {
	if ($value < $lower) {
		return $lower;
	} elseif ($value > $upper) {
		return $upper;
	} else {
		return $value;
	}
}

function formatImageUpload($uploadedFile, $destFullPath, $id, $recordType){
	$result = ['success' => false];
	$fileType = $uploadedFile["type"];
	if ($fileType == 'image/png') {
		if (copy($uploadedFile["tmp_name"], $destFullPath)) {
			$result['success'] = true;
		}
	} elseif ($fileType == 'image/gif') {
		$imageResource = @imagecreatefromgif($uploadedFile["tmp_name"]);
		if (!$imageResource) {
			$result['message'] = translate([
				'text' => 'Unable to process this image, please try processing in an image editor and reloading',
				'isAdminFacing' => true,
			]);
		} elseif (@imagepng($imageResource, $destFullPath, 9)) {
			$result['success'] = true;
		}
	} elseif ($fileType == 'image/jpg' || $fileType == 'image/jpeg') {
		$imageResource = @imagecreatefromjpeg($uploadedFile["tmp_name"]);
		if (!$imageResource) {
			$result['message'] = translate([
				'text' => 'Unable to process this image, please try processing in an image editor and reloading',
				'isAdminFacing' => true,
			]);
		} elseif (@imagepng($imageResource, $destFullPath, 9)) {
			$result['success'] = true;
		}
	} else {
		$result['message'] = translate([
			'text' => 'Incorrect image type.  Please upload a PNG, GIF, or JPEG',
			'isAdminFacing' => true,
		]);
	}

	if ($result['success'] == true) {
		require_once ROOT_DIR . '/sys/Covers/BookCoverInfo.php';
		$bookCoverInfo = new BookCoverInfo();
		$bookCoverInfo->recordType = $recordType;
		$bookCoverInfo->recordId = $id;
		if($bookCoverInfo->find(true)) {
			$bookCoverInfo->imageSource = "upload";
			$bookCoverInfo->thumbnailLoaded = 0;
			$bookCoverInfo->mediumLoaded = 0;
			$bookCoverInfo->largeLoaded = 0;
			if($bookCoverInfo->update()) {
				$result['message'] = translate([
					'text' => 'Your cover has been uploaded successfully',
					'isAdminFacing' => true,
				]);
			}
		}
		try {
			chgrp($destFullPath, 'aspen_apache');
			chmod($destFullPath, 0775);
		} catch (Exception $e) {
			//Just ignore errors
		}
	}

	return $result;
}

function resizeImage($originalPath, $newPath, $maxWidth, $maxHeight) {
	global $logger;
	[
		$width,
		$height,
		$type,
	] = @getimagesize($originalPath);
	if ($image = @file_get_contents($originalPath, false)) {
		if (!$imageResource = @imagecreatefromstring($image)) {
			return false;
		} else {
			if ($width > $maxWidth || $height > $maxHeight) {
				// Images larger than constraints get scaled down to fit within
				// both maxWidth and maxHeight, thus preserving aspect ratio.
				$scaleWidth = $maxWidth / $width;
				$scaleHeight = $maxHeight / $height;
				$scale = min($scaleWidth, $scaleHeight);
				$new_width = floor($width * $scale);
				$new_height = floor($height * $scale);

				$tmp_img = imagecreatetruecolor($new_width, $new_height);
				imagealphablending($tmp_img, false);
				imagesavealpha($tmp_img, true);
				$transparent = imagecolorallocatealpha($tmp_img, 255, 255, 255, 127);
				imagefilledrectangle($tmp_img, 0, 0, $new_width, $new_height, $transparent);

				if (!imagecopyresampled($tmp_img, $imageResource, 0, 0, 0, 0, $new_width, $new_height, $width, $height)) {
					$logger->log("Could not resize image $originalPath to $newPath", Logger::LOG_ERROR);
					return false;
				}

				// save thumbnail into a file
				if (file_exists($newPath)) {
					$logger->log("File $newPath already exists, deleting", Logger::LOG_DEBUG);
					unlink($newPath);
				}

				if (!@imagepng($tmp_img, $newPath, 9)) {
					$logger->log("Could not save re-sized file $newPath", Logger::LOG_ERROR);
					return false;
				} else {
					return true;
				}
			} else {
				//Just copy the image over
				copy($originalPath, $newPath);
				return true;
			}
		}
	} else {
		return false;
	}
}