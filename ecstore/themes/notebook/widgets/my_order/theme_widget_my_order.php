<?php
function theme_widget_my_order(&$setting,&$smarty) {
    $result = array();
    if(isset($_SESSION['account']['member'])){
        $uid = $_SESSION['account']['member'];
        $member_obj = app::get('b2c')->model('members');
        $seller = '';
        if($uid){
            $member = $member_obj->dump(array('member_id'=>$uid),'seller');
            $seller = $member['seller'];
        }
        
        $order_obj = app::get('b2c')->model('orders');
        if($seller == 'seller'){
            //商家
            $result['seller'] = 1;
            $sto= kernel::single("business_memberstore",$uid);
            $store = $sto->storeinfo;
            //退款中的订单
            $refundeds = $order_obj->getList('order_id',array('pay_status'=>'4','status'=>'active','store_id'=>$store['store_id']));
            $result['refunded_order_num'] = count($refundeds);
            //待发货的订单
            $ships = $order_obj->getList('order_id',array('pay_status'=>'1','ship_status'=>'0','store_id'=>$store['store_id']));
            $result['ship_order_num'] = count($ships);
        }else{
            //买家
            $filter = array(
                'member_id'=>$uid,
                'pay_status'=>'0',
                'status'=>'active',
                'display'=>'false'
            );
            $result['no_pay'] = $order_obj->count($filter);
            unset($filter);
            $filter = array(
                'member_id'=>$uid,
                'ship_status'=>'1',
                'pay_status'=>'1',
                'status'=>'active',
                'display'=>'false'
            );
            $result['ship'] = $order_obj->count($filter);
            unset($filter);
            $filter = array(
                'member_id'=>$uid,
                'comments_count'=>0,
                'status'=>'finish',
                //'pay_status'=>'1',
                //'ship_status'=>'1'
            );
            $result['is_comment'] = $order_obj->count($filter);
        }
    }else{
        $result = array('no_pay'=>0,'ship'=>0,'is_comment'=>0);
    }
    return $result;
}

?>
