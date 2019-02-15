
THEMENAME="paper_lantern"
if [ ! "$1" ]
then
THEMENAME="$1"
fi

FRONTENDBASE="/usr/local/cpanel/base/frontend/$THEMENAME/pagecarton/"

#   remove previous

rm -rf pagecarton-cpanel-plugin
rm -rf "$FRONTENDBASE"


#   download plugin
git clone https://github.com/pagecarton/pagecarton-cpanel-plugin.git

#   copy frontend
cp -r pagecarton-cpanel-plugin/frontend/* "$FRONTENDBASE"

/usr/local/cpanel/scripts/install_plugin pagecarton-cpanel-plugin/configuration --theme "$THEMENAME" 

