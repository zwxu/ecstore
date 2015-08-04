<?php
function theme_widget_gallery_goodshot(&$setting,&$smarty) {
    
    /*$show_nums=intval($setting['show_nums']?$setting['show_nums']:'7');
    $imageDefault = app::get('image')->getConf('image.set');
	$result['defaultImage'] = $imageDefault['S']['default_image'];
    $goods_id=explode(',',$setting['linkid']);
    $goods=json_decode($setting['goods'],1);    
    $goods=utils::array_change_key($goods,'id');

    $mdl_goods=app::get('b2c')->model('goods');
    $filter=array('goods_id'=>$goods_id,'disabled'=>'false','marketable'=>'true');
    $goodsList=$mdl_goods->getList('goods_id,name,image_default_id,price,udfimg ,thumbnail_pic',$filter);
    $goodsList=utils::array_change_key($goodsList,'goods_id');
    $searchid=array_keys($goodsList);
    foreach($goods as $key=> $id){
        if(in_array($key,$searchid)==false){
            unset($goods[$key]);
        }
    }
    $ngoods=array_slice($goods,0,$show_nums,true);//前6个
    foreach($ngoods as $k=>&$v){
        if(empty($v['pic'])){
            $v['pic']=$goodsList[$k]['image_default_id'];
        }
        if(empty($v['nice'])){
            
            $v['nice']=$goodsList[$k]['name'];
        }
        $v['price']=$goodsList[$k]['price'];
        $v['udfimg']=$goodsList[$k]['udfimg'];
        $v['thumbnail_pic']=$goodsList[$k]['thumbnail_pic'];
    }
    $result['goods']=$ngoods;
    return $result;*/

    //标签
    $show_nums=intval($setting['show_nums']?$setting['show_nums']:'7');
    $tag=$setting['tag'];
    $imageDefault = app::get('image')->getConf('image.set');
	$result['defaultImage'] = $imageDefault['S']['default_image'];
    //置顶商品
    $goods_id=explode(',',$setting['linkid']);
    $goods=json_decode($setting['goods'],1);
    $goods=utils::array_change_key($goods,'id');

    $mdl_goods=app::get('b2c')->model('goods');
    $filter=$smarty->pagedata['filter'];
    $orderTop=' 0 as gtop ';
    if(!empty($goods_id)){
          $orderTop=' case when goods_id in ('.$setting['linkid'].') then 1 else 0 end as gtop ';
    }
    $filter['tag']=$tag;
    $filter['str_where'] = $mdl_goods->_filter($filter);
    if(method_exists($smarty,'_filterStore')){
        $filter['str_where']=$filter['str_where'].' and '.$smarty->_filterStore();
   }
    $sql='select  '.$orderTop.',goods_id ,goods_id as id,name as nice,image_default_id  as pic,price,udfimg ,thumbnail_pic from sdb_b2c_goods where '.$filter['str_where'].' order by gtop desc,buy_count desc limit 0,'.$show_nums;
 
    $goodsList=$mdl_goods->db ->select($sql);
    $goodsList=utils::array_change_key($goodsList,'goods_id');
      
    $ngoods=array_slice($goodsList,0,$show_nums,true);//前6个
    foreach($ngoods as $k=>&$v){
        if(!empty($goods[$k]['pic'])){
            $v['pic']=$goods[$k]['pic'];
        }
        if(!empty($goods[$k]['nice'])){            
            $v['nice']=$goods[$k]['nice'];
        }
    }
    $result['goods']=$ngoods;
    return $result;
}