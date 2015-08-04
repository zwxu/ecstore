<?php
class package_auto_ship {
    function exec_auto(){
        $shipTime = app::get('b2c')->getConf('site.activity.payed_ship_time');//天
        $noAttendTime = app::get('b2c')->getConf('site.activity.no_attendActivity_time');//天
        
        if($shipTime == '')    $shipTime = 3;
        if($noAttendTime == '')    $shipTime = 60;
        $shipTime = $shipTime * 3600 * 24;//转化成秒
        $noAttendTime = $noAttendTime * 3600 * 24;//转化成秒
        $nowTime = time();
        $orderObj = app::get('b2c')->model('orders');
        $orders = $orderObj->db->select("select order_id,store_id from sdb_b2c_orders where status='active' and pay_status='1' and ship_status='0' and last_modified >".($nowTime-$shipTime)." and order_id in (select order_id from sdb_b2c_order_objects where obj_type='package')");
        $actObj = app::get('business')->model('activity');
        foreach($orders as $k=>$v){
            $rs = $actObj->dump(array('order_id'=>$v['order_id']),'id');
            if(empty($rs)){
                $data = array(
                            'act_type'=>'package',
                            'store_id'=>$v['store_id'],
                            'start_time'=>$nowTime,
                            'end_time'=>($nowTime+$noAttendTime),
                            'order_id'=>$v['order_id'],
                            'desc'=>'订单'.$v['order_id'].'未在规定时间内发货'
                        );
                $actObj->save($data);
            }
        }

        echo 'success';
    }

}