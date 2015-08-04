<?php

 
class b2c_order_checkdelivery{
    
    function check_delivery_status($sdf,&$message){
        $order = app::get('b2c')->model('orders');
        $sdf_order = $order->dump($sdf['order_id'],'*');
        if($sdf_order['status'] != 'active' || $sdf_order['ship_status'] == '1'){
            $message = app::get('b2c')->_('发货失败: 订单状态锁定');
            return false;
            exit;
        }
        return true;
    }
    function check_sendnum(&$sdf, &$message)
    {
        if(!$sdf['order_id']){
            $message = app::get('b2c')->_('订单找不到');
            return false;
        }
        $order = app::get('b2c')->model('orders');
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
        $sdf_order = $order->dump($order_id,'*',$subsdf);
        $order_items = array();
        foreach($sdf_order['order_objects'] as $k=>$v){
            $order_items = array_merge($order_items,$v['order_items']);
        }

        foreach($order_items as $key=>$dinfo){
            if(!isset($sdf['send']) || floor($sdf['send'][$dinfo['item_id']]) > 0){
                if($aData['send'][$dinfo['item_id']] > $dinfo['nums'] - $dinfo['sendnum']){
                    $message .= app::get('b2c')->_('商品：').$dinfo['name'].app::get('b2c')->_('发货超出购买量');
                    return false;
                }
            }
        }
        
        return true;
    }
 
}
