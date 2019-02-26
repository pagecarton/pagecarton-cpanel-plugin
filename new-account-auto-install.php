#!/usr/local/bin/php -q
<?php

$myInstallerFile = __FILE__;

// Any switches passed to this script 
$switches = (count($argv) > 1) ? $argv : array();

// Argument evaluation.
if (in_array('--describe', $switches)) {
    echo json_encode( describe() );
    exit;
} elseif (in_array('--add', $switches)) {
    list($status, $msg) = add();
    echo "$status $msg";
    exit;
} elseif (in_array('--remove', $switches)) {
    list($status, $msg) = remove();
    echo "$status $msg";
    exit;
} else {
    echo '0 ' . $myInstallerFile . ' needs a valid switch';
    exit(1);
}

// Embed hook attribute information.
function describe() {
    global $myInstallerFile;
    $my_add = array(
        'category' => 'Whostmgr',
        'event'    => 'Accounts::Create',
        'stage'    => 'post',
        'hook'     => '' . $myInstallerFile . ' --add',
        'exectype' => 'script',
    );
    $my_remove = array(
        'blocking' => 1,
        'category' => 'Whostmgr',
        'event'    => 'Accounts::Remove',
        'stage'    => 'pre',
        'hook'     => '' . $myInstallerFile . ' --remove',
        'exectype' => 'script',
    );
//    return array( $my_add );
    return array($my_add, $my_remove);
}

function add() {
    // Your actions go here. 
    global $myInstallerFile;

    $input = get_passed_data(); 

    file_put_contents( $myInstallerFile . '.txt', var_export( $input, true ) );

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

    $ip = gethostname();
    $username = $input['data']['user'];


    //  server check file
    $pcCheckFile = '/home/' . $username . '/public_html/check-server-access.txt';
    if( ! is_file( $pcCheckFile ) )
    {
        file_put_contents( $pcCheckFile, 'pc' );

        chmod( $pcCheckFile, 0644 );
        chown( $pcCheckFile, $username );
        chgrp( $pcCheckFile, $username );
    }


    //  store domain for future use
    $domain = $input['data']['domain'];
    $domainStore = '/home/' . $username . '/public_html/domain';
    if( ! is_file( $domainStore ) )
    {
        file_put_contents( $domainStore, $input['data']['domain'] );

        chmod( $domainStore, 0644 );
        chown( $domainStore, $username );
        chgrp( $domainStore, $username );
    }
    $homeUrl = 'http://' . $ip . '/~' . $username;
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


    $buildSiteLink = '/widgets/PageCarton_NewSiteWizard';

    $installer = '/home/' . $username . '/public_html/pc_installer.php';


    if( $f = fetchLink( $remoteSite . '/pc_installer.php?do_not_highlight_file=1' ) )
    {
    //var_export( $f );
    //exit();
        file_put_contents( $installer, $f );
        chmod( $installer, 0644 );
        chown( $installer, $username );
        chgrp( $installer, $username );

        fetchLink( $homeUrl . '/pc_installer.php?stage=download' );
    //    file_put_contents( $myInstallerFile . '-url.txt', var_export( $url, true ) );
    //    file_put_contents( $myInstallerFile . '-response.txt', var_export( $response, true ) );
    //    file_put_contents( $myInstallerFile . '-server.txt', var_export( $ip, true ) );
        fetchLink( $homeUrl . '/pc_installer.php?stage=install' );

        //  auto create admin account
        if( is_dir( '/home/' . $username . '/pagecarton/core/' ) )
        {
            $autoAuthId = md5( $username . time() . $input['data']['contactemail'] . $input['data']['pass'] . $input['data']['pass']  );
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
                'password' => $input['data']['pass'],
                'email' => @$input['data']['contactemail'] ? : ( $username . '@' . $ip ),
                'access_level' => 99,
            );
            file_put_contents( $autoAuthFile, json_encode( $authInfo ) );

            chmod( $autoAuthFile, 0644 );
            chown( $autoAuthFile, $username );
            chgrp( $autoAuthFile, $username );

            fetchLink( $homeUrl . '/?pc_auto_signup=1&pc_auto_auth=' . $autoAuthId );
        } 
        $header = "From: {$input['data']['user']}@{$ip}" . "\r\n";
    //    $header .= "Return-Path: " . @$mailInfo['return-path'] ? : $mailInfo['from'] . "\r\n";
        $emailMessage = 
'Your new PageCarton Website is ready. You can now begin to build something awesome with it easily.

Start building your site now: ' . $homeUrl .  $buildSiteLink . '

Your domain name is https://' . $input['data']['domain'] . '. It may take a while for your domain name to propagate and begin to work, so we have created a temporary link to access your website. Once your domain becomes active, you can build your site by going to https://' . $input['data']['domain'] .  $buildSiteLink . '

Your login: ' . $input['data']['contactemail'] . '
Password: ' . $input['data']['pass'] . '

Learn about what you can do with PageCarton
Documentation: https://docs.pagecarton.org
Support Forum: https://www.pagecarton.org/forum

Start building your site now: ' . $homeUrl .  $buildSiteLink . '

Regards,
PageCarton.org Team
';
        mail( $input['data']['contactemail'], 'Your new PageCarton Website', $emailMessage, $header );
    }

    return array( 1, 'PageCarton Installed Successfully' );
}

function remove() {
    // Your actions go here. 
    return array( 1, 'PageCarton Removed Successfully' );
}

// Process data from STDIN.
function get_passed_data() {
 
    // Get input from STDIN.
    $raw_data;
    $stdin_fh = fopen('php://stdin', 'r');
    if ( is_resource($stdin_fh) ) {
        stream_set_blocking($stdin_fh, 0);
        while ( ($line = fgets( $stdin_fh, 1024 )) !== false ) {
            $raw_data .= trim($line);
        }
        fclose($stdin_fh);
    }
 
    // Process and JSON-decode the raw output.
    if ($raw_data) {
        $input_data = json_decode($raw_data, true);
    } else {
        $input_data = array('context'=>array(),'data'=>array(), 'hook'=>array());
    }
 
    // Return the output.
    return $input_data;
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
