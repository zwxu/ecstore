<?php
function theme_widget_index_onegoods_roll(&$setting,&$smarty) {
    //商品
    if(!empty($setting['goods'])){
        $goods = $setting['goods'];
        $ids = explode(',',$goods['linkid']);
        $filter = array('goods_id'=>$ids);
        $goods_info = b2c_widgets::load('Goods')->getGoodsList($filter);
        $goods_list = json_decode($goods['goods'],true);

        foreach($goods_list as $k=>$v){
            $goods_list[$k]['goods_info'] = $goods_info['goodsRows'][$v['id']];
        }

        $setting['goods']['goods_info'] = $goods_list;
    }

//echo '<pre>';print_r($setting);die;//qianleidebug
    return $setting;
}

?>
