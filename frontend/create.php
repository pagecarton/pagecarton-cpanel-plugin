<?php

    require_once( '/usr/local/cpanel/3rdparty/bin/pagecarton/frontend/check_install.php' );
   
    $username = $_SERVER['USER'];
    $ip = $_SERVER['SERVER_NAME'];
    $homeUrl = 'http://' . $ip . '/~' . $username;

    $pcCheckFile = '/home/' . $username . '/public_html/check-server-access.txt';
    file_put_contents( $pcCheckFile, 'pc' );

    $domainStore = '/home/' . $username . '/public_html/domain';
    $domain = file_get_contents( $domainStore );
    if( 'pc' == fetchLink( $domain . '/' . basename( $pcCheckFile ) ) )
    {
        $homeUrl = 'http://' . $domain;
    }

    header( 'Location: ' . $homeUrl . '/widgets/PageCarton_NewSiteWizard?pc_auto_auth=' . PC_AUTO_AUTH_TOKEN );
    exit();

?>