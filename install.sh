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

mkdir -p "$FRONTEND_BASE"
mkdir -p "$PLUGIN_BASE"

#   download plugin
git clone https://github.com/pagecarton/pagecarton-cpanel-plugin.git


#   DUMP everything
cp -r pagecarton-cpanel-plugin/* "$PAGECARTON_BASE"

#   copy frontend
#cp -r pagecarton-cpanel-plugin/frontend/* "$PLUGIN_BASE"
ln -s "$PLUGIN_BASE" "$FRONTEND_BASE"

#   do hook for new accounts auto-install
INSTALL_FILENAME="pagecarton-cpanel-plugin/new-account-auto-install.sh"
chmod 755 $INSTALL_FILENAME
$INSTALL_FILENAME

#   uninstall
/usr/local/cpanel/scripts/uninstall_plugin /usr/local/cpanel/3rdparty/bin/pagecarton/plugin/configuration --theme="$THEMENAME"

#   install
/usr/local/cpanel/scripts/install_plugin /usr/local/cpanel/3rdparty/bin/pagecarton/plugin/configuration --theme "$THEMENAME" 

#   make required files executable
CRON_SCRIPT_PATH="/usr/local/cpanel/3rdparty/bin/pagecarton/plugin/cron.sh"
UPDATE_SCRIPT_PATH="/usr/local/cpanel/3rdparty/bin/pagecarton/plugin/update.sh"
INSTALLER_SCRIPT_PATH="/usr/local/cpanel/3rdparty/bin/pagecarton/plugin/install.sh"
UNINSTALLER_SCRIPT_PATH="/usr/local/cpanel/3rdparty/bin/pagecarton/plugin/uninstall.sh"

chmod 755 $CRON_SCRIPT_PATH
chmod 755 $UPDATE_SCRIPT_PATH
chmod 755 $INSTALLER_SCRIPT_PATH
chmod 755 $UNINSTALLER_SCRIPT_PATH

#   do cron
#   some servers remove hook so we install 
CRON_JOB="*/30 * * * * $CRON_SCRIPT_PATH > /dev/null 2>&1"
cat <(fgrep -i -v "$CRON_SCRIPT_PATH" <(crontab -l)) <(echo "$CRON_JOB") | crontab -
