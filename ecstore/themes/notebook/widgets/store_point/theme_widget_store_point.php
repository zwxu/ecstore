<?php
function theme_widget_store_point(&$setting,&$render){
    $data['store_id'] = $render->pagedata['store_id'];
    $data['login'] = $render->pagedata['login'];
    $data['showname'] = $setting['showname'];
    $data['title'] = $setting['title'];
    return $data;
}
?>
