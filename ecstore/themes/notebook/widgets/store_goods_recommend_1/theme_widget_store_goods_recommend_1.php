<?php
function theme_widget_store_goods_recommend_1(&$setting,&$render){

    $mdl_goods = app::get('b2c')->model('goods');
	
    //sql
    $sql = "SELECT g.goods_id,g.name,g.price,g.image_default_id,g.buy_m_count,g.mktprice FROM sdb_b2c_goods g";
    //店铺ID
    $where = " WHERE g.store_id=".intval($render->pagedata['store_id']);	
    //是否上架
    $where .= " AND g.marketable ='true' AND g.disabled='false'";
 
    //排行
	$order = " ORDER BY";
    if($ranking = $setting['ranking']){
        $order .= " $ranking,";
    }
    $order .= " g.uptime";
        //显示数量
        $set_limit = intval($setting['limit']);
        $set_limit = $set_limit ? $set_limit:3;
        $limit  = " LIMIT 0,$set_limit";
  
    $sql .= $where.$order.$limit;
    
    $data['goods'] = $mdl_goods->db->select($sql);
    $data['title'] = $setting['title'];

    $imageDefault = app::get('image')->getConf('image.set');
    $data['defaultImage'] = $imageDefault['M']['default_image'];
    $data['store'] = $render->pagedata['store'];
    $data['setting']=$setting;

    return $data;
}
?>
