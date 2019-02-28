#!/bin/bash
# Get latest installation from github
rm -rf pagecarton-cpanel-plugin
# install git if not already installed by using the following command
# yum install git
git clone https://github.com/pagecarton/pagecarton-cpanel-plugin.git
# install
INSTALL_FILENAME="pagecarton-cpanel-plugin/install.sh"
chmod 755 $INSTALL_FILENAME
$INSTALL_FILENAME 