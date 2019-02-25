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
    
/*     $header = 'From: info@pagecarton.com' . "\r\n";
    $header .= "Return-Path: " . @$mailInfo['return-path'] ? : $mailInfo['from'] . "\r\n";
    mail( 'info@pagecarton.com', 'New PageCarton Installed on cPanel', var_export( $input, true ), $header );
 */
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

?>
