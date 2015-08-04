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
checkprocess $1 $2"/app/business/crontab/auto_get_tradeAvgPoint.php"
