<?php
/** @noinspection SqlDialectInspection */

/** @noinspection PhpUnused */
function getUpdates26_05_00(): array {
	$now = time();

	return [
		/*'name' => [
			 'title' => '',
			 'description' => '',
			 'continueOnError' => false,
			 'sql' => [
				 ''
			 ]
		 ], //name*/

		//mark n

		//kirstien

		//kodi
		'materials_request_title' => [
			'title' => 'Add Materials Request Title table',
			'description' => 'Add table to facilitate grouping materials requests by title.',
			'sql' => [
				'CREATE TABLE IF NOT EXISTS materials_request_title  (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
					title varchar(255),
					author varchar(255),
					format varchar(25),
					formatId int(10),
					isbn varchar(15),
					upc varchar(15),
					issn varchar(8),
    				comments varchar(255),
					hasExistingRecord tinyint(1),
					lastCheckForExistingRecord int(11),
					existingRecordUrl tinytext,
					dateFirstRequested int(11),
					dateLastRequested int(11)
				) ENGINE = InnoDB',
			]
		], //materials_request_title
		'materials_request_title_id' => [
			'title' => 'Add Column for Materials Request Title ID to Materials Request Table',
			'description' => 'Add column materialsRequestTitleId to materials_request to connect materials_request to materials_request_title.',
			'sql' => [
				"ALTER TABLE materials_request ADD COLUMN materialsRequestTitleId INT(11)"
			]
		], //materials_request_title_id
		'move_materials_request_info' => [
			'title' => 'Migrate Materials Request Info',
			'description' => 'Migrate data from materials_request to materials_request_title.',
			'continueOnError' => false,
			'sql' => [
				'migrateMaterialsRequestTitleData',
			]
		], //move_materials_request_info

		//yanjun

		//imani

		//galen

		//chloe

		//pedro

		//mark j

		//lucas

		//tomas

		// stephen


		//pedro

		//other

	];
}

function normalizeAuthorTitleString(string $value): string {
	//remove extra/leading/trailing spaces and special characters
	$value = trim($value);
	$value = preg_replace('/[^a-zA-Z0-9 ]/', '', $value);
	$value = preg_replace('/\s+/', ' ', $value);
	return strtolower($value);
}

function migrateMaterialsRequestTitleData(): void {
	global $aspen_db;

	$titleColumns = [
		'title', 'author', 'format', 'formatId', 'isbn', 'season', 'upc', 'issn',
		'hasExistingRecord', 'lastCheckForExistingRecord', 'existingRecordUrl'
	];
	$columnList = implode(', ', $titleColumns);

	$requests = $aspen_db->query(
		"SELECT id, dateCreated, $columnList FROM materials_request WHERE materialsRequestTitleId IS NULL"
	);
	$rows = $requests->fetchAll(PDO::FETCH_ASSOC);

	if (empty($rows)) {
		echo "Nothing to migrate.\n";
		return;
	}

	foreach ($rows as $row) {
		try {
			$requestId   = $row['id'];
			$dateCreated = isset($row['dateCreated']) && $row['dateCreated'] !== '' ? (int)$row['dateCreated'] : null;
			$titleId = null;

			// --- Step 1: Match on isbn, upc, or issn ---
			$identifiers = [];
			$params      = [];

			foreach (['isbn', 'upc', 'issn'] as $field) {
				$val = trim($row[$field] ?? '');
				if ($val !== '') {
					$identifiers[] = "$field = :$field";
					$params[":$field"] = $val;
				}
			}

			if (!empty($identifiers)) {
				$whereClause = implode(' OR ', $identifiers);
				$stmt = $aspen_db->prepare(
					"SELECT id FROM materials_request_title WHERE $whereClause LIMIT 1"
				);
				$stmt->execute($params);
				$existing = $stmt->fetch(PDO::FETCH_ASSOC);
				if ($existing) {
					$titleId = $existing['id'];
				}
			}

			// --- Step 2: Fallback — title + author + format ---
			if ($titleId === null
				&& !empty(trim($row['title'] ?? ''))
				&& !empty(trim($row['author'] ?? ''))
				&& !empty(trim($row['format'] ?? ''))
			) {
				$stmt = $aspen_db->prepare(
					"SELECT id FROM materials_request_title
                     WHERE LOWER(TRIM(REGEXP_REPLACE(REGEXP_REPLACE(title,  '[^a-zA-Z0-9 ]+', ''), '\\\\s+', ' '))) = :title
                       AND LOWER(TRIM(REGEXP_REPLACE(REGEXP_REPLACE(author, '[^a-zA-Z0-9 ]+', ''), '\\\\s+', ' '))) = :author
                       AND format = :format
                     LIMIT 1"
				);
				$stmt->execute([
					':title'  => normalizeAuthorTitleString($row['title']),
					':author' => normalizeAuthorTitleString($row['author']),
					':format' => $row['format'],
				]);
				$existing = $stmt->fetch(PDO::FETCH_ASSOC);
				if ($existing) {
					$titleId = $existing['id'];
				}
			}

			// --- Step 3: Fallback — title + author ---
			if ($titleId === null
				&& !empty(trim($row['title'] ?? ''))
				&& !empty(trim($row['author'] ?? ''))
			) {
				$stmt = $aspen_db->prepare(
					"SELECT id FROM materials_request_title
                     WHERE LOWER(TRIM(REGEXP_REPLACE(REGEXP_REPLACE(title,  '[^a-zA-Z0-9 ]+', ''), '\\\\s+', ' '))) = :title
                       AND LOWER(TRIM(REGEXP_REPLACE(REGEXP_REPLACE(author, '[^a-zA-Z0-9 ]+', ''), '\\\\s+', ' '))) = :author
                     LIMIT 1"
				);
				$stmt->execute([
					':title'  => normalizeAuthorTitleString($row['title']),
					':author' => normalizeAuthorTitleString($row['author']),
				]);
				$existing = $stmt->fetch(PDO::FETCH_ASSOC);
				if ($existing) {
					$titleId = $existing['id'];
				}
			}

			// --- Step 4: Fallback — title only ---
			if ($titleId === null && !empty(trim($row['title'] ?? ''))) {
				$stmt = $aspen_db->prepare(
					"SELECT id FROM materials_request_title
                     WHERE LOWER(TRIM(REGEXP_REPLACE(REGEXP_REPLACE(title, '[^a-zA-Z0-9 ]+', ''), '\\\\s+', ' '))) = :title
                     LIMIT 1"
				);
				$stmt->execute([':title' => normalizeAuthorTitleString($row['title'])]);
				$existing = $stmt->fetch(PDO::FETCH_ASSOC);
				if ($existing) {
					$titleId = $existing['id'];
				}
			}

			// --- Step 5: Update matched title row ---
			if ($titleId !== null) {
				$updates      = [];
				$updateParams = [':id' => $titleId];

				foreach ($titleColumns as $col) {
					$val = $row[$col] ?? null;
					if ($val !== null && $val !== '') {
						$updates[]             = "$col = COALESCE($col, :$col)";
						$updateParams[":$col"] = $val;
					}
				}

				if ($dateCreated !== null) {
					// Fetch current date values from the matched title row
					$dateStmt = $aspen_db->prepare(
						"SELECT dateFirstRequested, dateLastRequested FROM materials_request_title WHERE id = :id"
					);
					$dateStmt->execute([':id' => $titleId]);
					$currentDates = $dateStmt->fetch(PDO::FETCH_ASSOC);

					$currentFirst = $currentDates['dateFirstRequested'];
					$currentLast  = $currentDates['dateLastRequested'];

					if ($currentFirst === null || $dateCreated < $currentFirst) {
						$updates[] = "dateFirstRequested = :dateFirstRequested";
						$updateParams[':dateFirstRequested'] = $dateCreated;
					}

					if ($currentLast === null || $dateCreated > $currentLast) {
						$updates[] = "dateLastRequested = :dateLastRequested";
						$updateParams[':dateLastRequested'] = $dateCreated;
					}
				}

				if (!empty($updates)) {
					$updateSql = "UPDATE materials_request_title SET "
						. implode(', ', $updates)
						. " WHERE id = :id";
					$aspen_db->prepare($updateSql)->execute($updateParams);
				}
			}

			// --- Step 6: No match — insert a new title row ---
			if ($titleId === null) {
				$insertValues = [];
				$insertParams = [];

				foreach ($titleColumns as $col) {
					$insertValues[]        = ":$col";
					$insertParams[":$col"] = $row[$col] ?? null;
				}

				$insertParams[':dateFirstRequested'] = $dateCreated;
				$insertParams[':dateLastRequested']  = $dateCreated;

				$insertSql = "INSERT INTO materials_request_title ($columnList, dateFirstRequested, dateLastRequested)
                              VALUES (" . implode(', ', $insertValues) . ", :dateFirstRequested, :dateLastRequested)";
				$stmt = $aspen_db->prepare($insertSql);
				$stmt->execute($insertParams);
				$titleId = $aspen_db->lastInsertId();
			}

			// --- Step 7: Link the request to the title row ---
			$aspen_db->prepare(
				"UPDATE materials_request SET materialsRequestTitleId = :titleId WHERE id = :requestId"
			)->execute([':titleId' => $titleId, ':requestId' => $requestId]);

		} catch (Throwable $e) {
			echo "Skipped request ID {$row['id']}: " . $e->getMessage() . "\n";
		}
	}

	echo "Migration complete. " . count($rows) . " request(s) processed.\n";
}