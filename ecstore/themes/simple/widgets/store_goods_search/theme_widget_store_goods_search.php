<?php
function theme_widget_store_goods_search(&$setting,&$render){
    $data['title'] = $setting['title'];
    $data['store'] = $render->pagedata['store'];
    return $data;
}
?>
