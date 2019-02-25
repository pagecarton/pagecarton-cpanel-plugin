<?php

//    ini_set('display_errors', 1);
//    ini_set('display_startup_errors', 1);
//    error_reporting(E_ALL);

    if( ! is_dir( $_SERVER['HOME'] . '/pagecarton/core/' ) )
    {
        require_once( '/usr/local/cpanel/3rdparty/bin/pagecarton/frontend/pc_installer.php' );
    //    exit();
    } 
        
    $username = $_SERVER['USER'];
    $ip = $_SERVER['SERVER_NAME'];
    $homeUrl = 'http://' . $ip . '/~' . $username;

    $installer = '/home/' . $username . '/public_html/pc_installer.php';

    $autoAuthId = md5( $username . $_SERVER['SERVER_ADDR'] . $_SERVER['SERVER_NAME'] . $_SERVER['REMOTE_PORT'] . $_SERVER['cp_security_token'] . time() . $_SERVER['HTTP_USER_AGENT'] . $_SERVER['HTTP_COOKIE'] );
    $autoAuthFile = '/home/' . $username . '/pagecarton/sites/default/application/auto-auth/' . $autoAuthId;

    if( ! is_dir( dirname( $autoAuthFile ) ) )
    {
        mkdir( dirname( $autoAuthFile ), 0777, true );

        chmod( '/home/' . $username . '/pagecarton', 0644 );
        chown( '/home/' . $username . '/pagecarton', $username );
        chgrp( '/home/' . $username . '/pagecarton', $username );
    
        chmod( '/home/' . $username . '/pagecarton/sites', 0644 );
        chown( '/home/' . $username . '/pagecarton/sites', $username );
        chgrp( '/home/' . $username . '/pagecarton/sites', $username );
    
        chmod( '/home/' . $username . '/pagecarton/sites/default', 0644 );
        chown( '/home/' . $username . '/pagecarton/sites/default', $username );
        chgrp( '/home/' . $username . '/pagecarton/sites/default', $username );
    
        chmod( '/home/' . $username . '/pagecarton/sites/default/application', 0644 );
        chown( '/home/' . $username . '/pagecarton/sites/default/application', $username );
        chgrp( '/home/' . $username . '/pagecarton/sites/default/application', $username );
    
        chmod( '/home/' . $username . '/pagecarton/sites/default/application', 0644 );
        chown( '/home/' . $username . '/pagecarton/sites/default/application', $username );
        chgrp( '/home/' . $username . '/pagecarton/sites/default/application', $username );
    
        chmod( '/home/' . $username . '/pagecarton/sites/default/application/auto-auth', 0644 );
        chown( '/home/' . $username . '/pagecarton/sites/default/application/auto-auth', $username );
        chgrp( '/home/' . $username . '/pagecarton/sites/default/application/auto-auth', $username ); 
    }

    $authInfo = array(
        'username' => $_SERVER['USER'],
    //    'password' => $_SERVER['REMOTE_PASSWORD'],
        'email' => $_SERVER['USER'] . '@' . $_SERVER['SERVER_NAME'],
        'access_level' => 99,
    );
    file_put_contents( $autoAuthFile, json_encode( $authInfo ) );

    chmod( $autoAuthFile, 0644 );
    chown( $autoAuthFile, $username );
    chgrp( $autoAuthFile, $username );
    defined( 'PC_AUTO_AUTH_TOKEN' ) || define( 'PC_AUTO_AUTH_TOKEN', $autoAuthId );



?>