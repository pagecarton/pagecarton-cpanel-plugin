<?php

    if( ! is_dir( $_SERVER['HOME'] . '/pagecarton/core/' ) )
    {
        require_once( '/usr/local/cpanel/3rdparty/bin/pagecarton/plugin/pc_install.php' );
    //    exit();
    }
    
    $username = $_SERVER['USER'];
    $ip = $_SERVER['SERVER_NAME'];
    $homeUrl = 'http://' . $ip . '/~' . $username;

    $installer = '/home/' . $username . '/public_html/pc_installer.php';

    $autoAuthId = md5( $username . $_SERVER['SERVER_ADDR'] . $_SERVER['SERVER_NAME'] . $_SERVER['REMOTE_PORT'] . $_SERVER['cp_security_token'] . time() . $_SERVER['HTTP_USER_AGENT'] . $_SERVER['HTTP_COOKIE'] );
    $autoAuthFile = '/home/' . $username . '/pagecarton/sites/default/application/auto-auth/' . $autoAuthId;

    mkdir( dirname( $autoAuthFile ), 0777, true );
    $authInfo = array(
        'username' => $_SERVER['USER'],
    //    'password' => $_SERVER['REMOTE_PASSWORD'],
        'email' => $_SERVER['USER'] . '@' . $_SERVER['SERVER_NAME'],
        'access_level' => 99,
    );
    file_put_contents( $autoAuthFile, json_encode( $authInfo ) );
    defined( 'PC_AUTO_AUTH_TOKEN' ) || define( 'PC_AUTO_AUTH_TOKEN', $autoAuthId );



?>