<?php
class groupbuy_auto_ship {
    function exec_auto(){
        $shipTime = app::get('b2c')->getConf('site.activity.payed_ship_time');//天
        $noAttendTime = app::get('b2c')->getConf('site.activity.no_attendActivity_time');//天
        
        if($shipTime == '')    $shipTime = 3;
        if($noAttendTime == '')    $shipTime = 60;
        $shipTime = $shipTime * 3600 * 24;//转化成秒
        $noAttendTime = $noAttendTime * 3600 * 24;//转化成秒
        $nowTime = time();
        $filter = array(
                    'status'=>'active',
                    'order_type'=>'group',
                    'pay_status'=>1,
                    'ship_status'=>0,
                    'last_modified|sthan'=>($nowTime-$shipTime)
                 );
        $orderObj = app::get('b2c')->model('orders');
        $actObj = app::get('business')->model('activity');
        $orders = $orderObj->getList('order_id,store_id',$filter);
        foreach($orders as $k=>$v){
            $rs = $actObj->dump(array('order_id'=>$v['order_id'],'act_type'=>'group'),'id');
            if(empty($rs)){
                $data = array(
                            'act_type'=>'group',
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