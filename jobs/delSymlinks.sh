#!/bin/bash

# removes all files symlink scary N minutes
# cron */2 * * * * root

MA="+3"
BASEDIR=`dirname $0`
PTSL=$BASEDIR/../www/img/

find $PTSL -type f ! -name ".gitignore" -cmin $MA -delete > /dev/null 2>&1