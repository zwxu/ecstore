<?php
function theme_widget_cfg_gallery_goodscat( $system, $cat_id ){
    $mdl_goodsCat = app::get('b2c')->model('goods_cat');
    $data['cat'] = $mdl_goodsCat->getList('*',array('parent_id'=>$cat_id),0,-1,'p_order ASC');

    return $data;

}
?>