<?php


    require_once( '/usr/local/cpanel/3rdparty/bin/pagecarton/frontend/check_install.php' );


    $username = $_SERVER['USER'];
    $ip = $_SERVER['SERVER_NAME'];
    $homeUrl = 'http://' . $ip . '/~' . $username;

    header( 'Location: ' . $homeUrl . '?pc_auto_auth=' . PC_AUTO_AUTH_TOKEN );
    exit();

?>