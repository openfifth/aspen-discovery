<?php
class AspenMobile_Admin {
	public static function getAdminSection()
	{
		$section = new AdminSection('Aspen Mobile');
		$section->addAction(new AdminAction('Notification Test Tool', 'Aspen Mobile notification test tool', '/AspenMobile/NotificationTestingTool'), [true]);
		$section->addAction(new AdminAction('Settings', 'Aspen Mobile settings', '/AspenMobile/Settings'), [true]);

		return $section;
	}
}
?>