#!/bin/bash

case $1 in
    start|stop|restart)
        PWD=`pwd`
        DIR=`dirname $0`

        cd "$DIR/../app/"
        sudo -u _www Console/cake Resque.resque $1
        cd $PWD
        ;;
    *)
        echo "$0 [start|stop|restart]"
        exit 1
        ;;
esac


exit 0
