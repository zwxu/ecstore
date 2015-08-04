<?php


function widget_gifttrees(&$setting,&$system){
    $gift = app::get('gift')->model('cat');
    $result=$gift->getList('*', array('ifpub'=>'true'));
	$obj_gift_ref = app::get('gift')->model('ref');
	$obj_goods = app::get('b2c')->model('goods');
    foreach($result as $k=>$v){
        $return[$v['cat_id']]['link']=$v['cat_id'];
        $return[$v['cat_id']]['cat_name']=$v['cat_name'];
		/** 修改货品名称 **/
		$arr = array();
		$arr = $obj_gift_ref->db->select("SELECT * FROM ".$obj_gift_ref->table_name(1)." WHERE `cat_id`=".intval($v['cat_id'])." GROUP BY `goods_id`");
		foreach ($arr as $key=>$arr_good){
			$row = $obj_goods->db->selectrow("SELECT * FROM ".$obj_goods->table_name(1)." WHERE `goods_id`=".intval($arr_good['goods_id'])." AND `marketable`='true'");
			if (!$row) unset($arr[$key]);
		}
		
        $return[$v['cat_id']]['sub'] = $arr;
    }
	
    return $return;

}
?>
