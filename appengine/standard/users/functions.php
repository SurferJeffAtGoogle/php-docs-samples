<?php

use google\appengine\api\users\User;
use google\appengine\api\users\UserService;

function getGreeting()
{
    $user = UserService::getCurrentUser();

    if (isset($user)) {
        return sprintf('Welcome, %s! (<a href="%s">sign out</a>)',
            $user->getNickname(),
            UserService::createLogoutUrl('/'));
    } else {
        return sprintf('<a href="%s">Sign in or register</a>',
            UserService::createLoginUrl('/'));
    }
}