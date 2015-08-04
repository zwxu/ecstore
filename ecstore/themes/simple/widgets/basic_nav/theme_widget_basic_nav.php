<?php

 
function theme_widget_basic_nav($setting, &$smarty){

    define('IN_SHOP',true);

    $result = app::get('site')->model('menus')->select()->where('hidden = ?', 'false')->order('display_order ASC')->instance()->fetch_all();

    $setting['max_leng'] = $setting['max_leng'] ? $setting['max_leng'] : 7;
    $setting['showinfo'] = $setting['showinfo'] ? $setting['showinfo'] : app::get('b2c')->_("更多");

    foreach($result as $k=>$menu){
        if($menu['is_goods_cat'] == 'true'){
            $goods_cats = getCatChildrenById($menu['cat_id']);
            if($goods_cats){
                $result[$k]['goods_cat_tree'] = $goods_cats;
            }
        }
    }

    return $result;
}

function getCatChildrenById($id){
    $goodsCatObj = app::get('b2c')->model('goods_cat');
    $goods_cats = $goodsCatObj->getTree($id);
    if(!empty($goods_cats)){
        foreach($goods_cats as $key=>$cat){
            if($cat['child_count'] > 0){
                $children = getCatChildrenById($cat['id']);
                if(!empty($children)){
                    $goods_cats[$key]['children'] = $children;
                }
            }
        }
    }

    return $goods_cats;
}
?>
