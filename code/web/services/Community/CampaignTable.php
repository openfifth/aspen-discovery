<?php
class Community_CampaignTable extends Action {
    
    function launch() {
        global $interface;
        $this->display('campaignTable.tpl', 'Campaign Table');
    }

    function getBreadcrumbs(): array
    {
        $breadcrumbs = [];
        return $breadcrumbs;
    }
}