<?php

 
function theme_widget_cfg_store_goods_recommend($app,$store){
    $o = &app::get('b2c')->model('goods');
    $data['orderby'] =  $o->orderBy();
    $data['store'] =  $store;
    return $data;
}
?>
