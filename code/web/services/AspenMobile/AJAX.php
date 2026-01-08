<?php

require_once ROOT_DIR . '/JSON_Action.php';

class AspenMobile_AJAX extends JSON_Action {

    function test()
    {
        require_once ROOT_DIR . '/sys/Account/User.php';
        $user = new User();
        $user->id = 4;
        if($user->find(true))
        {
            $body = [
                "title" => "test alert",
                "body"=>  "this is a message",
            ];
            $result = $user->sendPushNotification($body, "test_message");
            return ["result" => $result];
        }
        return [
            "result" => "hello world",
            "loggedin" => UserAccount::isLoggedIn()
        ];
    }

    function saveNotificationPushToken()
    {
        require_once ROOT_DIR . '/services/API/UserAPI.php';
        $api = new UserAPI('internal');
        return $api->saveNotificationPushToken();
    }
}