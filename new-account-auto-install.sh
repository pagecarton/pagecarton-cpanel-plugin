#!/bin/bash
APP_DIR=/usr/local/cpanel/3rdparty/bin/pagecarton/
mkdir -p $APP_DIR
INSTALL_FILENAME=${APP_DIR}new-account-auto-install.php

#	MAKE IT EXECUTABLE
# chown -hR root:root APP_DIR
chmod 755 $INSTALL_FILENAME

#	RUN THE INSTALLER
#   $INSTALL_FILENAME

/usr/local/cpanel/bin/manage_hooks delete script $INSTALL_FILENAME
#/usr/local/cpanel/bin/manage_hooks add script $INSTALL_FILENAME
