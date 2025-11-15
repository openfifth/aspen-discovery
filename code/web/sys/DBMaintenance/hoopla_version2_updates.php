<?php

require_once ROOT_DIR . '/sys/SystemVariables.php';

function getHooplaVersion2Updates(): array {

	$systemVariables = SystemVariables::getSystemVariables();
	$hooplaVersion = ($systemVariables !== false && !empty($systemVariables->hooplaVersion)) ? (int)$systemVariables->hooplaVersion : 1;

	if ($hooplaVersion == 2) {
		return [
			'add_hoopla_country_code_to_hoopla_settings' => [
				'title' => 'Add Hoopla Country Code',
				'description' => 'Allow Hoopla settings to define country code',
				'continueOnError' => false,
				'sql' => [
					"ALTER TABLE hoopla_settings ADD COLUMN countryCode VARCHAR(2) DEFAULT 'US'",
				]
			], //add_hoopla_country_code_to_hoopla_settings
			'create_library_hoopla_settings_table' => [
				'title' => 'Create Library Hoopla Settings Table',
				'description' => 'Store Hoopla Library Information',
				'continueOnError' => false,
				'sql' => [
					'CREATE TABLE IF NOT EXISTS library_hoopla_settings (
						id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
						weight INT DEFAULT 0,
						settingId INT NOT NULL,
						libraryId INT NOT NULL,
						hooplaLibraryID VARCHAR(50) DEFAULT NULL,
						circulationEnabled TINYINT(1) DEFAULT 1,
						hooplaInstantEnabled TINYINT(1) DEFAULT 0,
						hooplaFlexEnabled TINYINT(1) DEFAULT 0,
						fullUpdateForLibrary TINYINT(1) DEFAULT 0,
						cleanUpInstant TINYINT(1) DEFAULT 0,
						cleanUpFlex TINYINT(1) DEFAULT 0,
						UNIQUE KEY librarySettingHoopla (libraryId, settingId)
					)'
				]
			], //create_library_hoopla_settings_table
			'create_hoopla_entitlements_table' => [
				'title' => 'Create Hoopla Entitlements Table',
				'description' => 'Store Hoopla library entitlement data',
				'continueOnError' => false,
				'sql' => [
					'CREATE TABLE IF NOT EXISTS hoopla_entitlements (
						id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
						hooplaId INT(11) NOT NULL,
						hooplaType VARCHAR(20) NOT NULL,
						UNIQUE KEY hooplaEntitlementUnique (hooplaId, hooplaType)
					)'
				]
			], //create_hoopla_entitlements_table
			'create_hoopla_entitlement_scopes_table' => [
				'title' => 'Create hoopla_entitlement_scopes table',
				'description' => 'Create hoopla_entitlement_scopes table',
				'continueOnError' => false,
				'sql' => [
					'CREATE TABLE IF NOT EXISTS hoopla_entitlement_scopes (
						entitlementId INT NOT NULL,
						scopeLibraryId INT NOT NULL,
						PRIMARY KEY (entitlementId, scopeLibraryId),
						KEY idx_scopeLibraryId (scopeLibraryId),
						CONSTRAINT fk_entitlement_scopes_entitlementId FOREIGN KEY (entitlementId) REFERENCES hoopla_entitlements(id) ON DELETE CASCADE ON UPDATE CASCADE
					)'
				]
			], //create_hoopla_entitlement_scopes_table
			'add_scopeLibraryId_to_hoopla_flex_availability' => [
				'title' => 'Add scopeLibraryId to hoopla_flex_availability table',
				'description' => 'Add scopeLibraryId to hoopla_flex_availability table',
				'continueOnError' => false,
				'sql' => [
					'ALTER TABLE hoopla_flex_availability ADD COLUMN scopeLibraryId INT NULL',
					'ALTER TABLE hoopla_flex_availability DROP INDEX hooplaId',
					'ALTER TABLE hoopla_flex_availability ADD UNIQUE KEY hooplaFlexScopeUnique (hooplaId, scopeLibraryId)'
				]
			], //add_scopeLibraryId_to_hoopla_flex_availability
			'migrate_hoopla_data_to_new_structure' => [
				'title' => 'Migrate Legacy Hoopla Data',
				'description' => 'Populate new Hoopla tables so existing libraries retain access prior to configuration changes',
				'continueOnError' => false,
				'sql' => [
					// Populate library_hoopla_settings using library-hoopla scopes
					"INSERT IGNORE INTO library_hoopla_settings (weight, settingId, libraryId, hooplaLibraryID, circulationEnabled, hooplaInstantEnabled, hooplaFlexEnabled)
						SELECT
							COALESCE((SELECT MAX(existing.weight) + 1 FROM library_hoopla_settings existing WHERE existing.libraryId = l.libraryId), 0) AS weight,
							hs.settingId,
							l.libraryId,
							CASE WHEN l.hooplaLibraryID IS NULL OR l.hooplaLibraryID = 0 THEN NULL ELSE CAST(l.hooplaLibraryID AS CHAR) END AS hooplaLibraryID,
							CASE WHEN l.hooplaLibraryID IS NOT NULL AND l.hooplaLibraryID <> 0 THEN 1 ELSE 0 END AS circulationEnabled,
							CASE WHEN hs.includeInstant = 1 AND s.hooplaInstantEnabled = 1 THEN 1 ELSE 0 END AS hooplaInstantEnabled,
							CASE WHEN hs.includeFlex = 1 AND s.hooplaFlexEnabled = 1 THEN 1 ELSE 0 END AS hooplaFlexEnabled
						FROM library l
						INNER JOIN hoopla_scopes hs ON hs.id = l.hooplaScopeId
						INNER JOIN hoopla_settings s ON s.id = hs.settingId
						WHERE l.hooplaScopeId > -1",
					// Populate new entitlements table for Instant content
					"INSERT IGNORE INTO hoopla_entitlements (hooplaId, hooplaType)
						SELECT DISTINCT he.hooplaId, 'Instant'
						FROM hoopla_export he
						WHERE he.hooplaId IS NOT NULL
							AND (he.hooplaType IS NULL OR UPPER(he.hooplaType) <> 'FLEX')",
					// Populate new entitlements table for Flex content
					"INSERT IGNORE INTO hoopla_entitlements (hooplaId, hooplaType)
						SELECT DISTINCT hfa.hooplaId, 'Flex'
						FROM hoopla_flex_availability hfa
						WHERE hfa.hooplaId IS NOT NULL",
					// Attach Instant entitlements to all libraries that previously had Instant enabled
					"INSERT IGNORE INTO hoopla_entitlement_scopes (entitlementId, scopeLibraryId)
						SELECT he.id, lhs.libraryId
						FROM hoopla_entitlements he
						INNER JOIN library_hoopla_settings lhs ON lhs.hooplaInstantEnabled = 1
						WHERE he.hooplaType = 'Instant'",
					// Attach Flex entitlements to libraries that previously had Flex enabled
					"INSERT IGNORE INTO hoopla_entitlement_scopes (entitlementId, scopeLibraryId)
						SELECT he.id, lhs.libraryId
						FROM hoopla_entitlements he
						INNER JOIN library_hoopla_settings lhs ON lhs.hooplaFlexEnabled = 1
						WHERE he.hooplaType = 'Flex'",
					// Assign a flex-enabled library to legacy availability rows missing scopeLibraryId
					"UPDATE hoopla_flex_availability hfa
						JOIN (
							SELECT base.id,
								COALESCE(MIN(lhs.libraryId), 0) AS targetLibraryId
								FROM hoopla_flex_availability base
								LEFT JOIN hoopla_entitlements he ON he.hooplaId = base.hooplaId AND he.hooplaType = 'Flex'
								LEFT JOIN hoopla_entitlement_scopes hes ON hes.entitlementId = he.id
								LEFT JOIN library_hoopla_settings lhs ON lhs.libraryId = hes.scopeLibraryId AND lhs.hooplaFlexEnabled = 1
								WHERE base.scopeLibraryId IS NULL OR base.scopeLibraryId = 0
								GROUP BY base.id
							) picked ON picked.id = hfa.id
						SET hfa.scopeLibraryId = picked.targetLibraryId
						WHERE picked.targetLibraryId <> 0",
					// Duplicate availability rows so each flex-enabled library receives a copy
					"INSERT INTO hoopla_flex_availability (hooplaId, holdsQueueSize, availableCopies, totalCopies, status, scopeLibraryId)
						SELECT base.hooplaId,
							base.holdsQueueSize,
							base.availableCopies,
							base.totalCopies,
							base.status,
							lhs.libraryId
						FROM (
								SELECT hfa.*
								FROM hoopla_flex_availability hfa
								JOIN (
									SELECT hooplaId, MIN(id) AS minId
									FROM hoopla_flex_availability
									GROUP BY hooplaId
								) first_row ON first_row.hooplaId = hfa.hooplaId AND first_row.minId = hfa.id
							) base
						JOIN hoopla_entitlements he ON he.hooplaId = base.hooplaId AND he.hooplaType = 'Flex'
						JOIN hoopla_entitlement_scopes hes ON hes.entitlementId = he.id
						JOIN library_hoopla_settings lhs ON lhs.libraryId = hes.scopeLibraryId AND lhs.hooplaFlexEnabled = 1
						LEFT JOIN hoopla_flex_availability existing
							ON existing.hooplaId = base.hooplaId
							AND existing.scopeLibraryId = lhs.libraryId
						WHERE existing.id IS NULL",
					// Populate the higher timestamps into the fields we will keep
					"UPDATE hoopla_settings
						SET lastUpdateOfChangedRecordsInstant = GREATEST(
							COALESCE(lastUpdateOfChangedRecordsInstant, 0),
							COALESCE(lastUpdateOfChangedRecordsFlex, 0)
						),
						lastUpdateOfAllRecordsInstant = GREATEST(
							COALESCE(lastUpdateOfAllRecordsInstant, 0),
							COALESCE(lastUpdateOfAllRecordsFlex, 0)
						)"
				]
			], //migrate_hoopla_data_to_new_structure
			'reset_scopeLibraryId_to_not_null' => [
				'title' => 'Reset scopeLibraryId to not null',
				'description' => 'Reset scopeLibraryId to not null',
				'continueOnError' => false,
				'sql' => [
					'ALTER TABLE hoopla_flex_availability MODIFY COLUMN scopeLibraryId INT NOT NULL'
				]
			], //reset_scopeLibraryId_to_not_null
			'drop_unused_hoopla_scopes_columns' => [
				'title' => 'Drop Unused Hoopla Scopes Columns',
				'description' => 'Drop unused Hoopla scopes columns',
				'continueOnError' => false,
				'sql' => [
					"ALTER TABLE hoopla_scopes DROP column includeInstant",
					"ALTER TABLE hoopla_scopes DROP column includeFlex",
				]
			], //drop_unused_hoopla_scopes_columns
			'update_hoopla_settings' => [
				'title' => 'Add Global Contents Hoopla Settings',
				'description' => 'Update Hoopla settings',
				'continueOnError' => false,
				'sql' => [
					"ALTER TABLE hoopla_settings ADD COLUMN lastRecordProcessed VARCHAR(30) DEFAULT '0'",
					"ALTER TABLE hoopla_settings CHANGE COLUMN lastUpdateOfChangedRecordsInstant lastUpdateOfChangedRecords INT(11) DEFAULT 0",
					"ALTER TABLE hoopla_settings CHANGE COLUMN lastUpdateOfAllRecordsInstant lastUpdateOfAllRecords INT(11) DEFAULT 0",
					"ALTER TABLE hoopla_settings DROP COLUMN lastUpdateOfChangedRecordsFlex",
					"ALTER TABLE hoopla_settings DROP COLUMN lastUpdateOfAllRecordsFlex",
					"ALTER TABLE hoopla_settings DROP COLUMN hooplaFlexEnabled",
					"ALTER TABLE hoopla_settings DROP COLUMN hooplaInstantEnabled",
					"ALTER TABLE hoopla_settings DROP COLUMN runFullUpdateFlex",
					"ALTER TABLE hoopla_settings DROP COLUMN runFullUpdateInstant",
					"ALTER TABLE hoopla_settings ADD COLUMN runFullUpdate TINYINT(1) DEFAULT 0",
				]
			], //update_hoopla_settings
			'update_hoopla_export_table' => [
				'title' => 'Update Hoopla Export Table',
				'description' => 'Update Hoopla export table',
				'continueOnError' => false,
				'sql' => [
					"ALTER TABLE hoopla_export DROP COLUMN active",
					"ALTER TABLE hoopla_export DROP COLUMN hooplaType",
					"ALTER TABLE hoopla_export CHANGE COLUMN kind format VARCHAR(50) DEFAULT NULL",
					"ALTER TABLE hoopla_export CHANGE COLUMN price ppuPrice DOUBLE NOT NULL DEFAULT 0",
				]
			], //update_hoopla_export_table
			'drop_hooplaLibraryID_from_library_table' => [
				'title' => 'Drop hooplaLibraryID from library table',
				'description' => 'Drop hooplaLibraryID from library table',
				'continueOnError' => false,
				'sql' => [
					'ALTER TABLE library DROP COLUMN hooplaLibraryID',
				]
			], //drop_hooplaLibraryID_from_library_table
			'update_hoopla_export_log' => [
				'title' => 'Add entitlements to hoopla export log',
				'description' => 'Add entitlements to hoopla export log',
				'continueOnError' => false,
				'sql' => [
					'ALTER TABLE hoopla_export_log ADD COLUMN numEntitlementsUpdated INT DEFAULT 0',
					'ALTER TABLE hoopla_export_log ADD COLUMN numEntitlementsDeleted INT DEFAULT 0',
					'ALTER TABLE hoopla_export_log DROP COLUMN numSkipped',
				]
			], //update_hoopla_export_log
			'test_test' => [
				'title' => 'Test test',
				'description' => 'Test test',
				'continueOnError' => false,
				'sql' => [
					'ALTER TABLE test ADD COLUMN test INT DEFAULT 0',
				]
			], //test_test
			'test_test2' => [
				'title' => 'Test test2',
				'description' => 'Test test2',
				'continueOnError' => false,
				'sql' => [
					'ALTER TABLE test ADD COLUMN test2 INT DEFAULT 0',
				]
			], //test_test2
		];
	}
	return [];
}
