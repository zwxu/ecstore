<?php
function theme_widget_store_goods_rank(&$setting,&$render){
    //
    $limit = intval($setting['limit']);
    $limit = $limit ? $limit:10;
    //
    $store_id = $render->pagedata['store_id'];
    //
    $buys = app::get('b2c')->model('goods')->getList('*',array('store_id'=>$store_id),0,$limit,'buy_m_count DESC');
    $data['buys'] = $buys;
    //
    $favs = app::get('b2c')->model('goods')->getList('*',array('store_id'=>$store_id),0,$limit,'fav_count DESC');
    $data['favs'] = $favs;
    //
    $data['title'] = $setting['title'];

    $imageDefault = app::get('image')->getConf('image.set');
    $data['defaultImage'] = $imageDefault['S']['default_image'];
    $data['store'] = $render->pagedata['store'];
    return $data;
}
?>
