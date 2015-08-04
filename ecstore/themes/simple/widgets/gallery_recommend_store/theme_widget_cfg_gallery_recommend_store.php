<?php
function theme_widget_cfg_gallery_recommend_store(&$smarty) {
    $filter['status']='1';
    $filter['disabled']='false';
    $filter['approved']='1';
    $filter['filter_sql']="( {table}last_time is null or {table}last_time >=".mktime(0, 0, 0, date("m")  , date("d"), date("Y")).")";
    return http_build_query($filter);
}
