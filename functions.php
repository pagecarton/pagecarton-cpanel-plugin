<?php



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
