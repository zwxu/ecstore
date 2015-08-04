<?php

 
function theme_widget_gallery_recommend_goods($setting,&$smarty){
    //标签
    $tag=$setting['tag'];
    $imageDefault = app::get('image')->getConf('image.set');
	$result['defaultImage'] = $imageDefault['M']['default_image'];
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
    $sql='select  '.$orderTop.',goods_id ,goods_id as id,name as nice,image_default_id  as pic,price,udfimg ,thumbnail_pic from sdb_b2c_goods where '.$filter['str_where'].' order by gtop desc,buy_count desc limit 0,6';
 
    $goodsList=$mdl_goods->db ->select($sql);
    $goodsList=utils::array_change_key($goodsList,'goods_id');
      
    $ngoods=array_slice($goodsList,0,6,true);//前6个
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