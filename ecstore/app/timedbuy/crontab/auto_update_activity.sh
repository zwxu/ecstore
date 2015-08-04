#!/bin/bash
function checkprocess(){
    if (ps aux|grep -v grep|grep "$2" )
    then
        echo "active"
    else
        echo "miss"
        $1 $2 &
    fi
}
checkprocess $1 $2"/app/timedbuy/crontab/auto_update_activity.php"