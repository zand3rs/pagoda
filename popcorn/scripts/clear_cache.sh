#!/bin/bash

PWD=`pwd`
DIR=`dirname $0`

cd $DIR
find ../app/tmp -not -name '.gitignore' -exec sudo rm -f {} \;
cd $PWD

exit 0
