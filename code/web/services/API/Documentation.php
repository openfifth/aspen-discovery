<?php

global $configArray;
require_once ROOT_DIR . '/Action.php';

class API_Documentation extends Action {
    function launch() : void {
        global $interface;

        $openApiPath = ROOT_DIR . '/openapi';
        $apiFiles = [];

        if (is_dir($openApiPath)) {
            foreach (scandir($openApiPath) as $apiFile) {
                if (preg_match('/_openapi\.json$/i', $apiFile) && is_file($openApiPath . '/' . $apiFile)) {
                    $apiFiles[str_ireplace('_openapi.json', '', $apiFile)] = $apiFile;
                }
            }
        }

        if (empty($apiFiles)) {
            $activeApiFile = '';
            $activeApiFileFullPath = '';
        } else {
            if (isset($_REQUEST['api']) && array_key_exists($_REQUEST['api'], $apiFiles)) {
                $activeApiFile = $_REQUEST['api'];
            } else {
                $keys = array_keys($apiFiles);
                $activeApiFile = $keys[0];
            }
            $activeApiFileFullPath = '/openapi/' . $apiFiles[$activeApiFile];
        }

        $apiBasePath = "/API/$activeApiFile?method=";

        $interface->assign('apiFiles', $apiFiles);
        $interface->assign('activeApiFile', $activeApiFile);
        $interface->assign('activeApiFileFullPath', $activeApiFileFullPath);
        $interface->assign('apiBasePath', $apiBasePath);
        $interface->assign('showBreadcrumbs', true);
        $interface->assign('showContentAsFullWidth', true);

        if (UserAccount::isLoggedIn() && count(UserAccount::getActivePermissions()) > 0) {
            $adminActions = UserAccount::getActiveUserObj()->getAdminActions();
            $interface->assign('adminActions', $adminActions);
            $interface->assign('activeAdminSection', $this->getActiveAdminSection());
            $interface->assign('activeMenuOption', 'admin');
            $sidebar = 'Admin/admin-sidebar.tpl';
        } else {
            $sidebar = '';
        }
        $this->display('apiDocumentation.tpl', 'Aspen API Documentation', $sidebar);
    }

    function getBreadcrumbs(): array {
        $breadcrumbs = [];
        if (UserAccount::isLoggedIn() && count(UserAccount::getActivePermissions()) > 0) {
            $breadcrumbs[] = new Breadcrumb('/Admin/Home', 'Administration Home');
            $breadcrumbs[] = new Breadcrumb('/Admin/Home#support', 'Aspen Discovery Support');
        }
        $breadcrumbs[] = new Breadcrumb('', 'API Documentation');
        return $breadcrumbs;
    }

    function getActiveAdminSection(): string {
        return 'support';
    }

    function canView(): bool {
        return true;
    }
}