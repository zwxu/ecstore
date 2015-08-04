<?php
function theme_widget_goods_comp(&$setting,&$render){
    $mdl_goods=app::get('b2c')->model('goods');
    $imageDefault = app::get('image')->getConf('image.set');
    $data['defaultImage'] = $imageDefault['S']['default_image'];
    foreach((array)$setting['list'] as $key => $value){
        $show_nums=intval($setting["num{$key}"]?$setting["num{$key}"]:'7');
        $goods_id=explode(',',$value['linkid']);
        //$goods=json_decode($value['goods'],1);    
        //$goods=utils::array_change_key($goods,'id');
        $filter=array('goods_id'=>$goods_id,'disabled'=>'false','marketable'=>'true');
        $goods = $mdl_goods->getList('goods_id,name,image_default_id,price,udfimg ,thumbnail_pic,buy_count',$filter,0,-1);
        $goods = utils::array_change_key($goods,'goods_id');
        $data['goodsdata'] = array();
        foreach((array)$goods_id as $items){
            $data['goodsdata'][] = $goods[$items];
        }
    }
    return $data;
}
?>
