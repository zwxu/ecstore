<?php
function theme_widget_cfg_duce_goodscat( $system ){
    $mdl_goodsCat = app::get('b2c')->model('goods_cat');
    $mdl_goodsVirtualCat = app::get('b2c')->model('goods_virtual_cat');
    $data['cat'] = $mdl_goodsCat->get_subcat_list(0);
    $vcat = $mdl_goodsVirtualCat->getMapTree();
    foreach ($vcat as $key => $val) {
        $len = array_unique(explode(',',$val['cat_path']));
        $vcat[$key]['len'] = count($len);
    }
    $data['virtualcat'] = $vcat;
    
    return $data;
}

?>
