<?php
function theme_widget_store_friend_link(&$setting,&$render){
    $store_id = $render->pagedata['store_id'];
    $partners = app::get('business')->model('partner')->getList('*',array('store_id'=>$store_id));
    $data['partners'] = $partners;
    $data['title'] = $setting['title'];
    return $data;
}
?>
