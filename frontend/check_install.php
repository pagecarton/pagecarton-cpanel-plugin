<?php

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL); 

    require_once( '/usr/local/cpanel/3rdparty/bin/pagecarton/functions.php' );
    $username = $_SERVER['USER'];
    $ip = $_SERVER['SERVER_NAME'];
    $homeUrl = null;

    if( ! is_dir( $_SERVER['HOME'] . '/pagecarton/core/' ) )
    {
        $switches = array( '--add' );
        require_once( '/usr/local/cpanel/3rdparty/bin/pagecarton/pc_installer.php' );
        install();

        if( ! is_dir( $_SERVER['HOME'] . '/pagecarton/core/' ) )
        {
        //    require_once( '/usr/local/cpanel/3rdparty/bin/pagecarton/frontend/pc_installer.php' );
            if( ! is_dir( $_SERVER['HOME'] . '/pagecarton/core/' ) )
            {
                echo 'PageCarton Could not be installed automatically.'; 
                exit();
            } 
    
        } 

    }


    $pcCheckFile = '/home/' . $username . '/public_html/check-server-access.txt';
    file_put_contents( $pcCheckFile, 'pc' );

    chmod( $pcCheckFile, 0644 );
    chown( $pcCheckFile, $username );
    chgrp( $pcCheckFile, $username );

    $domainStore = '/home/' . $username . '/public_html/domain';
    if( is_file( $domainStore ) AND $domain = file_get_contents( $domainStore ) )
    {
        if( 'pc' === fetchLink( $domain . '/' . basename( $pcCheckFile ) ) )
        {
            $homeUrl = 'http://' . $domain;
        }
    }
 //   var_export( 'http://' . $domain . '/' );  
//    var_export( fetchLink( 'http://' . $domain . '/' ) );  
//    var_export( $_SERVER );   
    if( ! $homeUrl )
    {
        if( 'pc' !== fetchLink( 'http://' . $ip . '/~' . $username . '/' . basename( $pcCheckFile ) ) )
        {
        //    var_export( 'http://' . $ip . '/~' . $username . '/' . basename( $pcCheckFile ) );   
        //    var_export( fetchLink( 'http://' . $ip . '/~' . $username . '/' . basename( $pcCheckFile ) ) );   
            echo 'ERROR! Domain name "' . $domain . '" is not yet accessible on this server. Please contact the technical department.';
            exit(); 
            $homeUrl = 'http://' . $ip . '/~' . $username;
        }
        else
        {
            $homeUrl = 'http://' . $ip . '/~' . $username;
        }
    }


    $autoAuthId = md5( $username . $_SERVER['SERVER_ADDR'] . $_SERVER['SERVER_NAME'] . $_SERVER['REMOTE_PORT'] . $_SERVER['cp_security_token'] . time() . $_SERVER['HTTP_USER_AGENT'] . $_SERVER['HTTP_COOKIE'] );
    $autoAuthFile = '/home/' . $username . '/pagecarton/sites/default/application/auto-auth/' . $autoAuthId;

    //  create dir
    $dirExplode = explode( '/', dirname( $autoAuthFile ) );
    $dirNow = null;
    foreach( $dirExplode as $each )
    {
        if( ! trim( $each ) )
        {
            continue;
        }
        $dirNow .= '/' . $each;
        if( ! is_dir( $dirNow ) )
        {
            mkdir( $dirNow, 0700, true );
            chmod( $dirNow, 0700 );
            chown( $dirNow, $username );
            chgrp( $dirNow, $username );
        }    
    }

    $authInfo = array(
        'username' => $username,
    //    'password' => $_SERVER['REMOTE_PASSWORD'],
        'email' => $username . '@' . $_SERVER['SERVER_NAME'],
        'access_level' => 99,
    );
    file_put_contents( $autoAuthFile, json_encode( $authInfo ) );

    chmod( $autoAuthFile, 0644 );
    chown( $autoAuthFile, $username );
    chgrp( $autoAuthFile, $username );
    defined( 'PC_AUTO_AUTH_TOKEN' ) || define( 'PC_AUTO_AUTH_TOKEN', $autoAuthId );

?>
