<?php

    require_once( 'check_install.php' );

    
    $username = $_SERVER['USER'];
    $ip = $_SERVER['SERVER_ADDR'];
    $homeUrl = 'http://' . $ip . '/~' . $username;

    header( 'Location: ' . $homeUrl . '/new-site-wizard' );
    exit();

?>