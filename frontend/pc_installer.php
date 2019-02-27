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
    
    $installer = '/home/' . $username . '/public_html/pc_installer.php';
    if( $f = fetchLink( $remoteSite . '/pc_installer.php?do_not_highlight_file=1' ) )
    {
        file_put_contents( $installer, $f );
        chmod( $installer, 0700 );
        chown( $installer, $username );
        chgrp( $installer, $username );
        fetchLink( $homeUrl . '/pc_installer.php?stage=download' );
        fetchLink( $homeUrl . '/pc_installer.php?stage=install' );

        //  rename default index 
        $index = dirname( $installer ) . '/index.html'; 
        rename( $index, $index . '.backup' );
    }

?>
