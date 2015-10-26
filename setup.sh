#!/bin/sh

PWD="$PWD";

rm -rf $PWD/dev/wordpress/wp-content/uploads
rm -rf $PWD/dev/wordpress/wp-content/themes
rm -rf $PWD/dev/wordpress/wp-content/plugins

ln -sf $PWD/ext/wordpress $PWD/dev
ln -sf $PWD/dev/wp-config.php $PWD/ext/wordpress

ln -sf $PWD/dev/uploads $PWD/dev/wordpress/wp-content
ln -sf $PWD/dev/themes $PWD/dev/wordpress/wp-content
ln -sf $PWD/dev/plugins $PWD/dev/wordpress/wp-content