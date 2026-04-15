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

		//yanjun

		//imani

		//galen

		//chloe
		'add_enable_patron_ils_registration_by_staff' => [
			'title' => 'Add Enable Patron ILS Registration By Staff Library Setting',
			'description' => 'Add library setting to enable staff to register new ILS patrons from within Aspen.',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE library ADD COLUMN enablePatronIlsRegistrationByStaff TINYINT(1) NOT NULL DEFAULT 0",
			],
		], //add_enable_patron_ils_registration_by_staff
		'add_register_new_ils_patrons_permissions' => [
			'title' => 'Add Register New ILS Patrons Permission Family',
			'description' => 'Add Patron Management permissions allowing staff to register new ILS patrons, scoped by home library / location, mirroring the Masquerade scoping pattern.',
			'continueOnError' => false,
			'sql' => [
				"INSERT INTO permissions (sectionName, name, requiredModule, weight, description) VALUES
					('Patron Management', 'Register New ILS Patrons for any home library', '', 30, 'Allows the user to register new ILS patrons with any home library.'),
					('Patron Management', 'Register New ILS Patrons for patrons with same home library', '', 31, 'Allows the user to register new ILS patrons whose home library matches the staff member''s.'),
					('Patron Management', 'Register New ILS Patrons for patrons with same home location', '', 32, 'Allows the user to register new ILS patrons whose home location matches the staff member''s.')
				",
				"INSERT INTO role_permissions(roleId, permissionId) VALUES ((SELECT roleId from roles where name='opacAdmin'), (SELECT id from permissions where name='Register New ILS Patrons for any home library'))",
			],
		], //add_register_new_ils_patrons_permissions

		//pedro

		//mark j

		//lucas

		//tomas

		// stephen


		//pedro

		//other

	];
}
