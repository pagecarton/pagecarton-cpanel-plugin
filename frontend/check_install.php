<?php

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL); 

    $username = $_SERVER['USER'];
    $ip = $_SERVER['SERVER_NAME'];
    $homeUrl = 'http://' . $ip . '/~' . $username;

    $pcCheckFile = '/home/' . $username . '/public_html/check-server-access.txt';
    file_put_contents( $pcCheckFile, 'pc' );

    chmod( $pcCheckFile, 0644 );
    chown( $pcCheckFile, $username );
    chgrp( $pcCheckFile, $username );

    $domainStore = '/home/' . $username . '/public_html/domain';
    $domain = file_get_contents( $domainStore );
    if( 'pc' === fetchLink( $domain . '/' . basename( $pcCheckFile ) ) )
    {
        $homeUrl = 'http://' . $domain;
    }
    else
    {
        if( 'pc' !== fetchLink( 'http://' . $ip . '/' . basename( $pcCheckFile ) ) )
        {
            echo 'ERROR! Domain name ' . $domain . 'is no longer available. Please contact the technical department.';
            exit();
        }
    }

    if( ! is_dir( $_SERVER['HOME'] . '/pagecarton/core/' ) )
    {
        require_once( '/usr/local/cpanel/3rdparty/bin/pagecarton/frontend/pc_installer.php' );

        if( ! is_dir( $_SERVER['HOME'] . '/pagecarton/core/' ) )
        {
            echo 'PageCarton Could not be installed automatically. Please proceed to <a href="' . $homeUrl . '/pc_installer.php">' . $homeUrl . '/pc_installer.php</a> to install manually.';
            exit();
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
            mkdir( $dirNow, 0644, true );
            chmod( $dirNow, 0644 );
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

  
    /** 
     * Fetches a remote link. Lifted from Ayoola_Abstract_Viewable
     *
     * @param string Link to fetch
     * @param array Settings  
     */
    function fetchLink( $link, array $settings = null )
    {
        $request = curl_init( $link );
        //curl_setopt( $request, CURLOPT_HEADER, true );
        curl_setopt( $request, CURLOPT_URL, $link );

        //dont check ssl
        curl_setopt($request, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($request, CURLOPT_SSL_VERIFYPEER, 0);   

        curl_setopt( $request, CURLOPT_USERAGENT, @$settings['user_agent'] ? : __FILE__ ); 
        curl_setopt( $request, CURLOPT_AUTOREFERER, true );
        curl_setopt( $request, CURLOPT_REFERER, @$settings['referer'] ? : $link );
        if( @$settings['destination_file'] )
        {
        $fp = fopen( $settings['destination_file'], 'w' );
        curl_setopt( $request, CURLOPT_FILE, $fp );
        curl_setopt( $request, CURLOPT_BINARYTRANSFER, true );
        curl_setopt( $request, CURLOPT_HEADER, 0 ); 
        }
        else  
        {
        curl_setopt( $request, CURLOPT_RETURNTRANSFER, true );
        }
        //curl_setopt( $request, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $request, CURLOPT_FOLLOWLOCATION, @$settings['follow_redirect'] === false ? false : true ); //By default, we follow redirect
        curl_setopt( $request, CURLOPT_CONNECTTIMEOUT, @$settings['connect_time_out'] ? : 10000 );//Max of 1 Secs on a single request
        curl_setopt( $request, CURLOPT_TIMEOUT, @$settings['time_out'] ? : 10000 );//Max of 1 Secs on a single request
        if( @$settings['post_fields'] )
        {
        curl_setopt( $request, CURLOPT_POST, true );
        //var_export( $request );
        //var_export( $settings['post_fields'] );   
        curl_setopt( $request, CURLOPT_POSTFIELDS, $settings['post_fields'] );
        }
        if( @$settings['raw_response_header'] )
        {
        //var_export( $settings );
        $headerBuff = fopen( '/tmp/headers' . time(), 'w+' );
        //var_export( $headerBuff );
        curl_setopt( $request, CURLOPT_WRITEHEADER, $headerBuff );
        }
        if( is_array( @$settings['http_header'] ) )
        {
        curl_setopt( $request, CURLOPT_HTTPHEADER, $settings['http_header'] );
        }
        $response = curl_exec( $request );
        $responseOptions = curl_getinfo( $request );

        // close cURL resource, and free up system resources
        curl_close( $request );
        //var_export( htmlentities( $response ) ); 

        //var_export( $responseOptions );
        //exit( var_export( $responseOptions ) );
        //var_export( $settings['post_fields'] );
        //if( ! $response || $responseOptions['http_code'] != 200 ){ return false; }
        if( empty( $settings['return_error_response'] ) )
        {   
        if( $responseOptions['http_code'] != 200 ){ return false; }
        }
        if( @$settings['return_as_array'] == true )
        {   
        if( @$settings['raw_response_header'] )
        {
        //var_export( $headerBuff );
        rewind($headerBuff);
        $headers = stream_get_contents( $headerBuff );
        @$responseOptions['raw_response_header'] = $headers;
        }
        $response = array( 'response' => $response, 'options' => $responseOptions );
        }
        //var_export( $response );
        return $response;
    } 


?>