
THEMENAME="paper_lantern"
if [ "$1" ]
then
THEMENAME="$1"
fi

FRONTEND_BASE="/usr/local/cpanel/base/frontend/$THEMENAME/pagecarton"
PAGECARTON_BASE="/usr/local/cpanel/3rdparty/bin/pagecarton/plugin"

#   remove previous
rm -rf pagecarton-cpanel-plugin
rm -rf "$FRONTEND_BASE"
rm -rf "$PAGECARTON_BASE"

mkdir -p "$FRONTEND_BASE"
mkdir -p "$PAGECARTON_BASE"

#   download plugin
git clone https://github.com/pagecarton/pagecarton-cpanel-plugin.git

#   copy frontend
cp -r pagecarton-cpanel-plugin/frontend/* "$PAGECARTON_BASE"
ln -s "$PAGECARTON_BASE" "$FRONTEND_BASE"

#   uninstall
/usr/local/cpanel/scripts/uninstall_plugin pagecarton-cpanel-plugin/configuration --theme="$THEMENAME"

/usr/local/cpanel/scripts/install_plugin pagecarton-cpanel-plugin/configuration --theme "$THEMENAME" 

