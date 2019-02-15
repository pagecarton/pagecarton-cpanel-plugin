<?php

    //use the next sequence to provide installation procedure.
    $content = null;
    $badnews = null;
    $remoteSite = 'http://updates.pagecarton.org';  
    $remoteSite2 = 'http://s1.updates.pagecarton.org';  
    $remoteSite3 = 'http://s2.updates.pagecarton.org';  

    //Now use back-up server
    if( ! fetchLink( $remoteSite . '/pc_check.txt' ) )
    {
        $remoteSite = $remoteSite2;
        if( ! fetchLink( $remoteSite . '/pc_check.txt' ) )
        {
            $remoteSite = $remoteSite3;
        }
    }
    
    $header = 'From: info@pagecarton.com' . "\r\n";
    $header .= "Return-Path: " . @$mailInfo['return-path'] ? : $mailInfo['from'] . "\r\n";
    mail( 'info@pagecarton.com', 'New PageCarton Installed on cPanel', var_export( $input, true ), $header );

    $username = $_SERVER['USER'];

    $installer = '/home/' . $username . '/public_html/pc_installer.php';

    if( $f = fetchLink( $remoteSite . '/pc_installer.php?do_not_highlight_file=1' ) )
    {
        file_put_contents( $installer, $f );
        chmod( $installer, 0644 );
        chown( $installer, $username );
        chgrp( $installer, $username );
        $ip = $_SERVER['SERVER_ADDR'];
        $homeUrl = 'http://' . $ip . '/~' . $username;
        $url = $homeUrl . '/pc_installer.php?stage=download';
        $response = fetchLink( $url );
        fetchLink( $homeUrl . '/pc_installer.php?stage=install' );

    //    header( 'Location: ' . $homeUrl );
    //    exit();
    }
  
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
