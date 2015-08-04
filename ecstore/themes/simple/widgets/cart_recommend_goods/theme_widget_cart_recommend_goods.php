<?php
function theme_widget_cart_recommend_goods(&$setting,&$smarty) {
    $cat_ids = array();
    if($smarty->pagedata['fastbuy']){
        $aCart = $smarty->pagedata['aCart'];
    }else{
        $cart_obj = app::get('b2c')->model('cart');
        $aCart = $cart_obj->get_objects();
    }
    //catIds
    if(isset($aCart['object']['goods'])){
        foreach($aCart['object']['goods'] as $k=>$v){
            $cat_ids[] = $v['obj_items']['products'][0]['cat_id'];
        }
        $cat_ids = array_unique($cat_ids);
    
    }

    $goods_obj = app::get('b2c')->model('goods');
    $filter = array();
    if(!empty($cat_ids)){
        $filter = array('str_where'=>'cat_id in ('.implode(',',$cat_ids).')');
    }
    $recomment_goods = $goods_obj->getList('*',$filter,0,5,'buy_count');//推荐商品
    $imageDefault = app::get('image')->getConf('image.set');
    $img = base_storager::image_path( $imageDefault['S']['default_image'],'s' );
    $data['imageDefault'] = $img;
    $data['recommend'] = $recomment_goods;

    //猜你喜欢
    $goods_cat = app::get('b2c')->model('goods_cat');
    $cat_pid = $goods_cat->getList('parent_id',array('cat_id|in'=>$cat_ids));
    $pid = array();
    foreach($cat_pid as $k=>$v){
        $pid[$v['parent_id']] = $v['parent_id'];
    }
    $cat_ids = $goods_cat->getList('cat_id',array('parent_id|in'=>$pid));
    $c_ids = array();
    foreach($cat_ids as $k=>$v){
        $c_ids[$v['cat_id']] = $v['cat_id'];
    }

    $filter = array();
    if(!empty($c_ids)){
        $filter = array('str_where'=>'cat_id in ('.implode(',',$c_ids).')');
    }
    $youlike_goods = $goods_obj->getList('*',$filter,0,5,'buy_count');//猜你喜欢
    $data['youlike_goods'] = $youlike_goods;

    return $data;
}