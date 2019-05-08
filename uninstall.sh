#!/bin/bash
THEMENAME="paper_lantern"
if [ "$1" ]
then
THEMENAME="$1"
fi

FRONTEND_BASE="/usr/local/cpanel/base/frontend/$THEMENAME/pagecarton"
PAGECARTON_BASE="/usr/local/cpanel/3rdparty/bin/pagecarton/plugin"
PLUGIN_BASE="$PAGECARTON_BASE/frontend"

#   remove previous
rm -rf pagecarton-cpanel-plugin
rm -rf "$FRONTEND_BASE"
rm -rf "$PAGECARTON_BASE"

#   hook for new accounts auto-install
# older files plugin
/usr/local/cpanel/bin/manage_hooks delete script /usr/local/cpanel/3rdparty/bin/pagecarton/new-account-auto-install.php

# remove auto-install
/usr/local/cpanel/bin/manage_hooks delete script /usr/local/cpanel/3rdparty/bin/pagecarton/plugin/new-account-auto-install.php

#   uninstall real interphace plugin
/usr/local/cpanel/scripts/uninstall_plugin /usr/local/cpanel/3rdparty/bin/pagecarton/plugin/configuration --theme="$THEMENAME"