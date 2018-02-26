#!/usr/bin/env bash
#================================================================
# HEADER
#================================================================
#% SYNOPSIS
#+      ${SCRIPT_NAME} args ...
#%
#% DESCRIPTION
#%      Wordpress dev environment setup installer for extending framework and/or testing.
#%      After setup setcooki wp framework is bootstrapped in test wordpress theme and plugin.
#%      Script will:
#%      - run composer install
#%      - run wp-installer from https://github.com/setcooki/wp-installer
#%      - copy theme and plugin skeleton from /dev into wordpress install with "test" plugin/theme
#%        name (only if no exists, use -o flag to force overwrite)
#%      NOTE: tested only on OSX!
#%
#% OPTIONS
#%      --php=[path]                Specify custom PHP executable path if not "php" env variable will be used. NOTE: that
#%                                  under certain conditions (e.g. PHP 7.1 under OSX) its possible that wp-cli will fail
#%                                  running certain commands such as wp core install|update. In this case you must run this
#%                                  script with PHP < 7.1 by passing the path to executable with this argument
#%      -o, --overwrite             Will copy and replace theme and plugin skeleton
#%                                  regardless of if a theme and plugin with name
#%                                  test already exists
#%      -h, --help                  Print this help
#%      -v, --version               Print script information
#%
#% EXAMPLES
#%      ${SCRIPT_NAME} -o
#%
#================================================================
#- IMPLEMENTATION
#-      version         ${SCRIPT_NAME} 0.0.1
#-      author          Frank Mueller (setcooki)
#-      copyright       Copyright (c) http://set.cooki.me
#-      license         GNU General Public License
#-
#================================================================
#  HISTORY
#       2017/06/15 : setcooki : Script creation
#       2017/06/18 : setcooki : update wp theme/plugin activation logic
#       2018/01/29 : setcooki : major overhaul regarding running wp-installer + adding --php argument
#
#================================================================
# END_OF_HEADER
#================================================================


SCRIPT_HEADSIZE=$(head -200 ${0} |grep -n "^# END_OF_HEADER" | cut -f1 -d:)
SCRIPT_NAME="$(basename ${0})"

USAGE() { printf "Usage: "; head -${SCRIPT_HEADSIZE:-99} ${0} | grep -e "^#+" | sed -e "s/^#+[ ]*//g" -e "s/\${SCRIPT_NAME}/${SCRIPT_NAME}/g" ; }
HELP() { head -${SCRIPT_HEADSIZE:-99} ${0} | grep -e "^#[%+-]" | sed -e "s/^#[%+-]//g" -e "s/\${SCRIPT_NAME}/${SCRIPT_NAME}/g" ; }
INFO() { head -${SCRIPT_HEADSIZE:-99} ${0} | grep -e "^#-" | sed -e "s/^#-//g" -e "s/\${SCRIPT_NAME}/${SCRIPT_NAME}/g"; }

if [[ ( $@ == "--help") ||  $@ == "-h" ]]
then
    HELP
	exit 0
fi;

if [[ ( $@ == "--version") ||  $@ == "-v" ]]
then
    INFO
	exit 0
fi;

if [[ ( $@ == "--overwrite") ||  $@ == "-o" ]]
then
    OVERWRITE=1
else
    OVERWRITE=0
fi;

PHP="PHP"
WP_CLI="./wp-cli.phar"
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
BASE_DIR="$(dirname "$DIR")"
WORDPRESS="$BASE_DIR/wordpress"
THEMES="$BASE_DIR/dev/skeletons/themes"
PLUGINS="$BASE_DIR/dev/skeletons/plugins"
PHPUNIT="/usr/local/bin/phpunit"

for i in "$@"
do
case $i in
    --php=*)
    PHP="${i#*=}"
    shift # past argument=value
    ;;
    *)
    # unknown option
    ;;
esac
done

if ! [ -x "$(command -v composer)" ];
then
    echo -e "> Please install PHP composer (global) before setup. Aborting now!"
    exit 1
fi;

if ! [ -x "$(command -v sed)" ];
then
    echo -e "> Need \"sed\" command to run. Aborting now!"
    exit 1
fi;

if [[ $(uname -s) == 'Darwin' ]]; then
  IO="-i"
else
  IO="-i ''"
fi;

#install composer now
echo -e "> running composer install now ..."
composer install --dev

#install wordpress + data
mkdir -p $WORDPRESS
cd $WORDPRESS
wget -Nnv https://raw.githubusercontent.com/setcooki/wp-installer/master/wp-installer
if [ "$PHP" != "PHP" ]; then
    bash ./wp-installer --wp-path=. --php=$PHP
else
    bash ./wp-installer --wp-path=.
fi;
cd ..

#install theme skeleton
if [[ ! -d "$WORDPRESS/wp-content/themes/theme1" || OVERWRITE -eq 1 ]];
then
    echo -e "> copying theme1 test skeleton now ..."
    if [ -d "$WORDPRESS/wp-content/themes/.theme1" ];
        THEME='.theme1'
    then
        THEME='theme1'
    fi;
    mkdir -p $WORDPRESS/wp-content/themes/$THEME
    cp -Rfa $THEMES/theme1/* $WORDPRESS/wp-content/themes/$THEME
    sed $IO -e 's%\(require_once\).*core.php.*$%\1 "'"$PWD"'/core.php";%g' $WORDPRESS/wp-content/themes/$THEME/functions.php
    rm -rf $WORDPRESS/wp-content/themes/$THEME/functions.php-e
    if [ -x "$(command -v wp)" ];
    then
        cd $WORDPRESS
        echo -e "> activating theme1 test skeleton"
        $PHP $WP_CLI theme activate theme1 --path=$WORDPRESS
        cd ..
    fi;
fi;

#install plugin skeleton
if [[ ! -d "$WORDPRESS/wp-content/plugins/plugin1" || OVERWRITE -eq 1 ]];
then
    echo -e "> copying plugin1 test skeleton now ..."
    if [ -d "$WORDPRESS/wp-content/plugins/.plugin1" ];
        PLUGIN='.plugin1'
    then
        PLUGIN='plugin1'
    fi;
    mkdir -p $WORDPRESS/wp-content/plugins/$PLUGIN
    cp -Rfa $PLUGINS/plugin1/* $WORDPRESS/wp-content/plugins/$PLUGIN
    sed $IO -e 's%\(require_once\).*core.php.*$%\1 "'"$PWD"'/core.php";%g' $WORDPRESS/wp-content/plugins/$PLUGIN/plugin1.php
    rm -rf $WORDPRESS/wp-content/plugins/$PLUGIN/plugin1.php-e
    if [ -x "$(command -v wp)" ];
    then
        cd $WORDPRESS
        echo -e "> activating plugin1 test skeleton"
        $PHP $WP_CLI plugin activate plugin1 --path=$WORDPRESS
        cd ..
    fi;
fi;

#change permalink structure to work with dev install
cd $WORDPRESS
$PHP $WP_CLI rewrite structure '/%postname%/' --path=$WORDPRESS
cd ..
echo -e "> permalink rewrite structure changed to '/%postname%/'"

ln -sf $PHPUNIT $PWD/tests/phpunit.phar

echo -e "> Setup done, good bye!"
exit 0;