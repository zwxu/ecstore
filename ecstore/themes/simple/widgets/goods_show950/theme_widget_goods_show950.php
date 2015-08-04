<?php
function theme_widget_goods_show950(&$setting,&$render){
    $goods_list = json_decode($setting['goods'],1);
    $goodsId = array();$goodsInfo = array();
    if (is_array($goods_list))
    foreach ($goods_list as $goods){
        $goodsId[] = $goods['id'];
        $goodsInfo[$goods['id']] = $goods;
    }
    $filter['goodsId'] = $goodsId;
    $data['info'] = $goodsInfo;
    $filter['goodsNum']= $setting['goodsNum'];
    $data['goodsdata'] = b2c_widgets::load('Goods')->getGoodsList($filter);
    return $data; 
}
?>
