<?php

 
class commenterprise_task{

    function post_install($options)
    {
       $gask_type = array(array('type_id' => 1,'name' =>'商品咨询'),
       					array('type_id' => 2,'name' =>'配送咨询'),
       					array('type_id' => 3,'name' =>'售后咨询'),					
					);
       app::get('b2c')->setConf('gask_type',serialize($gask_type));
       $comment_goods_type = app::get('b2c')->model('comment_goods_type');
       $row = array(array('name' => '综合评价'),array('name' => '尺码'),array('name' => '外观'));
       foreach($row as $key => $val){
            $sdf['name'] = $val['name'];
            $sdf['type_id'] = $key+1;
            if($key==0){
            	$addon['is_total_point'] = 'on';
            	$sdf['addon'] = serialize($addon);
        	}
            $comment_goods_type->insert($sdf);
            unset($sdf);
       }
    }
    
    function post_uninstall(){
        app::get('b2c')->setConf('gask_type','');
        app::get('b2c')->setConf('member_point',0);
        $comment_goods_type = app::get('b2c')->model('comment_goods_type');
        $comment_goods_type->delete(array('type_id|than' =>1));
    }

}
