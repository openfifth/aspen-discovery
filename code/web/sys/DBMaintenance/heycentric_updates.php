<?php /** @noinspection SqlResolve */
function getHeyCentricUpdates() {
	return [
        'create_heyCentric_url_paramaters_table' => [
			'title' => 'Create HeyCentric URL Parameters Table',
			'description' => 'Add a list of existing current HeyCentric URL parameters',
			'sql' => [
				"CREATE TABLE heycentric_url_parameter (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					name VARCHAR(50) NOT NULL UNIQUE,
					multiline TINYINT(1) DEFAULT 0,
					optional TINYINT(1) DEFAULT 0,
					defaultValue VARCHAR(255) DEFAULT ''
				)"
			],
		], // create_heyCentric_url_paramaters_table
		'add_heyCentric_url_parameters' => [
			'title' => 'Add HeyCentric URL Parameters',
			'description' => 'Add a list of existing current HeyCentric URL parameters',
			'sql' => [	
				"INSERT INTO heycentric_url_parameter (name, multiline, optional) VALUES ('client', 0, 0)",
				"INSERT INTO heycentric_url_parameter (name, multiline, optional) VALUES ('area', 0, 0)",
				"INSERT INTO heycentric_url_parameter (name, multiline, optional) VALUES ('till', 0, 0)",
				"INSERT INTO heycentric_url_parameter (name, multiline, optional) VALUES ('entity', 0, 0)",
				"INSERT INTO heycentric_url_parameter (name, multiline, optional) VALUES ('co', 0, 1)",
				"INSERT INTO heycentric_url_parameter (name, multiline, optional) VALUES ('bu', 0, 1)",
				"INSERT INTO heycentric_url_parameter (name, multiline, optional) VALUES ('lang', 0, 1)",
				"INSERT INTO heycentric_url_parameter (name, multiline, optional) VALUES ('mode', 0, 1)",
				"INSERT INTO heycentric_url_parameter (name, multiline, optional) VALUES ('pmtTyp', 1, 1)",
				"INSERT INTO heycentric_url_parameter (name, multiline, optional, defaultValue) VALUES ('val1', 1, 0, 'The reference number as indicated on the the fine/debt in the ILS.')",
				"INSERT INTO heycentric_url_parameter (name, multiline, optional, defaultValue) VALUES ('val1Desc', 1, 0, 'The message as indicated on the on the fine/debt in the ILS.')",
				"INSERT INTO heycentric_url_parameter (name, multiline, optional) VALUES ('val2', 1, 0)",
				"INSERT INTO heycentric_url_parameter (name, multiline, optional) VALUES ('val2Desc', 1, 1)",
				"INSERT INTO heycentric_url_parameter (name, multiline, optional, defaultValue) VALUES ('am', 1, 0, 'The amount due as indicated on the on the fine/debt in the ILS.')",
				"INSERT INTO heycentric_url_parameter (name, multiline, optional) VALUES ('cmt', 1, 1)",
				"INSERT INTO heycentric_url_parameter (name, multiline, optional) VALUES ('extRef', 1, 1)",
				"INSERT INTO heycentric_url_parameter (name, multiline, optional, defaultValue) VALUES ('rurl', 0, 0, 'The URL of the /MyAccount/Fines page on the relevant Aspen Discovery site ')",
				"INSERT INTO heycentric_url_parameter (name, multiline, optional) VALUES ('burl', 0, 1)",
				"INSERT INTO heycentric_url_parameter (name, multiline, optional, defaultValue) VALUES ('email', 0, 1, 'The email of the patron paying the fine as stored in Aspen Discovery.')",
				"INSERT INTO heycentric_url_parameter (name, multiline, optional) VALUES ('ccemail', 0, 1)",
				"INSERT INTO heycentric_url_parameter (name, multiline, optional) VALUES ('sid', 0, 1)",
			],
		], // add_heyCentric_url_parameters
		'create_heyCentric_url_paramater_setting_table' => [
			'title' => 'Create HeyCentric URL Parameters Settings Table',
			'description' => 'Add a table to store HeyCentric URL parameter settings',
			'sql' => [
				"CREATE TABLE heycentric_url_parameter_setting (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					value VARCHAR(255) DEFAULT '',
					heyCentricUrlParameterId INT(11) DEFAULT -1,
					heyCentricSettingId INT(11) DEFAULT -1,
					includeInUrl TINYINT(1) DEFAULT 0,
					includeInHash TINYINT(1) DEFAULT 0,
					kohaAdditionalField VARCHAR(255) DEFAULT NULL
				)"
			],
		], // create_heyCentric_url_paramater_settings_table
        'add_heycentric_setting_table' => [
			'title' => 'HeyCentric Settings Are Stored',
			'description' => 'HeyCentric settings are stored so they can be administered and assigned to libraries',
			'continueOnError' => false,
			'sql' => [
				"CREATE TABLE heycentric_setting (
				id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
				name VARCHAR(50) NOT NULL UNIQUE,		
				baseUrl VARCHAR(50) NOT NULL,
				privateKey VARCHAR(255) NOT NULL
				)"
			]
		], // add_heycentric_setting_table
		'add_heyCentric_permissions' => [
			'title' => 'Create HeyCentric Permissions',
			'description' => 'Add an HeyCentric permission section containing the permissions to do with this module',
			'sql' => [
				"INSERT INTO permissions (name, sectionName, weight, description) VALUES ( 'Administer HeyCentric Settings','ecommerce', 10, 'Allows the user to administer the integration with HeyCentric')",
				"INSERT INTO role_permissions(roleId, permissionId) VALUES ((SELECT roleId from roles where name='opacAdmin'), (SELECT id from permissions where name='Administer HeyCentric Settings'))",
			],
		], // add_heyCentric_permissions
		'add_heycentric_setting_id_to_library' => [
			'title' => 'Add HeyCentric Setting Id To Library',
			'description' => 'Add an heyCentricSettingId property to libraries so that they can be assigned the relevant HeyCentric Setting',
			'sql' => [
				"ALTER TABLE library ADD heyCentricSettingId INT NOT NULL DEFAULT -1",
			],
		], // add_heycentric_setting_id_to_library
		'add_heycentric_setting_id_to_location' => [
			'title' => 'Add HeyCentric Setting Id To Location',
			'description' => 'Add an heyCentricSettingId property to locations so that they can be assigned the relevant HeyCentric Setting',
			'sql' => [
				"ALTER TABLE location ADD heyCentricSettingId INT NOT NULL DEFAULT -1",
			],
		], // add_heycentric_setting_id_to_location
		'add_declined_status_to_user_payment' => [
			'title' => 'Add Declined Status To User Payment',
			'description' => 'add a declined column to the user payment table so this payment attempt outcome is accounted for',
			'sql' => [
				"ALTER TABLE user_payments ADD declined TINYINT(1) DEFAULT 0",
			],
		], //'add_declined_status_to_user_payment'
		'add_heyCentricPaymentReferenceNumber_to_user_payment' => [
			'title' => 'Add HeyCentric Payment Reference Number To User Payment',
			'description' => 'stores the reference number for an approved payment as returned by HeyCentric',
			'sql' => [
				"ALTER TABLE user_payments ADD heyCentricPaymentReferenceNumber INT",
			],
		], // 'add_heyCentricPaymentReferenceNumber_to_user_payment'
    ];
}