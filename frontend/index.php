<?php


    require_once( '/usr/local/cpanel/3rdparty/bin/pagecarton/plugin/frontend/check_install.php' );

    header( 'Location: ' . $homeUrl . '?pc_auto_auth=' . PC_AUTO_AUTH_TOKEN );
    exit();

?>