<?php
function theme_widget_store_pic(&$setting,&$smarty) {    
    $setting['area']=explode('|',$setting['link_area']);
    return $setting;
}