<?php
function theme_widget_mr_floor(&$setting,&$smarty) {
    //大商品
    if(!empty($setting['floor_suspended_goods'])){
        $goods = $setting['floor_suspended_goods'];
        $ids = explode(',',$goods['linkid']);
        $filter = array('goods_id'=>$ids,'goodsNum'=>1);
        $default_cols = 'goods_id,name,price,mktprice,cost,bn,marketable,marketable_allow,type_id,cat_id,brand_id,p_order,d_order,score,score_setting,image_default_id,udfimg,rank_count,comments_count,view_count,view_w_count,view_m_count,buy_count,buy_w_count,buy_m_count,store_id,fav_count,brief';
        $goods_info = b2c_widgets::load('Goods')->getGoodsList($filter,$default_cols);
        $goods_list = json_decode($goods['goods'],true);

        foreach($goods_list as $k=>$v){
            $goods_list[$k]['goods_info'] = $goods_info['goodsRows'][$v['id']];
        }

        $setting['floor_suspended_goods']['goods_info'] = $goods_list;
    }

    //楼层商品
    if(!empty($setting['floor_goods'])){
        $goods = $setting['floor_goods'];
        $ids = explode(',',$goods['linkid']);
        $filter = array('goods_id'=>$ids,'goodsNum'=>8);
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
