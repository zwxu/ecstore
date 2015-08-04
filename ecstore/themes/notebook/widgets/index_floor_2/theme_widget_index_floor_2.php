<?php
function theme_widget_index_floor_2(&$setting,&$smarty) {
    //悬浮商品
    if(!empty($setting['floor_suspended_goods'])){
        $goods = $setting['floor_suspended_goods'];
        $ids = explode(',',$goods['linkid']);
        $filter = array('goods_id'=>$ids);
        $goods_info = b2c_widgets::load('Goods')->getGoodsList($filter);
        $goods_list = json_decode($goods['goods'],true);

        foreach($goods_list as $k=>$v){
            $goods_list[$k]['goods_info'] = $goods_info['goodsRows'][$v['id']];
        }

        $setting['floor_suspended_goods']['goods_info'] = $goods_list;
    }

    //悬浮商品
    if(!empty($setting['floor_goods'])){
        $goods = $setting['floor_goods'];
        $ids = explode(',',$goods['linkid']);
        $filter = array('goods_id'=>$ids,'goodsNum'=>9);
        $goods_info1 = b2c_widgets::load('Goods')->getGoodsList($filter);
        $goods_list1 = json_decode($goods['goods'],true);
        foreach($goods_list1 as $k=>$v){
            $goods_list1[$k]['goods_info'] = $goods_info1['goodsRows'][$v['id']];
        }

        $setting['floor_goods']['goods_info'] = $goods_list1;
    }
    return $setting;
}

?>
