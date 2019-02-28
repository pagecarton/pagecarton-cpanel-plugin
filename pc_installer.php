<?php
    require_once( '/usr/local/cpanel/3rdparty/bin/pagecarton/functions.php' );

    $myInstallerFile = '/usr/local/cpanel/3rdparty/bin/pagecarton/new-account-auto-install.php';

    if( empty( $username ) )
    {
        // Any switches passed to this script 
        if( empty( $switches ) )
        {
            $switches = (count($argv) > 1) ? $argv : array();
        }

        // Argument evaluation.
        if (in_array('--describe', $switches)) 
        {
            echo json_encode( describe() );
            exit;
        } 
        elseif (in_array('--add', $switches)) 
        {
            list($status, $msg) = install();
            echo "$status $msg";
            exit;
        } 
        elseif (in_array('--remove', $switches)) 
        {
            list($status, $msg) = remove();
            echo "$status $msg";
            exit;
        } 
        else 
        {
            echo '0 ' . $myInstallerFile . ' needs a valid switch';
            exit(1);
        }
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

    function install() 
    {
        // Your actions go here. 
        global $myInstallerFile;

        $input = get_passed_data(); 

    //    @file_put_contents( $myInstallerFile . '.txt', var_export( $input, true ) );

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

        if( empty( $input['data']['user'] ) )
        {
            exit( 'USERNAME TO INSTALL NOT SET' );
        }
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
        $homeUrl = null;
        if( 'pc' === fetchLink( 'http://' . $domain . '/' . basename( $pcCheckFile ) ) )
        {
            $homeUrl = 'http://' . $domain;
        }
        else
        {
            if( 'pc' !== fetchLink( 'http://' . $ip . '/~' . $username . '/' . basename( $pcCheckFile ) ) )
            {
                $homeUrl = 'http://' . $domain;

            //    file_put_contents( $myInstallerFile . '-home0-cdomain=error.txt', var_export(  $domain . '/' . basename( $pcCheckFile ), true ) );
            //    file_put_contents( $myInstallerFile . '-home0-domain=error.txt', var_export( $domain, true ) );
            //    file_put_contents( $myInstallerFile . '-home=error.txt', var_export( 'http://' . $ip . '/~' . $username, true ) );
                echo 'ERROR! Domain name "' . $domain . '" is not yet accessible on this server. Please contact the technical department.';
            //  exit();
            }
            else
            {
                $homeUrl = 'http://' . $ip . '/~' . $username;
            }
        }


        $buildSiteLink = '/new-site-wizard';

        $installer = '/home/' . $username . '/public_html/pc_installer.php';

        $remoteInstallerUrl = $remoteSite . '/pc_installer.php?do_not_highlight_file=1';
        $f = fetchLink( $remoteInstallerUrl );
    //    @file_put_contents( $myInstallerFile . '-install-file.txt', var_export( $f, true ) );
    //    @file_put_contents( $myInstallerFile . '-url.txt', var_export( $remoteInstallerUrl, true ) );

        if( $f )
        {
            file_put_contents( $installer, $f ); 
            chmod( $installer, 0644 );
            chown( $installer, $username );
            chgrp( $installer, $username );

            $response = fetchLink( $homeUrl . '/pc_installer.php?stage=download' );
        //    @file_put_contents( $myInstallerFile . '-install-response.txt', var_export( $response, true ) );
        //    @file_put_contents( $myInstallerFile . '-server.txt', var_export( $ip, true ) );
            fetchLink( $homeUrl . '/pc_installer.php?stage=install' );

            //  rename default index 
            $index = dirname( $installer ) . '/index.html'; 
            if( is_file( $index ) )
            {
                rename( $index, $index . '.backup' );
            }

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
                        mkdir( $dirNow, 0700, true );
                        chmod( $dirNow, 0700 );
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
                $saveUserInfoFile = '/home/' . $username . '/userinfo';

                //  delete user data after installation for security reasons
               @unlink( $saveUserInfoFile );

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
    function get_passed_data() 
    {  
        global $username;

        // Get input from STDIN.
        $raw_data = null;
        $stdin_fh = fopen( 'php://stdin', 'r' );
        if ( is_resource($stdin_fh) ) 
        {
            stream_set_blocking( $stdin_fh, 0 );
            while ( ($line = fgets( $stdin_fh, 1024 )) !== false ) 
            {
                $raw_data .= trim($line);
            }
            fclose($stdin_fh);
        }
    
        // Process and JSON-decode the raw output.
        if( $raw_data ) 
        {
            $input_data = json_decode( $raw_data, true );

            $username = $input_data['data']['user'];

            //  server check file
            $saveUserInfoFile = '/home/' . $username . '/userinfo';

            file_put_contents( $saveUserInfoFile, $raw_data );

            chmod( $autoAuthFile, 0644 );
            chown( $autoAuthFile, $username );
            chgrp( $autoAuthFile, $username );
        } 
        elseif( ! empty( $username ) AND $raw_data = file_get_contents( '/home/' . $username . '/userinfo' ) ) 
        {
            $input_data = json_decode( $raw_data, true );
        } 
        elseif( @$input_data = include( '/root/xxx' ) )  
        {
            
        } 
        else 
        {
            $input_data = array('context'=>array(),'data'=>array(), 'hook'=>array());
        }
    
        // Return the output.
        return $input_data;
    }


?>
