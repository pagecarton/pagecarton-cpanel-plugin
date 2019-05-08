# PageCarton cPanel Plugin

CONTENTS OF THIS FILE
---------------------

 * About
 * Features
 * Installation
 * Installation

ABOUT
------------
PageCarton cPanel Plugin helps hosting companies to provide a custom site builder for users of their cpanel web hosting service.

FEATURES
---------------------
* Installs a global PageCarton to cPanel to enable custom features
* Auto-install PageCarton to new cpanel accounts
* Puts necessary icons in the cPanel interfaces so users can easily manage their website and publish new sites through the cPanel.

INSTALLATION
---------------------
Run install.sh on servers that run cPanel & WHM version 11.44 and later. install.sh must be run as root to work. Script copies installation files to /usr/local/cpanel/3rdparty/bin/pagecarton/plugin/

UNINSTALL
---------------------
Run uninstall.sh on the server to uninstall PageCarton cPanel Plugin. Script is usually located on /usr/local/cpanel/3rdparty/bin/pagecarton/plugin/uninstall.sh. Run the following commands on the terminal to uninstall the plugin.

`UNINSTALLER="usr/local/cpanel/3rdparty/bin/pagecarton/plugin/uninstall.sh"`
`chmod 755 $UNINSTALLER`
`$UNINSTALLER`

Don't forget to help us by leaving a feedback before you remove the plugin, it is possible for us to provide custom upgrades and bug fixes so the plugin can work to your specification.