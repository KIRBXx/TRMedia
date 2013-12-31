<?php

return array(
    'secret'   => array(
        //put your app id and secret
        'appId'  => '504325102999321',
        'secret' => '5ca869a91611b1358f081fe137ca4758'
    ),
    //Redirect after successfull login
    'redirect' => url('/login/facebook'),
    //When Someone Logout from your Site
    'logout'   => url('/logout'),
    //you can add scope according to your requirement
    'scope'    => 'email'
);
