<?php
function theme_widget_store_nav(&$setting,&$smarty) {
    $setting['store']=$smarty->pagedata['store'];
    return $setting;
}