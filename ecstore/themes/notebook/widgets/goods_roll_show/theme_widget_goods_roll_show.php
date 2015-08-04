<?php
function theme_widget_goods_roll_show(&$setting,&$render){
    $filter['goodsId'] = $setting['goodsid'];
    $data['goodsdata'] = b2c_widgets::load('Goods')->getGoodsList($filter);
    $setting['goodsid'] = array_flip($setting['goodsid']);
    $i = 0;
    foreach($data['goodsdata']['goodsRows'] as $k=>$v){
        if($i<10){
            $data['goodsdata']['goodsRows'][$k]['title1'] = $setting['title1'][$setting['goodsid'][$k]];
            $data['goodsdata']['goodsRows'][$k]['title2'] = $setting['title2'][$setting['goodsid'][$k]];
        }else{
            unset($data['goodsdata']['goodsRows'][$k]);
        }
        $i++;
    }
    return $data; 
}
?>
