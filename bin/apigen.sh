#!/usr/bin/env bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
BASE_DIR="$(dirname "$DIR")"
APIGEN="$BASE_DIR/lib/vendor/apigen/apigen/bin/apigen"

$APIGEN generate --source $BASE_DIR/src --source $BASE_DIR/core.php --source $BASE_DIR/helper.php --source $BASE_DIR/wp.php --destination $BASE_DIR/docs/api