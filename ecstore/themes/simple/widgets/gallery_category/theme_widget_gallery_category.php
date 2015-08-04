<?php
function theme_widget_gallery_category(&$setting, &$system) {
    $goodsCatObj = app::get('b2c')->model('goods_cat');
    $goods_cats = $goodsCatObj->get_cat_list();
    $gCats = array();
    $cat_one = array();
    $i = 0;
    foreach($goods_cats as $k=>$cat){
        if($cat['pid']=='0'){
            $i++;
            $cat_one[] = $cat['cat_id'];
            $gCats[$cat['cat_id']] = $cat;
            $gCats[$cat['cat_id']]['i'] = $i;
            unset($goods_cats[$k]);
        }
    }
    foreach($goods_cats as $k=>$cat){
        if(in_array($cat['pid'],$cat_one)){
            $gCats[$cat['pid']]['children'][] = $cat;
        }
        
    }

    return $gCats;

}
?>
