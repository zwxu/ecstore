<?php

function theme_widget_goods_tabnew(&$setting, &$render) {
    $data['tag'] = array();
    $mdl_goods = app::get('b2c')->model('goods');
    $store_id = $render->pagedata['store_id'];
    $imageDefault = app::get('image')->getConf('image.set');
    $data['defaultImage'] = $imageDefault['S']['default_image'];
    $show_nums = intval($setting["tab_goods_name"] ? $setting["tab_goods_name"] : '7');
    //var_dump($store_id);
    //die();
    $filter_tui = array('store_id' => $store_id, 'disabled' => 'false', 'marketable' => 'true', 'is_tui' => 'true');
    $filter_new = array('store_id' => $store_id, 'disabled' => 'false', 'marketable' => 'true', 'is_new' => 'true');
    $goods_tab1 = $mdl_goods->getList('is_tui,goods_id,name,image_default_id,price,udfimg ,thumbnail_pic,buy_count', $filter_tui, 0, $show_nums);
    
    $goods_tab2 = $mdl_goods->getList('is_new,goods_id,name,image_default_id,price,udfimg ,thumbnail_pic,buy_count', $filter_new, 0, $show_nums);
//$goods = utils::array_change_key($goods, 'goods_id');
    $data['goods_tab1'] = $goods_tab1;
    $data['goods_tab2'] = $goods_tab2;

    return $data;
}

?>
